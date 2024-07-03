<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        $arr = array();
        foreach($this->resource[0] as $data){
            // dd($data->program->beginning->doc);
            array_push($arr,[
            'groupName' => $data->name,
            'programName' => $data->program->name,
            'course_id' => $data->program->course_id,
            'courseName' => Course::find($data->program->course_id)->name,
            'programImage' => $data->program->image,

            'beginningDoc' => $data->program->beginning ? $data->program->beginning->doc : null,
            'beginningTest' => $data->program->beginning ? $data->program->beginning->test : null,
            // 'beginningEtestName' => $data->program->beginning->test ? $data->program->beginning->test->name : null,

           ]);
        }
        return $arr;
    }
}
