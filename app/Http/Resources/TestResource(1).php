<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Define the function to format the due date

        $arr = [];
        foreach ($this->resource as $data) {
            // foreach ($data as $test) {
                // dd($data);
                // dd($course);
                // Access the test and due_date from the $course object
                // if (isset($course->tests)) {
                    $id = $data->id;
                    $name = $data->name;
                    $created_at = $data->created_at;
                    
                   
                    $type = $data->enum;
                    $textColor ='';
                    $bgColor ='';
                    
                if($type == 1){
                    $textColor = '#1690EB';
                    $bgColor = '#EDF7FF';
                }
                elseif($type == 2){
                    $textColor = '#6750A3';
                    $bgColor = '#6750A333';
                }
                elseif($type == 3){
                    $textColor = '#85207B';
                    $bgColor = '#c596c1';
                }
                elseif($type == 4){
                    $textColor = '#FF9330';
                    $bgColor = '#FF93301A';
                }
                elseif($type == 5){
                    $textColor = '#FF9330';
                    $bgColor = '#FF93301A';
                }
                elseif($type == 6){
                    $textColor = '#FF9330';
                    $bgColor = '#FF93301A';
                }
                elseif($type == 7){
                    $textColor = '#FF9330';
                    $bgColor = '#FF93301A';
                }
                    array_push($arr, [
                        'id' => $id,
                        'name' =>$name,
                        'created_at' => $created_at,
                        'textColor' => $textColor,
                        'bgColor' => $bgColor,
                    ]);
                // }
            // }
        }
        return $arr;
    }
}
