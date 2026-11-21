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
        'faculty_id',
        'promotion_id',
        'academic_year_id',
        'image_path',
        'is_active',
    ];

    // Pour sérialiser automatiquement l'URL complète dans l'API
    protected $appends = ['image_url'];

    /**
     * Générer l'URL publique de l'image.
     */
    public function getImageUrlAttribute()
    {
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }

    /**
     * Une photo de famille appartient à une faculté.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    /**
     * Une photo de famille appartient à une promotion.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    /**
     * Une photo de famille appartient à une année académique.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
