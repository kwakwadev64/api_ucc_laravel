<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;


class CourseController extends Controller
{


    /**
     * Liste des cours
     */
    public function index()
    {

        $user = auth()->user();


        $query = Course::with([
            'teacher',
            'promotion',
            'academicYear'
        ]);



        /**
         * Un étudiant voit uniquement
         * les cours de sa promotion
         * et son année académique
         */
        if($user->role === 'student'){

            $query->where('promotion_id', $user->promotion_id)
                  ->where('academic_year_id', $user->academic_year_id);

        }



        return response()->json(
            $query->latest()->paginate(15)
        );

    }




    /**
     * Voir un cours
     */
    public function show(Course $course)
    {

        $this->authorize(
            'view',
            $course
        );


        return response()->json(
            $course->load([
                'teacher',
                'promotion',
                'academicYear'
            ])
        );

    }





    /**
     * Créer un cours
     */
    public function store(StoreCourseRequest $request)
    {

        $this->authorize('create', Course::class);



        $data = $request->validated();



        /**
         * Upload du fichier
         */
        if($request->hasFile('file')){


            $file = $request->file('file');


            $data['file_path'] =
                $file->store(
                    'courses',
                    'public'
                );


            $data['file_type'] =
                $file->getClientOriginalExtension();

        }



        unset($data['file']);



        /**
         * Le créateur devient enseignant
         */
        if(auth()->user()->role === 'teacher'){

            $data['teacher_id'] = auth()->id();

        }



        $course = Course::create($data);



        return response()->json([

            'message' => 'Cours créé avec succès',

            'course' => $course

        ],201);

    }





    /**
     * Modifier un cours
     */
    public function update(
        UpdateCourseRequest $request,
        Course $course
    )
    {


        $this->authorize(
            'update',
            $course
        );



        $data = $request->validated();



        if($request->hasFile('file')){


            // Supprimer ancien fichier
            if($course->file_path){

                Storage::disk('public')
                    ->delete($course->file_path);

            }



            $file = $request->file('file');


            $data['file_path'] =
                $file->store(
                    'courses',
                    'public'
                );


            $data['file_type'] =
                $file->getClientOriginalExtension();

        }



        unset($data['file']);



        $course->update($data);



        return response()->json([

            'message'=>'Cours modifié avec succès',

            'course'=>$course

        ]);

    }





    /**
     * Supprimer un cours
     */
    public function destroy(Course $course)
    {


        $this->authorize(
            'delete',
            $course
        );



        if($course->file_path){

            Storage::disk('public')
                ->delete($course->file_path);

        }



        $course->delete();



        return response()->json([

            'message'=>'Cours supprimé avec succès'

        ]);

    }





    /**
     * Télécharger un cours
     */
    public function download(Course $course)
    {


        $this->authorize(
            'view',
            $course
        );



        if(
            !$course->file_path ||
            !Storage::disk('public')
                ->exists($course->file_path)
        ){

            return response()->json([

                'message'=>'Fichier introuvable'

            ],404);

        }



        return Storage::disk('public')
            ->download(
                $course->file_path
            );

    }


}
