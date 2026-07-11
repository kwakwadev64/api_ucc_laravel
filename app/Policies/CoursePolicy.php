<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{


    public function view(User $user, Course $course): bool
    {

        if($user->role !== 'student'){
            return true;
        }


        return
            $user->promotion_id === $course->promotion_id
            &&
            $user->academic_year_id === $course->academic_year_id;

    }



    public function create(User $user): bool
    {
        return $user->role !== 'student';
    }



    public function update(User $user, Course $course): bool
    {
        return $user->role !== 'student';
    }



    public function delete(User $user, Course $course): bool
    {
        return $user->role !== 'student';
    }

}
