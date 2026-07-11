<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;


    protected $fillable = [
        'teacher_id',
        'promotion_id',
        'academic_year_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'is_published',
    ];



    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }



    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }


   
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
