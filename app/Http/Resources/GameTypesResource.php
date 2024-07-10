<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameTypesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->resource);
        $arr = array();
        foreach($this->resource as $data){
            dd($data->gameTypes->name);
            // dd($data->gameTypes->name);
            array_push($arr,[
            'groupName' => $data->gameTypes->name,
            
            // 'beginningEtestName' => $data->program->beginning->test ? $data->program->beginning->test->name : null,

           ]);
        }
        return $arr;
    }
}
