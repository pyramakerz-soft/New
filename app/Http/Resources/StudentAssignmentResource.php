<?php
namespace App\Http\Resources;

use App\Models\Lesson;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\User;
class StudentAssignmentResource extends JsonResource
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
            $now = Carbon::now()->startOfDay();
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
            
            // foreach ($data as $course) {
                
                // Access the test and due_date from the $course object
                if (isset($course->tests)) {
                    $studentName = User::find($course->student_id)->name ?? '-';
                    $testName = $course->tests->name;
                    $testId = $course->test_id;
                    $dueDate = $course->due_date;
                    $type = $course->tests->type;
                    $teacher = User::find($course->teacher_id)->name;
                    $status = $course->status;
                    $lessonId = $course->lesson_id;
                    $lessonNum = Lesson::find($course->lesson_id)->number;
                    $lessonName = Lesson::find($course->lesson_id)->name;
                    $programId = $course->program_id;
                    $createdAt = $course->created_at;
                    $completedAt = $course->completed_at;
                    $updatedAt = $course->updated_at;
                     $daysDifference = null;
                if ($status !== 1 && $dueDate < Carbon::now()->format('Y-m-d')) {
                    if(!$dueDate)
                    $dueDateCarbon = Carbon::createFromFormat('Y-m-d', '2029-05-5');
                else
                    $dueDateCarbon = Carbon::createFromFormat('Y-m-d', $dueDate);
                    $daysDifference = $dueDateCarbon->diffInDays(Carbon::now());
                }
                    $programName = Program::join('courses','programs.course_id','courses.id')->where('programs.id',$course->program_id)->first()->name;
             $statuss = $course->status; 
             $chapterName = Lesson::join('units','lessons.unit_id','units.id')->select('units.*')->first()->name;
             $chapterID = Lesson::join('units','lessons.unit_id','units.id')->select('units.*')->first()->id;
             
             
             
                    $status_enum = $course->status_enum;
                    // dd($dueDate,date('Y-m-d'));
                    
                    $duedate = \Carbon\Carbon::parse($dueDate);
                    $now = \Carbon\Carbon::parse(date('Y-m-d'));   
                   
                    if($status == 1){
                        $status = 'Completed';
                    }
                    elseif($dueDate < date('Y-m-d')){
                       $status = 'Overdue';
                    }
                    // elseif($duedate->diffInDays($now) < 2){
                    //     $status = 'Due Soon';
                    // }
                    
                    else
                    $status = "Pending";

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
                    $bgColor = '#85207B1A';
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
                        'student_name' => $studentName ?? '-',
                        'test_name' => $testName,
                        'test_id' => $testId,
                        'teacher_name' => $teacher,
                        'status' => $status,
                        'status_enum' => $statuss,
                        'type' => $type,
                        'chapter_id' => $chapterID,
                        'chapter_name' => $chapterName,
                        'program_id' => $programId,
                        'program_name' => $programName,
                        'lesson_id' => $lessonId,
                        'lesson_num' => $lessonNum,
                        'lesson_name' => $lessonName,
                        'textColor' => $textColor,
                        'bgColor' => $bgColor,
                        'formatted_due_date' => formatDueDate($dueDate),
                        'days_left' => date('j F',strtotime($dueDate)),
                        'days_difference'=> $daysDifference > 0 ? $daysDifference.' days':$daysDifference,

                        
                        'completed_at' => $completedAt ? Carbon::parse($completedAt)->format('j F Y') : null,

                        
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                        
                    ]);
                // }
            }
        }
        return $arr;
    }
}
