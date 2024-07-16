<?php
namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\Test;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\User;
class StudentProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Define the function to format the due date
        function formatDueDate($dueDate)
        {
            $now = Carbon::now();
            $due = Carbon::parse($dueDate);

            $diffInDays = $now->diffInDays($due, false);

            // Check if the due date is today
            if ($diffInDays === 0) {
                return "0";
            }

            // Check if the due date is tomorrow
            if ($diffInDays === 1) {
                return "1";
            }

            // Check if the due date is within a week
            if ($diffInDays > 1) {
                return "{$diffInDays}";
            }

            // If due date is more than a week, return the actual date
            return $due->toDateString();
        }

        $arr = [];
        
        $check = 1;
        foreach ($this->resource as $course) {
            // dd($course);
            $student_name = User::find($course->student_id)->name ?? '-';
            $createdDate = Carbon::parse($course->created_at);
            $created_at = date('m',strtotime($course->created_at));
            $testName = $course->tests->name;
            $testId = $course->test_id;
            $type = $course->tests->type;
            // $teacher = User::find($course->teacher_id)->name;
            $status = $course->status;
            $lessonId = $course->lesson_id;
            $lessonNum = Lesson::find($course->lesson_id)->number ?? '-';
            $lessonName = Lesson::find($course->lesson_id)->name ?? '-';
            $programId = $course->program_id;
            $programName = Program::join('courses','programs.course_id','courses.id')->where('programs.id',$course->program_id)->first()->name;
            $chapterName = Lesson::join('units','lessons.unit_id','units.id')->select('units.*')->first()->name;
            $chapterID = Lesson::join('units','lessons.unit_id','units.id')->select('units.*')->first()->id;    
            $score = $course->score;
            $star = $course->stars;
            $month= $createdDate->format('M');
            // $gameName = Lesson::with('game')->get();
                    $test = Test::where('id' , $testId)->with('game')->first();
                    


    $gameName = $test->game->inst ?? '-';

        array_push($arr, [
            'student_name' => $student_name ?? '-',
            'test_name' => $testName,
            'test_id' => $testId,
            // 'teacher_name' => $teacher,
            'type' => $type,
            'chapter_id' => $chapterID,
            'chapter_name' => $chapterName,
            'program_id' => $programId,
            'program_name' => $programName,
            'lesson_id' => $lessonId,
            'lesson_num' => $lessonNum,
            'lesson_name' => $lessonName,
            'score' => $score,
            'total_score' => 100,
            'created_at' => $created_at,
            'star'=>$star,
            'month'=>$month,
            'game_name'=>$gameName
        ]);
}



        return $arr;
    }
}
