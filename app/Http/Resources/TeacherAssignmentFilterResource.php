<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\StudentTest;
use App\Models\TestQuestion;
use App\Models\Lesson;
use App\Models\Test;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentFilterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Define an array to store unique test IDs
        $uniqueTestIds = [];
        $uniqueLessonIds = [];
        $uniqueTeacherIds = [];

        // Define the array to store the final result
        $arr = [];

        foreach ($this->resource as $data) {
            
            // Check if the test_id is already in the uniqueTestIds array
            if (in_array($data->test_id, $uniqueTestIds) && in_array($data->lesson_id, $uniqueLessonIds) && in_array($data->teacher_id, $uniqueTeacherIds)) {
                // Skip this entry if the test_id is already processed
                continue;
            }
            
            // Add the test_id to the uniqueTestIds array to mark it as processed
            $uniqueTestIds[] = $data->test_id;
            $uniqueLessonIds[] = $data->lesson_id;
            $uniqueTeacherIds[] = $data->teacher_id;

            // Extract the necessary data
            $id = $data->id;
            $teacher_id = $data->test_id;
            $name = $data->tests->name;
            $due_date = $data->due_date;
            $diff = $data->tests->difficulty_level;
            $image = Test::find($data->test_id)->image;
            $question_count = TestQuestion::where('test_id',$data->test_id)->count();
            // $student_count = StudentTest::where('test_id',$data->test_id)->where('teacher_id',$data->teacher_id)->where('lesson_id',$data->lesson_id)->count();
            // Fetch the chapter name
            
            $chapterName = Lesson::join('units', 'lessons.unit_id', 'units.id')
                ->where('lessons.id', $data->lesson_id)
                ->select('units.name')
                ->first()
                ->name;
            // Add the data to the array
            $arr[] = [
                'id' => $id,
                'test_id' => $teacher_id,
                'name' => $name,
                'image' => $image,
                'created_at' => date('Y-n-d', strtotime($due_date)),
                'created_at_form' => date('D, M. Y', strtotime($due_date)),
                'chapter_name' => $chapterName,
                'diff_lvl' => $diff,
                'question_count' => $question_count,
                // 'students_count' => $student_count,
                'num_of_students_done' => StudentTest::where('test_id', $data->test_id)
                    ->where('due_date', $data->due_date)
                    ->where('status', 1)
                    ->count(),
                'total_num_of_students' => StudentTest::where('test_id', $data->test_id)
                    ->where('due_date', $data->due_date)
                    ->count(),
            ];
        }

        return $arr;
    }
}
