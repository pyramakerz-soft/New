<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\StudentTest;
use App\Models\Test;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentResource extends JsonResource
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
                // Define an array to store unique test IDs
        $uniqueTestIds = [];
        $uniqueLessonIds = [];
        $uniqueTeacherIds = [];
        $uniqueDueDate = [];
        foreach ($this->resource as $data) {
 if (in_array($data->test_id, $uniqueTestIds) && in_array($data->lesson_id, $uniqueLessonIds) && in_array($data->teacher_id, $uniqueTeacherIds) && in_array($data->due_date, $uniqueDueDate)) {
                // Skip this entry if the test_id is already processed
                continue;
            }
            if($data->due_date < date('Y-m-d',strtotime(now()))){
                // dd($data->due_date,date('Y-m-d',strtotime(now())));
                continue;
            }
            
            // Add the test_id to the uniqueTestIds array to mark it as processed
            $uniqueTestIds[] = $data->test_id;
            $uniqueLessonIds[] = $data->lesson_id;
            $uniqueTeacherIds[] = $data->teacher_id;
            $uniqueDueDate[] = $data->due_date;
            
                    $id = $data->id;
                    $teacher_id = $data->test_id;
                    $name = $data->tests->name;
                    $due_date = $data->due_date;
                    $diff = $data->tests->difficulty_level;
                    $image = Test::find($data->test_id)->image;

                
                    array_push($arr, [
                        'id' => $id,
                        'test_id' => $teacher_id,
                        'name' =>$name,
                        'image' => Test::find($data->test_id)->image,
                        'created_at' => date('Y-n-j',strtotime($due_date)),
                        'created_at_form' => date('Y-n-j',strtotime($due_date)),
                        'diff_lvl' => $diff,
                        'num_of_students_done' => StudentTest::where('test_id',$data->test_id)->where('due_date',$data->due_date)->where('status',1)->count(),
                        'total_num_of_students' => StudentTest::where('test_id',$data->test_id)->where('due_date',$data->due_date)->count(),
                    ]);
        }
        return $arr;
    }
}
