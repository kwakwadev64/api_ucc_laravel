<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoursReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'recours_id',
        'reason_id',
        'description',
    ];

    /**
     * Recours concerné.
     */
    public function recours()
    {
        return $this->belongsTo(Recours::class);
    }

    /**
     * Motif sélectionné.
     */
    public function reason()
    {
        return $this->belongsTo(AppealReason::class, 'reason_id');
    }
}
