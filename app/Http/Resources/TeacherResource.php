<?php

namespace App\Http\Resources;

use App\Models\Course;
use App\Models\TeacherProgram;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

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
        // dd($this->resource);
        // foreach ($this->resource as $data) {
            // dd($data->program->beginning->doc);
            // dd($data)
            // array_push($arr, [
                // 'name' => $data->name,
                // 'image' => $data->parent_image,
                // 'school_name' => School::find($data->school_id)->name,
                     // $arr['programs'] = TeacherProgram::join('programs', 'teacher_programs.program_id', 'programs.id')->join('courses', 'programs.course_id', 'courses.id')->where('teacher_id',$this->resource->id)->select('programs.id as id', 'courses.name as name', DB::raw("CONCAT('$loc/', programs.image) AS image"))->get();
                // $arr['grades'] = TeacherProgram::with(['stage'])
                //     ->where('teacher_id',$this->resource->id)
                //     ->get()

                //     ->unique('stage.id')
                //     ->values();
                //     $arr['school'] = $this->resource->school;
                

            // ]);
        // }
                
        $arr['user'] =  User::where('id', $this->resource->id)->first();
        $arr['program_data'] = TeacherProgram::with(['program.units.lessons' , 'stage'])->where('teacher_id', $this->resource->id)->get()->map(function($teacherProgram) {
            $teacherProgram->program_name = $teacherProgram->program->name . ' - ' . $teacherProgram->stage->name;
           $teacherProgram->image = $teacherProgram->program->image;
            
            return $teacherProgram ;
        });
 
        return $arr;
    }
}
