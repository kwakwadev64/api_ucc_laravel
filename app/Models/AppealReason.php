<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppealReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'requires_attachment',
        'requires_description',
        'is_active',
    ];

    protected $casts = [
        'requires_attachment' => 'boolean',
        'requires_description' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Recours utilisant ce motif.
     */
    public function recours()
    {
        return $this->belongsToMany(
            Recours::class,
            'recours_reasons'
        )->withPivot('description')
         ->withTimestamps();
    }

    /**
 * Pièces justificatives liées à ce motif.
 */
public function attachments()
{
    return $this->hasMany(
        RecoursAttachment::class,
        'reason_id'
    );
}
}
