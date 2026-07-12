<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'name',
        'code',
        'cycle',

    ];


    /**
     * Un programme appartient à une faculté
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }


    /**
     * Un programme peut avoir plusieurs promotions
     */
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
 * Horaires de la filiere
 */
public function schedules()
{
    return $this->hasMany(Schedule::class, 'program_id');
}
}
