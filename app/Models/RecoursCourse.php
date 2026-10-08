<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoursCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'recours_id',
        'course_id',
        'professor_name',
    ];

    /**
     * Recours auquel appartient cette ligne.
     */
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    /**
     * Cours concerné par le recours.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    
}
