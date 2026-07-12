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
        'program_id',
        'faculty_id',
        'level',
        'is_active',
    ];


    /**
     * Une promotion appartient à une faculté
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }


    /**
     * Une promotion peut appartenir à un programme (Master/Doctorat)
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }




    /**
     * Année académique de la promotion
     */


    public function courses()
{
    return $this->hasMany(Course::class);
}
public function students()
{
    return $this->hasMany(User::class);
}

/**
 * Horaires publiés par cet utilisateur.
 */

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'promotion_id');
    }

}
