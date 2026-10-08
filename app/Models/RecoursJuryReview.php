<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoursJuryReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'recours_id',
        'jury_member_id',
        'case_presentation',
        'professor_appreciation',
        'material_evidence',
        'decision',
        'decision_comment',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    /**
     * Recours concerné.
     */
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    /**
     * Membre du jury ayant traité le recours.
     */
    public function juryMember()
    {
        return $this->belongsTo(User::class, 'jury_member_id');
    }
}
