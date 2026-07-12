<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    /**
     * Types d'horaires.
     */
    public const TYPE_COURSE = 'course';

    public const TYPE_EXAM = 'exam';



    /**
     * Les champs autorisés en assignation massive.
     */
    protected $fillable = [
        'faculty_id',
        'promotion_id',
        'program_id',
        'academic_year_id',
        'type',
        'title',
        'file_path',
        'file_type',
        'is_active',
        'uploaded_by',
    ];



    /**
     * Conversion automatique des types.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => 'string',
        ];
    }



    /**
     * Événements du modèle.
     *
     * Gestion automatique :
     * - type du fichier
     * - utilisateur qui publie
     */
    protected static function booted()
    {
        /**
         * Avant création.
         */
        static::creating(function ($schedule) {

            /*
            |--------------------------------------------------------------------------
            | Génération automatique du type de fichier
            |--------------------------------------------------------------------------
            */
            if (
                !empty($schedule->file_path)
                &&
                empty($schedule->file_type)
            ) {
                $schedule->file_type = pathinfo(
                    $schedule->file_path,
                    PATHINFO_EXTENSION
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Enregistrement automatique de l'utilisateur connecté
            |--------------------------------------------------------------------------
            */
            if (
                empty($schedule->uploaded_by)
                &&
                auth()->check()
            ) {
                $schedule->uploaded_by = auth()->id();
            }
        });



        /**
         * Avant modification.
         */
        static::updating(function ($schedule) {

            /*
            |--------------------------------------------------------------------------
            | Mise à jour du type si le fichier change
            |--------------------------------------------------------------------------
            */
            if (
                $schedule->isDirty('file_path')
                &&
                !empty($schedule->file_path)
            ) {
                $schedule->file_type = pathinfo(
                    $schedule->file_path,
                    PATHINFO_EXTENSION
                );
            }
        });
    }





    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * Faculté concernée par l'horaire.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }



    /**
     * Promotion concernée.
     *
     * Peut être null pour un horaire général de faculté.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }



    /**
     * Filière / Programme concerné.
     *
     * Peut être null.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }



    /**
     * Année académique concernée.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }



    /**
     * Utilisateur ayant publié l'horaire.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
