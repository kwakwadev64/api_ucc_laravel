<?php

namespace Tests\Feature;

use App\Models\AppealReason;
use App\Models\Course;
use App\Models\Recours;
use App\Models\RecoursAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecoursEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $course;
    private AppealReason $reason;
    private int $year;
    private int $promotion;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->year = DB::table('academic_years')->insertGetId([
            'name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-07-01', 'status' => 'active',
        ]);
        $faculty = DB::table('faculties')->insertGetId(['name' => 'Informatique', 'code' => 'FSI']);
        $this->promotion = DB::table('promotions')->insertGetId([
            'name' => 'L1', 'level' => 'L1', 'faculty_id' => $faculty, 'academic_year_id' => $this->year,
        ]);
        $this->student = User::create([
            'first_name' => 'Etudiant', 'last_name' => 'Test', 'email' => 'student@example.test',
            'password' => 'password', 'role' => 'student', 'is_active' => true,
            'faculty_id' => $faculty, 'promotion_id' => $this->promotion, 'academic_year_id' => $this->year,
        ]);
        $this->course = Course::create([
            'teacher_id' => $this->student->id, 'promotion_id' => $this->promotion,
            'academic_year_id' => $this->year, 'title' => 'Cours de test',
        ]);
        $this->reason = AppealReason::create([
            'name' => 'Motif de test', 'code' => 'test', 'requires_description' => false,
            'requires_attachment' => false, 'is_active' => true,
        ]);
    }

    private function payload(): array
    {
        return [
            'academic_year_id' => $this->year, 'promotion_id' => $this->promotion,
            'first_name' => 'Etudiant', 'last_name' => 'Test',
            'courses' => [['course_id' => $this->course->id, 'professor_name' => 'Titulaire']],
            'reasons' => [['reason_id' => $this->reason->id, 'description' => 'Explication']],
        ];
    }

    private function file(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('piece.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");
    }

    public function test_routes_require_authentication(): void
    {
        $this->getJson('/api/recours/options')->assertUnauthorized();
        $this->getJson('/api/recours')->assertUnauthorized();
        $this->postJson('/api/recours', $this->payload())->assertUnauthorized();
    }

    public function test_options_are_scoped_to_the_student_and_active_reasons(): void
    {
        $this->reason->update(['is_active' => false]);
        $otherPromotion = DB::table('promotions')->insertGetId([
            'name' => 'L2', 'level' => 'L2', 'faculty_id' => $this->student->faculty_id,
            'academic_year_id' => $this->year,
        ]);
        Course::create([
            'teacher_id' => $this->student->id, 'promotion_id' => $otherPromotion,
            'academic_year_id' => $this->year, 'title' => 'Autre promotion',
        ]);
        $this->actingAs($this->student, 'sanctum')->getJson('/api/recours/options')
            ->assertOk()->assertJsonPath('data.promotion.id', $this->promotion)
            ->assertJsonPath('data.academic_year.id', $this->year)
            ->assertJsonCount(1, 'data.courses')->assertJsonCount(0, 'data.reasons');
    }

    public function test_student_can_create_list_and_read_recours_without_attachment(): void
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->post('/api/recours', $this->payload(), ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.courses.0.pivot.professor_name', 'Titulaire')
            ->assertJsonPath('data.reasons.0.pivot.description', 'Explication');
        $id = $response->json('data.id');
        $this->getJson('/api/recours')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/recours/'.$id)->assertOk()->assertJsonPath('data.id', $id);
    }

    public function test_other_students_cannot_see_or_download_the_recours(): void
    {
        $payload = $this->payload();
        $payload['attachments'] = [$this->file()];
        $payload['attachment_reason_ids'] = [$this->reason->id];
        $response = $this->actingAs($this->student, 'sanctum')
            ->post('/api/recours', $payload, ['Accept' => 'application/json'])->assertCreated();
        $id = $response->json('data.id');
        $download = $response->json('data.attachments.0.download_path');
        $other = $this->student->replicate();
        $other->email = 'other@example.test'; $other->save();
        $this->actingAs($other, 'sanctum')->getJson('/api/recours')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/recours/'.$id)->assertForbidden();
        $this->getJson('/api'.$download)->assertForbidden();
    }

    public function test_required_explanation_and_attachment_are_enforced(): void
    {
        $this->reason->update(['requires_description' => true, 'requires_attachment' => true]);
        $payload = $this->payload(); unset($payload['reasons'][0]['description']);
        $this->actingAs($this->student, 'sanctum')->postJson('/api/recours', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['reasons.0.description', 'reasons.0.attachment']);
        $this->assertDatabaseCount('recours', 0);
    }

    public function test_inactive_motifs_and_duplicate_courses_are_rejected(): void
    {
        $this->reason->update(['is_active' => false]);
        $this->actingAs($this->student, 'sanctum')->postJson('/api/recours', $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('reasons.0.reason_id');
        $this->reason->update(['is_active' => true]);
        $payload = $this->payload(); $payload['courses'][] = $payload['courses'][0];
        $this->postJson('/api/recours', $payload)->assertUnprocessable()->assertJsonValidationErrors('courses.0.course_id');
    }

    public function test_year_promotion_and_course_scope_cannot_be_forged(): void
    {
        $year = DB::table('academic_years')->insertGetId([
            'name' => '2025-2026', 'start_date' => '2025-09-01', 'end_date' => '2026-07-01', 'status' => 'closed',
        ]);
        $promotion = DB::table('promotions')->insertGetId([
            'name' => 'L2', 'level' => 'L2', 'faculty_id' => $this->student->faculty_id, 'academic_year_id' => $year,
        ]);
        $otherCourse = Course::create([
            'teacher_id' => $this->student->id, 'promotion_id' => $promotion, 'academic_year_id' => $year, 'title' => 'Autre cours',
        ]);
        $payload = $this->payload(); $payload['academic_year_id'] = $year; $payload['promotion_id'] = $promotion;
        $payload['courses'][0]['course_id'] = $otherCourse->id;
        $this->actingAs($this->student, 'sanctum')->postJson('/api/recours', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['academic_year_id', 'promotion_id', 'courses.0.course_id']);
    }

    public function test_pieces_are_private_and_downloadable_by_the_owner(): void
    {
        $payload = $this->payload(); $payload['attachments'] = [$this->file()];
        $payload['attachment_reason_ids'] = [$this->reason->id];
        $response = $this->actingAs($this->student, 'sanctum')
            ->post('/api/recours', $payload, ['Accept' => 'application/json'])->assertCreated();
        $this->assertArrayNotHasKey('file_path', $response->json('data.attachments.0'));
        $attachment = RecoursAttachment::firstOrFail();
        Storage::disk('local')->assertExists($attachment->file_path);
        Storage::disk('public')->assertMissing($attachment->file_path);
        $this->get('/api'.$response->json('data.attachments.0.download_path'))->assertOk()->assertDownload('piece.pdf');
    }

    public function test_piece_reason_and_index_must_match_selected_motifs(): void
    {
        $other = AppealReason::create([
            'name' => 'Autre', 'code' => 'other', 'requires_description' => false,
            'requires_attachment' => false, 'is_active' => true,
        ]);
        $payload = $this->payload(); $payload['attachments'] = [$this->file()];
        $payload['attachment_reason_ids'] = [$other->id];
        $this->actingAs($this->student, 'sanctum')->post('/api/recours', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('attachment_reason_ids.0');
        $payload['attachment_reason_ids'] = [1 => $this->reason->id];
        $this->post('/api/recours', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('attachments');
    }

    public function test_oversized_and_unsafe_files_are_rejected(): void
    {
        $payload = $this->payload(); $payload['attachment_reason_ids'] = [$this->reason->id];
        $payload['attachments'] = [UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf')];
        $this->actingAs($this->student, 'sanctum')->post('/api/recours', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('attachments.0');
        $payload['attachments'] = [UploadedFile::fake()->createWithContent('script.html', '<script>alert(1)</script>')];
        $this->post('/api/recours', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('attachments.0');
    }

    public function test_storage_is_cleaned_up_if_database_transaction_fails(): void
    {
        RecoursAttachment::creating(function () { throw new \RuntimeException('Failure for test'); });
        $payload = $this->payload(); $payload['attachments'] = [$this->file()];
        $payload['attachment_reason_ids'] = [$this->reason->id];
        $this->actingAs($this->student, 'sanctum')->post('/api/recours', $payload, ['Accept' => 'application/json'])
            ->assertStatus(500);
        $this->assertDatabaseCount('recours', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_inactive_students_and_teachers_cannot_create_recours(): void
    {
        $this->student->update(['is_active' => false]);
        $this->actingAs($this->student, 'sanctum')->postJson('/api/recours', $this->payload())->assertForbidden();
        $this->student->update(['is_active' => true, 'role' => 'teacher']);
        $this->postJson('/api/recours', $this->payload())->assertForbidden();
    }
}
