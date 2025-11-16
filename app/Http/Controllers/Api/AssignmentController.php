<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Game;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\User;
use App\Models\Program;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Traits\HelpersTrait;
use App\Traits\backendTraits;
class AssignmentController extends Controller
{
    use HelpersTrait;
    use backendTraits;
    public function assign(Request $request)
{
    $validatedData = $request->validate([
        'student_id'   => 'array',
        'student_id.*' => 'integer|exists:users,id',
        'group_id'     => 'array',
        'group_id.*'   => 'integer|exists:groups,id',
        'game_id'      => 'required|integer|exists:games,id',
        'lesson_id'    => 'nullable|integer|exists:lessons,id', // 👈 nullable now
        'name'         => 'required|string|max:255',
    ]);

    $teacher_id = auth()->id();

    $game = Game::with([
        'lesson.unit.program.course',
        'adaptiveLesson.unit.program.course',
        'secAdaptiveLesson.unit.program.course',
    ])->findOrFail($request->game_id);

    // Collect target students
    $targetStudentIds = collect($request->student_id ?? []);
    if (!empty($request->group_id)) {
        $groupStudentIds = GroupStudent::whereIn('group_id', (array)$request->group_id)
            ->pluck('student_id');
        $targetStudentIds = $targetStudentIds->merge($groupStudentIds);
    }
    $targetStudentIds = $targetStudentIds->unique()->values();
    if ($targetStudentIds->isEmpty()) {
        return response()->json(['message' => 'No students to assign.'], 422);
    }

    // If lesson_id is provided → NEW LOGIC
    if (!empty($request->lesson_id)) {
        $clickedLessonId = (int) $request->lesson_id;

        if (!in_array($clickedLessonId, [
            optional($game->lesson)->id,
            optional($game->adaptiveLesson)->id,
            optional($game->secAdaptiveLesson)->id,
        ])) {
            return response()->json(['message' => 'Invalid lesson_id for this game.'], 422);
        }

        $pickedLesson = Lesson::with('unit.program.course')->findOrFail($clickedLessonId);
        $program      = optional($pickedLesson->unit)->program;
        $programId    = optional($program)->id;

        if (!$programId) {
            return response()->json(['message' => 'Lesson has no valid program.'], 422);
        }

        $stage_id    = optional($program)->stage_id ?? optional($pickedLesson->unit)->stage_id;
        $course_name = optional(optional($program)->course)->name;

        $studentIdsForProgram = UserCourse::whereIn('user_id', $targetStudentIds)
            ->where('program_id', $programId)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($studentIdsForProgram->isEmpty()) {
            return response()->json(['message' => 'No selected students are enrolled in this program.'], 422);
        }

        $effectiveLessonId = $clickedLessonId;
        $pickedProgramId   = $programId;
    }
    // If lesson_id is NULL → OLD LOGIC
    else {
        // Build candidate lessons depending on adaptive mode
        $isAdaptive = (bool) ($game->adaptiveLesson || $game->secAdaptiveLesson);
        if (!$isAdaptive && $game->lesson && optional($game->lesson)->adaptive_flag == 1) {
            $isAdaptive = true;
        }

        if ($isAdaptive) {
            $candidateLessons = collect([$game->adaptiveLesson, $game->secAdaptiveLesson])->filter();
            if ($candidateLessons->isEmpty()) {
                return response()->json(['message' => 'Adaptive game has no adaptive lessons linked.'], 422);
            }
        } else {
            if (!$game->lesson) {
                return response()->json(['message' => 'Game has no base lesson to assign.'], 422);
            }
            $candidateLessons = collect([$game->lesson]);
        }

        $candidateByProgram = $candidateLessons->mapWithKeys(function ($lesson) {
            $program = optional($lesson->unit)->program;
            $programId = optional($program)->id;
            $stageId   = optional($program)->stage_id ?? optional($lesson->unit)->stage_id;

            return $programId ? [$programId => ['lesson' => $lesson, 'stage_id' => $stageId]] : [];
        });

        if ($candidateByProgram->isEmpty()) {
            return response()->json(['message' => 'No candidate lessons with a program found.'], 422);
        }

        $programCounts = UserCourse::whereIn('user_id', $targetStudentIds)
            ->whereIn('program_id', $candidateByProgram->keys())
            ->select('program_id', DB::raw('COUNT(DISTINCT user_id) as cnt'))
            ->groupBy('program_id')
            ->pluck('cnt', 'program_id');

        $pickedProgramId = $programCounts->keys()->first();
        if ($programCounts->isNotEmpty()) {
            $max = $programCounts->max();
            $topPrograms = $programCounts->filter(fn($c) => $c == $max)->keys();

            foreach ($topPrograms as $pid) {
                $programStage = Program::find($pid)->stage_id;
                if ($candidateByProgram[$pid]['stage_id'] == $programStage) {
                    $pickedProgramId = $pid;
                    break;
                }
            }
        } else {
            $pickedProgramId = collect([
                optional(optional($game->adaptiveLesson)->unit)->program_id,
                optional(optional($game->secAdaptiveLesson)->unit)->program_id,
                optional(optional($game->lesson)->unit)->program_id,
            ])->filter()->first();
        }

        if (!$pickedProgramId || !$candidateByProgram->has($pickedProgramId)) {
            return response()->json(['message' => 'Could not determine a single target program to assign.'], 422);
        }

        $pickedLessonData  = $candidateByProgram[$pickedProgramId];
        $pickedLesson      = $pickedLessonData['lesson'];
        $effectiveLessonId = $pickedLesson->id;
        $program           = optional($pickedLesson->unit)->program;
        $stage_id          = optional($program)->stage_id ?? optional($pickedLesson->unit)->stage_id;
        $course_name       = optional(optional($program)->course)->name;

        $studentIdsForProgram = UserCourse::whereIn('user_id', $targetStudentIds)
            ->where('program_id', $pickedProgramId)
            ->pluck('user_id')
            ->unique()
            ->values();
    }

    // Sibling games
    $games = Game::where(function ($q) use ($effectiveLessonId) {
                $q->where('lesson_id', $effectiveLessonId)
                  ->orWhere('adaptive_lesson_id', $effectiveLessonId)
                  ->orWhere('sec_adaptive_lesson_id', $effectiveLessonId);
            })
            ->where('game_type_id', $game->game_type_id)
            ->orderByRaw("
                CASE
                  WHEN adaptive_lesson_id = ? THEN COALESCE(adaptive_order, COALESCE(number, id))
                  WHEN sec_adaptive_lesson_id = ? THEN COALESCE(sec_adaptive_order, COALESCE(number, id))
                  WHEN lesson_id = ? THEN COALESCE(number, id)
                  ELSE id
                END ASC, id ASC
            ", [$effectiveLessonId, $effectiveLessonId, $effectiveLessonId])
            ->get();

    if ($games->isEmpty()) {
        return response()->json(['message' => 'No sibling games found for the chosen lesson.'], 404);
    }

    $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
    $dueDate   = Carbon::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d');

    $test = Test::create([
        'name'       => $request->name,
        'lesson_id'  => $effectiveLessonId,
        'program_id' => $pickedProgramId,
        'type'       => 1,
        'status'     => 1,
        'stage_id'   => $stage_id,
        'game_id'    => $request->game_id,
    ]);

    foreach ($games->pluck('id') as $gid) {
        TestQuestion::create([
            'game_id' => $gid,
            'test_id' => $test->id,
        ]);
    }

    foreach ($studentIdsForProgram as $sid) {
        StudentTest::create([
            'test_id'    => $test->id,
            'student_id' => $sid,
            'lesson_id'  => $effectiveLessonId,
            'program_id' => $pickedProgramId,
            'teacher_id' => $teacher_id,
            'start_date' => $startDate,
            'due_date'   => $dueDate,
            'status'     => 0,
        ]);

        Notification::create([
            'assignment_name' => $request->name,
            'course_name'     => $course_name,
            'user_id'         => $sid,
            'test_id'         => $test->id,
            'start_date'      => $startDate,
            'due_date'        => $dueDate,
            'is_read'         => 0,
        ]);
    }

    return response()->json(['msg' => 'Test assigned successfully'], 201);
}


public function deleteAssignment($id){
    StudentTest::where('test_id',$id)->delete();
    TestQuestion::where('test_id',$id)->delete();
    Test::find($id)->delete();
   return response()->json(['msg' => 'Test deleted successfully'], 201);
}

public function assignmentStudents($id){
    $data['done_students'] = User::whereIn('id',StudentTest::where('test_id',$id)->where('status',1)->select('student_id')->get())->select('name','gender','school_id')->get();
    $data['progress_students'] = User::whereIn('id',StudentTest::where('test_id',$id)->where('status',0)->select('student_id')->get())->select('name','gender','school_id')->get();
    
    return $this->returnData('data',$data,200);
}







}
