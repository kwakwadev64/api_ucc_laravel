<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoursAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'recours_id',
        'reason_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    /**
     * Recours auquel appartient la pièce.
     */
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    /**
     * Motif auquel la pièce est associée.
     */
    public function reason()
    {
        return $this->belongsTo(AppealReason::class, 'reason_id');
    }
}
