<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScheduleService
{


    /**
     * Créer un nouvel horaire.
     *
     * Empêche les doublons :
     *
     * Même :
     * - faculté
     * - année académique
     * - type (course/exam)
     * - programme
     * - promotion
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


                    'program_id' =>
                        $data['program_id'] ?? null,



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



        } catch(Throwable $e) {



            /*
            |--------------------------------------------------------------------------
            | Supprimer fichier si erreur
            |--------------------------------------------------------------------------
            */

            if($path){


                Storage::disk('public')
                    ->delete($path);


            }





            Log::error(
                'Erreur création horaire',
                [
                    'message'=>$e->getMessage()
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
        | Programme
        |--------------------------------------------------------------------------
        */

        if(!empty($data['program_id'])){


            $query->where(
                'program_id',
                $data['program_id']
            );


        }else{


            $query->whereNull(
                'program_id'
            );


        }





        /*
        |--------------------------------------------------------------------------
        | Promotion
        |--------------------------------------------------------------------------
        */

        if(!empty($data['promotion_id'])){


            $query->where(
                'promotion_id',
                $data['promotion_id']
            );


        }else{


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
     * - son programme
     * - sa promotion
     * - horaire général faculté
     *
     * Autres rôles :
     * tous les horaires
     */
    public function getSchedulesFor(
        User $user,
        string $type
    ): Collection {



        $query = Schedule::with([

            'faculty',
            'promotion',
            'program',
            'academicYear',
            'uploader'

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

        if($user->role !== 'student'){


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



            ->where(function($q) use($user){



                /*
                | Programme
                */

                if($user->program_id){


                    $q->where(
                        'program_id',
                        $user->program_id
                    );


                }





                /*
                | Promotion
                */

                $q->orWhere(
                    'promotion_id',
                    $user->promotion_id
                );






                /*
                | Horaire général faculté
                */

                $q->orWhere(function($general){


                    $general
                        ->whereNull('program_id')
                        ->whereNull('promotion_id');


                });



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



        if($schedule->file_path){


            Storage::disk('public')
                ->delete(
                    $schedule->file_path
                );


        }




        return $schedule->delete();


    }







    /**
     * Activer un horaire.
     *
     * Désactive les autres horaires
     * du même contexte.
     */
    public function activate(
        Schedule $schedule
    ): void {



        DB::transaction(function() use($schedule){



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



                ->where(function($q) use($schedule){


                    if($schedule->program_id){


                        $q->where(
                            'program_id',
                            $schedule->program_id
                        );


                    }else{


                        $q->whereNull(
                            'program_id'
                        );


                    }


                })



                ->where(function($q) use($schedule){


                    if($schedule->promotion_id){


                        $q->where(
                            'promotion_id',
                            $schedule->promotion_id
                        );


                    }else{


                        $q->whereNull(
                            'promotion_id'
                        );


                    }


                })



                ->where(
                    'id',
                    '!=',
                    $schedule->id
                )


                ->update([

                    'is_active'=>false

                ]);







            $schedule->update([

                'is_active'=>true

            ]);



        });


    }


}
