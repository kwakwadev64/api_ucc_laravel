<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\PromotionRequest;
use App\Models\User;
use App\Services\PromotionRequestService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PromotionRequestEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private AcademicYear $year;

    private int $targetPromotion;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->year = AcademicYear::create([
            'name' => '2026-2027', 'start_date' => '2026-09-01',
            'end_date' => '2027-07-31', 'status' => 'active',
        ]);
        $faculty = DB::table('faculties')->insertGetId(['name' => 'Informatique', 'code' => 'FSI']);
        $promotion = DB::table('promotions')->insertGetId([
            'name' => 'L1', 'level' => 'L1', 'faculty_id' => $faculty,
            'academic_year_id' => $this->year->id,
        ]);
        $this->targetPromotion = DB::table('promotions')->insertGetId([
            'name' => 'L2', 'level' => 'L2', 'faculty_id' => $faculty,
            'academic_year_id' => $this->year->id,
        ]);
        $this->student = User::create([
            'first_name' => 'Etudiant', 'last_name' => 'Test', 'email' => 'promotion@example.test',
            'password' => 'password', 'role' => 'student', 'is_active' => true,
            'faculty_id' => $faculty, 'promotion_id' => $promotion,
            'academic_year_id' => $this->year->id,
        ]);
    }

    private function payload(): array
    {
        return [
            'target_promotion_id' => $this->targetPromotion,
            'proof_file' => UploadedFile::fake()->createWithContent(
                'releve.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aK1sAAAAASUVORK5CYII=')
            ),
        ];
    }

    private function submit(array $extra = []): TestResponse
    {
        return $this->actingAs($this->student, 'sanctum')->post(
            '/api/promotion-requests', array_merge($this->payload(), $extra),
            ['Accept' => 'application/json']
        );
    }

    public static function requestStatuses(): array
    {
        return [['pending'], ['approved'], ['rejected']];
    }

    public function test_current_route_requires_authentication(): void
    {
        $this->getJson('/api/promotion-requests/current')->assertUnauthorized();
    }

    public function test_submission_requires_authentication(): void
    {
        $this->postJson('/api/promotion-requests', [])->assertUnauthorized();
    }

    public function test_first_submission_is_allowed_and_immediately_blocks_another(): void
    {
        $this->actingAs($this->student, 'sanctum')->getJson('/api/promotion-requests/current')
            ->assertOk()->assertJsonPath('success', true)
            ->assertJsonPath('data.can_submit', true)
            ->assertJsonPath('data.academic_year.id', $this->year->id)
            ->assertJsonPath('data.academic_year.name', $this->year->name)
            ->assertJsonPath('data.request', null);

        $id = $this->submit()->assertCreated()->assertJsonPath('data.status', 'pending')->json('data.id');
        $this->getJson('/api/promotion-requests/current')->assertOk()
            ->assertJsonPath('data.can_submit', false)
            ->assertJsonPath('data.request.id', $id)
            ->assertJsonPath('data.request.status', 'pending')
            ->assertJsonMissingPath('data.request.proof_file');
        $this->submit()->assertConflict()->assertJsonPath('status', 'pending');
        $this->assertDatabaseCount('promotion_requests', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles('proofs'));
    }

    #[DataProvider('requestStatuses')]
    public function test_every_status_blocks_the_whole_year_even_without_resubmitting_a_proof(string $status): void
    {
        $id = $this->submit()->assertCreated()->json('data.id');
        PromotionRequest::findOrFail($id)->update(['status' => $status, 'admin_comment' => 'Décision de test']);
        if ($status === 'approved') {
            $this->student->update(['promotion_id' => $this->targetPromotion]);
        }

        $this->getJson('/api/promotion-requests/current')->assertOk()
            ->assertJsonPath('data.can_submit', false)
            ->assertJsonPath('data.request.status', $status)
            ->assertJsonPath('data.request.admin_comment', 'Décision de test');
        $this->postJson('/api/promotion-requests', [])->assertConflict()->assertJsonPath('status', $status);
        $this->assertDatabaseCount('promotion_requests', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles('proofs'));
    }

    #[DataProvider('requestStatuses')]
    public function test_a_new_active_academic_year_allows_one_new_request(string $status): void
    {
        $id = $this->submit()->assertCreated()->json('data.id');
        PromotionRequest::findOrFail($id)->update(['status' => $status]);
        $this->year->update(['status' => 'closed']);
        $newYear = AcademicYear::create([
            'name' => '2027-2028', 'start_date' => '2027-09-01',
            'end_date' => '2028-07-31', 'status' => 'active',
        ]);

        $this->getJson('/api/promotion-requests/current')->assertOk()
            ->assertJsonPath('data.can_submit', true)
            ->assertJsonPath('data.academic_year.id', $newYear->id)
            ->assertJsonPath('data.request', null);
        $this->submit()->assertCreated()->assertJsonPath('data.academic_year_id', $newYear->id);
        $this->submit()->assertConflict();
        $this->assertDatabaseCount('promotion_requests', 2);
    }

    public function test_another_student_request_does_not_block_the_authenticated_student(): void
    {
        $this->submit()->assertCreated();
        $other = $this->student->replicate();
        $other->email = 'other-promotion@example.test';
        $other->save();

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/promotion-requests/current?user_id='.$this->student->id)
            ->assertOk()->assertJsonPath('data.can_submit', true)->assertJsonPath('data.request', null);
        $this->post('/api/promotion-requests', $this->payload(), ['Accept' => 'application/json'])->assertCreated();
        $this->assertDatabaseCount('promotion_requests', 2);
    }

    public function test_changing_profile_year_or_request_parameters_does_not_bypass_the_limit(): void
    {
        $this->submit()->assertCreated();
        $otherYear = AcademicYear::create([
            'name' => '2025-2026', 'start_date' => '2025-09-01',
            'end_date' => '2026-07-31', 'status' => 'closed',
        ]);
        $this->student->update(['academic_year_id' => $otherYear->id]);

        $this->getJson('/api/promotion-requests/current?academic_year_id='.$otherYear->id)
            ->assertOk()->assertJsonPath('data.can_submit', false)
            ->assertJsonPath('data.academic_year.id', $this->year->id);
        $this->submit(['academic_year_id' => $otherYear->id, 'status' => 'approved'])->assertConflict();
        $this->assertDatabaseCount('promotion_requests', 1);
    }

    public function test_submission_always_uses_the_authenticated_student_and_active_year(): void
    {
        $this->submit(['user_id' => 99999, 'academic_year_id' => 99999, 'status' => 'approved'])
            ->assertCreated()->assertJsonPath('data.user_id', $this->student->id)
            ->assertJsonPath('data.academic_year_id', $this->year->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_no_active_year_blocks_status_and_submission(): void
    {
        $this->year->update(['status' => 'closed']);
        $this->actingAs($this->student, 'sanctum')->getJson('/api/promotion-requests/current')
            ->assertOk()->assertJsonPath('data.can_submit', false)
            ->assertJsonPath('data.academic_year', null);
        $this->postJson('/api/promotion-requests', [])->assertUnprocessable();
        $this->assertDatabaseCount('promotion_requests', 0);
    }

    public function test_missing_current_promotion_blocks_status_and_submission(): void
    {
        $this->student->update(['promotion_id' => null]);
        $this->actingAs($this->student, 'sanctum')->getJson('/api/promotion-requests/current')
            ->assertOk()->assertJsonPath('data.can_submit', false);
        $this->postJson('/api/promotion-requests', [])->assertUnprocessable();
        $this->assertDatabaseCount('promotion_requests', 0);
    }

    public function test_proof_and_target_validation_still_apply_to_the_first_request(): void
    {
        $this->actingAs($this->student, 'sanctum')->postJson('/api/promotion-requests', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['target_promotion_id', 'proof_file']);
        $this->submit(['target_promotion_id' => $this->student->promotion_id])->assertUnprocessable();
        $this->assertDatabaseCount('promotion_requests', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('proofs'));
    }

    public function test_simultaneous_submissions_return_a_conflict_and_remove_the_extra_proof(): void
    {
        $service = Mockery::mock(PromotionRequestService::class)->makePartial();
        $service->shouldReceive('currentStatusFor')->twice()->andReturn([
            'academic_year' => $this->year, 'request' => null,
            'can_submit' => true, 'message' => 'Demande autorisée.',
        ]);
        $this->app->instance(PromotionRequestService::class, $service);

        $this->submit()->assertCreated();
        $this->submit()->assertConflict();
        $this->assertDatabaseCount('promotion_requests', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles('proofs'));
    }

    #[DataProvider('requestStatuses')]
    public function test_database_uniqueness_does_not_depend_on_the_status(string $status): void
    {
        $id = $this->submit()->assertCreated()->json('data.id');
        $existing = PromotionRequest::findOrFail($id);
        $existing->update(['status' => $status]);

        $this->expectException(UniqueConstraintViolationException::class);
        $duplicate = $existing->replicate();
        $duplicate->status = 'pending';
        $duplicate->save();
    }
}
