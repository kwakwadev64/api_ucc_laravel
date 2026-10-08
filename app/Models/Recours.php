<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recours extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'promotion_id',
        'last_name',
        'post_name',
        'first_name',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Étudiant ayant introduit le recours.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Année académique du recours.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Promotion concernée.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
 * Cours concernés par le recours.
 */
public function courses()
{
    return $this->belongsToMany(
        Course::class,
        'recours_courses'
    )->withPivot('professor_name')
     ->withTimestamps();
}

/**
 * Motifs du recours.
 */
public function reasons()
{
    return $this->belongsToMany(
        AppealReason::class,
        'recours_reasons',
        'recours_id',
        'reason_id'
    )->withPivot('description')
     ->withTimestamps();
}

/**
 * Pièces justificatives du recours.
 */
public function attachments()
{
    return $this->hasMany(RecoursAttachment::class);
}

/**
 * Évaluation du jury.
 */
public function juryReview()
{
    return $this->hasOne(RecoursJuryReview::class);
}

}
