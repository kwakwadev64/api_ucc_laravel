<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membre extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'role', 'description', 'photo', 'avatar_url', 
        'sujet_memoire', 'github', 'linkedin', 'portfolio',
    ];

    /**
     * Un membre peut appartenir à plusieurs sections d'équipe
     */
    public function sectionEquipes(): BelongsToMany
    {
        return $this->belongsToMany(SectionEquipe::class);
    }
}