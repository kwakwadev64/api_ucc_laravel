<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * Les champs autorisés en assignation massive
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role',
        'faculty_id',
        'promotion_id',
        'academic_year_id',
        'profile_photo',
        'is_active',
    ];


    /**
     * Les champs cachés dans les réponses JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Conversion des types
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }


    /**
     * Autoriser l'accès au panel Filament
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }


    /**
     * Nom affiché par Filament
     */
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }


    /**
     * Un utilisateur appartient à une faculté
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }


    /**
     * Un étudiant appartient à une promotion
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }


    /**
     * Un enseignant peut publier plusieurs cours
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }
}
