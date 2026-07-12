<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Promotion;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class RegisterOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'faculties' => Faculty::select('id', 'name')->get(),

            'programs' => Program::select(
                'id',
                'name',
                'faculty_id'
            )->get(),

            'promotions' => Promotion::select(
                'id',
                'name',
                'program_id',
                'faculty_id'
            )->get(),

            'academic_years' => AcademicYear::select(
                'id',
                'name',

            )->get(),
        ]);
    }
}
