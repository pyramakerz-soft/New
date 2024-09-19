<?php

namespace App\Http\Resources;

use App\Models\TeacherProgram;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $arr = array();
        $loc = asset('storage/');

        // Fetch user data
        $arr['user'] = User::with('school')->where('id', $this->resource->id)->first();

        // Fetch active programs and units
        $arr['program_data'] = TeacherProgram::with([
            'program.units' => function($query) {
                $query->where('is_active', 1); // Only get active units
            }, 
            'program.units.lessons',
            'stage'
        ])
        ->where('teacher_id', $this->resource->id)
        ->whereHas('program', function($query) {
            $query->where('is_active', 1); // Only get active programs
        })
        ->get()
        ->map(function($teacherProgram) {
            // Append program name and image
            $teacherProgram->program_name = $teacherProgram->program->name . ' - ' . $teacherProgram->stage->name;
            $teacherProgram->image = $teacherProgram->program->image;

            // Units are already filtered by the query, no need to filter again
            return $teacherProgram;
        });

        return $arr;
    }
}
