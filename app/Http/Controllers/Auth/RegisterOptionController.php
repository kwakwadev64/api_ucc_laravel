<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Promotion;
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
                'level',
                'program_id'
            )->get(),
        ]);
    }
}