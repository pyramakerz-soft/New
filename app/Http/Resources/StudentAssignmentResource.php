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
        // Helper function to format the due date
        function formatDueDate($dueDate)
        {
            $now = Carbon::now()->startOfDay();
            $due = Carbon::parse($dueDate);

            $diffInDays = $now->diffInDays($due, false);

            if ($diffInDays === 0) {
                return "0"; // Today
            }

            if ($diffInDays === 1) {
                return "1"; // Tomorrow
            }

            if ($diffInDays > 1) {
                return "{$diffInDays}"; // In X days
            }

            return $due->toDateString(); // Past due or other
        }

        $arr = [];

        foreach ($this->resource as $course) {
            if (!isset($course->tests)) {
                continue;
            }

            $studentName = User::find($course->student_id)->name ?? '-';
            $testName    = $course->tests->name;
            $testId      = $course->test_id;
            $dueDate     = $course->due_date;
            $type        = $course->tests->type;
            $teacher     = User::find($course->teacher_id)->name;
            $status      = $course->status;

            $lessonId   = $course->lesson_id;
            $lessonNum  = Lesson::find($course->lesson_id)->number ?? null;
            $lessonName = Lesson::find($course->lesson_id)->name ?? null;

            $programId   = $course->program_id;
            $createdAt   = $course->start_date;
            $completedAt = $course->completed_at;
            $updatedAt   = $course->updated_at;

            $daysDifference = null;
            if ($status !== 1 && $dueDate < Carbon::now()->format('Y-m-d')) {
                $dueDateCarbon = $dueDate
                    ? Carbon::createFromFormat('Y-m-d', $dueDate)
                    : Carbon::createFromFormat('Y-m-d', '2250-05-05');

                $daysDifference = $dueDateCarbon->diffInDays(Carbon::now());
            }

            $programName = Program::join('courses', 'programs.course_id', '=', 'courses.id')
                ->where('programs.id', $course->program_id)
                ->first()
                ->name ?? '-';

            $statuss = $course->status;

            // ❗️This currently pulls the *first* unit across all lessons, not scoped to $course
            $chapter = Lesson::join('units', 'lessons.unit_id', '=', 'units.id')
                ->select('units.*')
                ->first();

            $chapterName = $chapter->name ?? null;
            $chapterID   = $chapter->id ?? null;

            $status_enum = $course->status_enum;

            // Status mapping
            if ($status == 1) {
                $status = 'Completed';
            } elseif ($dueDate < date('Y-m-d')) {
                $status = 'Overdue';
            } else {
                $status = 'Pending';
            }

            // Color coding by type
            $textColor = '';
            $bgColor   = '';

            switch ($type) {
                case 1:
                    $textColor = '#1690EB';
                    $bgColor   = '#EDF7FF';
                    break;
                case 2:
                    $textColor = '#6750A3';
                    $bgColor   = '#6750A333';
                    break;
                case 3:
                    $textColor = '#85207B';
                    $bgColor   = '#85207B1A';
                    break;
                case 4:
                case 5:
                case 6:
                case 7:
                    $textColor = '#FF9330';
                    $bgColor   = '#FF93301A';
                    break;
            }

            $arr[] = [
                'student_name'        => $studentName,
                'test_name'           => $testName,
                'test_id'             => $testId,
                'teacher_name'        => $teacher,
                'status'              => $status,
                'status_enum'         => $statuss,
                'type'                => $type,
                'chapter_id'          => $chapterID,
                'chapter_name'        => $chapterName,
                'program_id'          => $programId,
                'program_name'        => $programName,
                'lesson_id'           => $lessonId,
                'lesson_num'          => $lessonNum,
                'lesson_name'         => $lessonName,
                'textColor'           => $textColor,
                'bgColor'             => $bgColor,
                'formatted_due_date'  => formatDueDate($dueDate),
                'days_left'           => date('j F', strtotime($dueDate)),
                'days_difference'     => $daysDifference > 0 ? $daysDifference . ' days' : $daysDifference,
                'completed_at'        => $completedAt ? Carbon::parse($completedAt)->format('j F Y') : null,
                'created_at'          => $createdAt,
                'updated_at'          => $updatedAt,
            ];
        }

        return $arr;
    }
}
