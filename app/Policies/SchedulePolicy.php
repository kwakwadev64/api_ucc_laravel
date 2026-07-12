<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{

    /**
     * Voir un horaire.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        /*
         * Les étudiants ne voient que leurs horaires
         * La vérification détaillée est déjà faite
         * dans ScheduleService.
         */
        if ($user->role === 'student') {

            return $schedule->faculty_id === $user->faculty_id;
        }


        /*
         * Tous les autres rôles peuvent consulter.
         */
        return true;
    }



    /**
     * Créer un horaire.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'faculty_admin',
            'super_admin',
        ]);
    }



    /**
     * Modifier un horaire.
     */
    public function update(User $user, Schedule $schedule): bool
    {

        /*
         * Super admin accès total.
         */
        if ($user->role === 'super_admin') {
            return true;
        }


        /*
         * Un admin faculté ne gère
         * que les horaires de sa faculté.
         */
        if ($user->role === 'faculty_admin') {

            return $user->faculty_id === $schedule->faculty_id;
        }


        return false;
    }



    /**
     * Supprimer un horaire.
     */
    public function delete(User $user, Schedule $schedule): bool
    {

        return $this->update($user, $schedule);
    }
}
