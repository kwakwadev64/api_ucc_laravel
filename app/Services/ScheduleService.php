<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScheduleService
{
    /**
     * Créer un nouvel horaire.
     *
     * Empêche les doublons selon :
     * - faculté
     * - année académique
     * - type
     * - promotion
     *
     * Une promotion NULL correspond à un horaire général
     * de la faculté.
     */
    public function create(
        array $data,
        UploadedFile $file,
        User $user
    ): Schedule {
        $path = null;

        try {
            return DB::transaction(function () use (
                $data,
                $file,
                $user,
                &$path
            ) {
                /*
                |--------------------------------------------------------------------------
                | Vérifier doublon
                |--------------------------------------------------------------------------
                */

                if ($this->scheduleExists($data)) {
                    throw new \Exception(
                        "Un horaire identique existe déjà. Veuillez modifier celui qui existe."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Upload fichier
                |--------------------------------------------------------------------------
                */

                $path = $file->store(
                    'schedules',
                    'public'
                );

                /*
                |--------------------------------------------------------------------------
                | Création horaire
                |--------------------------------------------------------------------------
                */

                return Schedule::create([
                    'faculty_id' => $data['faculty_id'],

                    'promotion_id' =>
                        $data['promotion_id'] ?? null,

                    'academic_year_id' =>
                        $data['academic_year_id'],

                    'type' =>
                        $data['type'],

                    'title' =>
                        $data['title'],

                    'file_path' =>
                        $path,

                    'file_type' =>
                        $file->getClientOriginalExtension(),

                    'is_active' =>
                        true,

                    'uploaded_by' =>
                        $user->id,
                ]);
            });

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Supprimer le fichier si erreur
            |--------------------------------------------------------------------------
            */

            if ($path) {
                Storage::disk('public')->delete($path);
            }

            Log::error(
                'Erreur création horaire',
                [
                    'message' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }

    /**
     * Vérifier si un horaire identique existe.
     */
    private function scheduleExists(
        array $data
    ): bool {
        $query = Schedule::where(
            'faculty_id',
            $data['faculty_id']
        )
        ->where(
            'academic_year_id',
            $data['academic_year_id']
        )
        ->where(
            'type',
            $data['type']
        );

        /*
        |--------------------------------------------------------------------------
        | Promotion
        |--------------------------------------------------------------------------
        |
        | promotion_id = NULL :
        | horaire général de la faculté.
        |
        | promotion_id renseigné :
        | horaire spécifique à cette promotion.
        |
        */

        if (!empty($data['promotion_id'])) {
            $query->where(
                'promotion_id',
                $data['promotion_id']
            );
        } else {
            $query->whereNull(
                'promotion_id'
            );
        }

        return $query->exists();
    }

    /**
     * Récupérer les horaires accessibles.
     *
     * Étudiant :
     * - sa faculté
     * - son année académique
     * - sa promotion
     * - les horaires généraux de sa faculté
     *
     * Autres rôles :
     * - tous les horaires
     */
    public function getSchedulesFor(
        User $user,
        string $type
    ): Collection {
        $query = Schedule::with([
            'faculty',
            'promotion',
            'academicYear',
            'uploader',
        ])
        ->where(
            'type',
            $type
        )
        ->where(
            'is_active',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Admin, CP, enseignant...
        |--------------------------------------------------------------------------
        */

        if ($user->role !== 'student') {
            return $query
                ->latest()
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Étudiant
        |--------------------------------------------------------------------------
        */

        return $query
            ->where(
                'faculty_id',
                $user->faculty_id
            )
            ->where(
                'academic_year_id',
                $user->academic_year_id
            )
            ->where(function ($q) use ($user) {

                /*
                |--------------------------------------------------------------------------
                | Horaire général
                |--------------------------------------------------------------------------
                */

                $q->whereNull(
                    'promotion_id'
                )

                /*
                |--------------------------------------------------------------------------
                | OU horaire spécifique à la promotion de l'étudiant
                |--------------------------------------------------------------------------
                */

                ->orWhere(
                    'promotion_id',
                    $user->promotion_id
                );
            })
            ->latest()
            ->get();
    }

    /**
     * Supprimer un horaire.
     */
    public function delete(
        Schedule $schedule
    ): bool {
        if ($schedule->file_path) {
            Storage::disk('public')->delete(
                $schedule->file_path
            );
        }

        return $schedule->delete();
    }

    /**
     * Activer un horaire.
     *
     * Désactive les autres horaires
     * du même contexte :
     *
     * - faculté
     * - année académique
     * - type
     * - promotion
     */
    public function activate(
        Schedule $schedule
    ): void {
        DB::transaction(function () use ($schedule) {

            Schedule::where(
                'faculty_id',
                $schedule->faculty_id
            )
            ->where(
                'academic_year_id',
                $schedule->academic_year_id
            )
            ->where(
                'type',
                $schedule->type
            )

            /*
            |--------------------------------------------------------------------------
            | Même promotion
            |--------------------------------------------------------------------------
            */

            ->where(function ($q) use ($schedule) {

                if ($schedule->promotion_id) {
                    $q->where(
                        'promotion_id',
                        $schedule->promotion_id
                    );
                } else {
                    $q->whereNull(
                        'promotion_id'
                    );
                }
            })

            /*
            |--------------------------------------------------------------------------
            | Ne pas désactiver l'horaire actuel
            |--------------------------------------------------------------------------
            */

            ->where(
                'id',
                '!=',
                $schedule->id
            )

            ->update([
                'is_active' => false,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Activer l'horaire actuel
            |--------------------------------------------------------------------------
            */

            $schedule->update([
                'is_active' => true,
            ]);
        });
    }
}
