<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionEquipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'annee',
        'titre',
        'description',
        'membre_id',
    ];

    /**
     * Une section d'équipe est liée à un membre
     */
    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }
}