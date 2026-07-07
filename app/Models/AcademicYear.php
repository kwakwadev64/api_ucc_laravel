<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];


    /**
     * Promotions liées à cette année académique
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }


    /**
     * Cours liés à cette année académique
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
