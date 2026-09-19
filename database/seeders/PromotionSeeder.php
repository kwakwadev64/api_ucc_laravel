<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('promotions')->delete();

        $faculty = Faculty::where('code', 'FSI')->first();

        if (!$faculty) {
            return;
        }

        $promotions = [
            'L1',
            'L2',
            'L3',
            'M1RSX',
            'M1CSI',
            'M2RSX',
            'M2CSI',
        ];

        foreach ($promotions as $promotion) {
            Promotion::create([
                'faculty_id' => $faculty->id,
                'name' => $promotion,
                'level' => str_starts_with($promotion, 'L')
                    ? $promotion
                    : substr($promotion, 0, 2),
                'academic_year_id' => 1,
                'is_active' => true,
            ]);
        }
    }
}
