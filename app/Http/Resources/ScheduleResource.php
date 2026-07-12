<?php

namespace App\Http\Resources;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau JSON.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,


            /*
            |--------------------------------------------------------------------------
            | Informations sur l'horaire
            |--------------------------------------------------------------------------
            */

            'title' => $this->title,

            'type' => [
                'value' => $this->type,
                'label' => match ($this->type) {
                    Schedule::TYPE_COURSE => 'Horaire des cours',
                    Schedule::TYPE_EXAM => 'Horaire des examens',
                    default => $this->type,
                },
            ],

            'file_url' => $this->file_path
                ? asset('storage/' . $this->file_path)
                : null,

            'file_type' => $this->file_type,

            'is_active' => $this->is_active,



            /*
            |--------------------------------------------------------------------------
            | Année académique
            |--------------------------------------------------------------------------
            */

            'academic_year' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ],



            /*
            |--------------------------------------------------------------------------
            | Faculté
            |--------------------------------------------------------------------------
            */

            'faculty' => [
                'id' => $this->faculty?->id,
                'name' => $this->faculty?->name,
            ],



            /*
            |--------------------------------------------------------------------------
            | Promotion (peut être null)
            |--------------------------------------------------------------------------
            */

            'promotion' => $this->when(
                $this->promotion,
                [
                    'id' => $this->promotion?->id,
                    'name' => $this->promotion?->name,
                ]
            ),



            /*
            |--------------------------------------------------------------------------
            | Programme / Filière (peut être null)
            |--------------------------------------------------------------------------
            */

            'program' => $this->when(
                $this->program,
                [
                    'id' => $this->program?->id,
                    'name' => $this->program?->name,
                ]
            ),



            /*
            |--------------------------------------------------------------------------
            | Auteur de publication
            |--------------------------------------------------------------------------
            */

            'uploaded_by' => $this->when(
                $this->uploader,
                [
                    'id' => $this->uploader?->id,
                    'name' => trim(
                        ($this->uploader?->first_name ?? '')
                        . ' '
                        . ($this->uploader?->last_name ?? '')
                    ),
                ]
            ),



            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
