<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;


class ScheduleController extends Controller
{


    public function __construct(
        private ScheduleService $scheduleService
    ) {}





    /*
    |--------------------------------------------------------------------------
    | HORAIRES DES COURS
    |--------------------------------------------------------------------------
    */



    /**
     * Liste des horaires de cours.
     */
    public function indexCourses(Request $request)
    {

        try {


            $schedules = $this->scheduleService
                ->getSchedulesFor(
                    $request->user(),
                    Schedule::TYPE_COURSE
                );



            return response()->json([

                'success'=>true,

                'data'=>ScheduleResource::collection($schedules)

            ]);



        } catch(Throwable $e) {


            Log::error(
                'Erreur récupération horaires cours : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>'Impossible de récupérer les horaires des cours.'

            ],500);


        }

    }






    /**
     * Création horaire de cours.
     */
    public function storeCourse(
        StoreScheduleRequest $request
    )
    {

        try {


            $this->authorize(
                'create',
                Schedule::class
            );



            $data = $request->validated();



            /*
            |--------------------------------------------------------------------------
            | Le backend impose le type
            |--------------------------------------------------------------------------
            */

            $data['type'] = Schedule::TYPE_COURSE;




            $schedule = $this->scheduleService
                ->create(

                    $data,

                    $request->file('file'),

                    $request->user()

                );





            return response()->json([

                'success'=>true,

                'message'=>'Horaire des cours ajouté avec succès.',

                'data'=>new ScheduleResource($schedule)

            ],201);




        } catch(Throwable $e) {


            Log::error(
                'Erreur création horaire cours : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>$e->getMessage()

            ],422);


        }

    }









    /*
    |--------------------------------------------------------------------------
    | HORAIRES DES EXAMENS
    |--------------------------------------------------------------------------
    */





    /**
     * Liste des horaires d'examen.
     */
    public function indexExams(
        Request $request
    )
    {

        try {


            $schedules = $this->scheduleService
                ->getSchedulesFor(

                    $request->user(),

                    Schedule::TYPE_EXAM

                );




            return response()->json([

                'success'=>true,

                'data'=>ScheduleResource::collection($schedules)

            ]);




        } catch(Throwable $e) {


            Log::error(
                'Erreur récupération horaires examens : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>'Impossible de récupérer les horaires d\'examens.'

            ],500);



        }

    }







    /**
     * Création horaire examen.
     */
    public function storeExam(
        StoreScheduleRequest $request
    )
    {

        try {



            $this->authorize(
                'create',
                Schedule::class
            );



            $data = $request->validated();




            /*
            |--------------------------------------------------------------------------
            | Type imposé par le backend
            |--------------------------------------------------------------------------
            */

            $data['type'] = Schedule::TYPE_EXAM;






            $schedule = $this->scheduleService
                ->create(

                    $data,

                    $request->file('file'),

                    $request->user()

                );






            return response()->json([

                'success'=>true,

                'message'=>'Horaire d\'examen ajouté avec succès.',

                'data'=>new ScheduleResource($schedule)

            ],201);





        } catch(Throwable $e) {



            Log::error(
                'Erreur création horaire examen : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>$e->getMessage()

            ],422);



        }

    }









    /*
    |--------------------------------------------------------------------------
    | ACTIONS COMMUNES
    |--------------------------------------------------------------------------
    */





    /**
     * Afficher un horaire.
     */
    public function show(
        Schedule $schedule
    )
    {

        try {



            $this->authorize(
                'view',
                $schedule
            );




            $schedule->load([

                'faculty',

                'promotion',

                'program',

                'academicYear',

                'uploader'

            ]);






            return response()->json([

                'success'=>true,

                'data'=>new ScheduleResource($schedule)

            ]);





        } catch(Throwable $e) {


            return response()->json([

                'success'=>false,

                'message'=>'Accès refusé.'

            ],403);



        }

    }








    /**
     * Modifier un horaire.
     */
    public function update(
        UpdateScheduleRequest $request,
        Schedule $schedule
    )
    {

        try {



            $this->authorize(
                'update',
                $schedule
            );




            $data = $request->validated();




            /*
            |--------------------------------------------------------------------------
            | Le type ne peut pas être modifié
            |--------------------------------------------------------------------------
            */

            unset($data['type']);






            $schedule->update($data);






            return response()->json([

                'success'=>true,

                'message'=>'Horaire modifié.',

                'data'=>new ScheduleResource($schedule)

            ]);






        } catch(Throwable $e) {



            Log::error(
                'Erreur modification horaire : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>$e->getMessage()

            ],422);



        }

    }








    /**
     * Supprimer un horaire.
     */
    public function destroy(
        Schedule $schedule
    )
    {

        try {



            $this->authorize(
                'delete',
                $schedule
            );




            $this->scheduleService
                ->delete($schedule);






            return response()->json([

                'success'=>true,

                'message'=>'Horaire supprimé.'

            ]);





        } catch(Throwable $e) {



            Log::error(
                'Erreur suppression horaire : '.$e->getMessage()
            );



            return response()->json([

                'success'=>false,

                'message'=>'Suppression impossible.'

            ],500);



        }

    }



}
