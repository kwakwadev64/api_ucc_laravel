<?php

namespace App\Policies;

use App\Models\Actualite;
use App\Models\User;

class ActualitePolicy
{
    /**
     * Le Super Admin contrôle tout, le Faculty Admin aussi, 
     * mais le CP et le Teacher ne font que voir ou créer.
     */
    public function viewAny(User $user): bool
    {
        // Tout utilisateur ayant accès à l'admin peut voir la liste
        return in_array($user->role, ['super_admin', 'faculty_admin', 'teacher', 'cp']);
    }

    public function create(User $user): bool
    {
        // Imaginer que seuls les admins et les CP publient les actus
        return in_array($user->role, ['super_admin', 'faculty_admin', 'cp']);
    }

    public function update(User $user, Actualite $actualite): bool
    {
        // Un CP ne peut modifier que si c'est dans sa propre faculté (exemple)
        if ($user->role === 'cp') {
            // Si vos actualités possèdent une relation ou clé avec la faculté/promotion
            return $user->faculty_id === $actualite->faculty_id; 
        }

        return in_array($user->role, ['super_admin', 'faculty_admin']);
    }

    public function delete(User $user, Actualite $actualite): bool
    {
        // Seuls les hauts gradés suppriment
        return in_array($user->role, ['super_admin', 'faculty_admin']);
    }
}