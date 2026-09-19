<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicCalendar extends Model
{
    protected $fillable = [
        'academic_year_id',
        'file_path',
        'file_type',
        'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (AcademicCalendar $calendar) {
            if (!empty($calendar->file_path)) {
                $calendar->file_type = strtolower(
                    pathinfo(
                        $calendar->file_path,
                        PATHINFO_EXTENSION
                    )
                );
            }

            if (empty($calendar->uploaded_by)) {
                $calendar->uploaded_by = auth()->id();
            }
        });

        static::updating(function (AcademicCalendar $calendar) {
            if (
                $calendar->isDirty('file_path')
                && !empty($calendar->file_path)
            ) {
                $calendar->file_type = strtolower(
                    pathinfo(
                        $calendar->file_path,
                        PATHINFO_EXTENSION
                    )
                );
            }
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}
