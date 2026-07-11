<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',

    ];


    public function users()
    {
        return $this->hasMany(User::class);
    }


    public function programs()
    {
        return $this->hasMany(Program::class);
    }
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
}
