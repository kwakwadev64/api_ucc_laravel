<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    public const TYPE_COURSE = 'course';
    public const TYPE_EXAM = 'exam';

    protected $fillable = [
        'faculty_id',
        'promotion_id',
        'academic_year_id',
        'type',
        'title',
        'file_path',
        'file_type',
        'is_active',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => 'string',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($schedule) {

            if (
                !empty($schedule->file_path) &&
                empty($schedule->file_type)
            ) {
                $schedule->file_type = pathinfo(
                    $schedule->file_path,
                    PATHINFO_EXTENSION
                );
            }

            if (
                empty($schedule->uploaded_by) &&
                auth()->check()
            ) {
                $schedule->uploaded_by = auth()->id();
            }
        });

        static::updating(function ($schedule) {

            if (
                $schedule->isDirty('file_path') &&
                !empty($schedule->file_path)
            ) {
                $schedule->file_type = pathinfo(
                    $schedule->file_path,
                    PATHINFO_EXTENSION
                );
            }
        });
    }

    /**
     * Faculté concernée par l'horaire.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Promotion concernée.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
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
