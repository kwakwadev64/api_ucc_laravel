<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archive extends Model
{
    protected $fillable = [
        'name',
        'promotion_id',
        'semester',
        'year',
        'type',
        'file_path',
        'file_type',
        'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Archive $archive) {
            /*
             * Déterminer automatiquement le type du fichier
             * à partir de son chemin.
             *
             * Exemple :
             * archives/abc123.jpeg
             * devient :
             * jpeg
             */
            if (!empty($archive->file_path)) {
                $archive->file_type = strtolower(
                    pathinfo(
                        $archive->file_path,
                        PATHINFO_EXTENSION
                    )
                );
            }

            /*
             * Utilisateur connecté
             */
            if (empty($archive->uploaded_by)) {
                $archive->uploaded_by = auth()->id();
            }
        });

        static::updating(function (Archive $archive) {
            /*
             * Si un nouveau fichier est envoyé
             * lors d'une modification, on recalcule
             * son extension.
             */
            if (
                $archive->isDirty('file_path')
                && !empty($archive->file_path)
            ) {
                $archive->file_type = strtolower(
                    pathinfo(
                        $archive->file_path,
                        PATHINFO_EXTENSION
                    )
                );
            }
        });
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(
            Promotion::class,
            'promotion_id'
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
