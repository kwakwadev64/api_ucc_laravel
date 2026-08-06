<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SectionEquipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'annee', 'titre', 'description',
    ];

    /**
     * Une section d'équipe est liée à un membre
     */
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Membre::class);
    }
}