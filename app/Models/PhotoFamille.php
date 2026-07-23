<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoFamille extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'faculte',
        'promotion',
        'image_path',
        'is_active',
    ];

    // Pour sérialiser l'URL complète automatiquement dans l'API
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }
        return asset('storage/' . $this->image_path);
    }
}
