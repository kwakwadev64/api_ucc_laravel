<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Promotion;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class RegisterOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'faculties' => Faculty::select(
                'id',
                'name',
                'code'
            )->get(),

            'promotions' => Promotion::select(
                'id',
                'name',
                'level',
                'faculty_id',
                'academic_year_id'
            )->get(),

            'academic_years' => AcademicYear::select(
                'id',
                'name'
            )->get(),
        ]);
    }
}
