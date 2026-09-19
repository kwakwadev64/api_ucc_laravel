<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'faculty_id',
        'level',
        'is_active',
    ];

    /**
     * Une promotion appartient à une faculté.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Cours de la promotion.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Étudiants de la promotion.
     */
    public function students()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Horaires de la promotion.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'promotion_id');
    }
}
