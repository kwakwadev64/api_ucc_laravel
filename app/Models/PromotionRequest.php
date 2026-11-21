<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'academic_year_id',
        'current_promotion_id',
        'target_promotion_id',
        'proof_file',
        'status',
        'admin_comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function currentPromotion()
    {
        return $this->belongsTo(Promotion::class, 'current_promotion_id');
    }

    public function targetPromotion()
    {
        return $this->belongsTo(Promotion::class, 'target_promotion_id');
    }
}
