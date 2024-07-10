<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Game;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Lesson;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\TestQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function assign(Request $request)
    {


        $validatedData = $request->validate([
            'student_id' => 'array',
            'student_id.*' => 'integer|exists:users,id',
            'group_id' => 'array',
            'group_id.*' => 'integer|exists:groups,id',
            'game_id' => 'required|integer|exists:games,id',
            'name' => 'required|string|max:255',
        ]);

        $teacher_id = auth()->id();
        $game = Game::findOrFail($request->game_id);
        $group = Group::find($request->group_id);
        $lesson_id = $game->lesson_id;


        $lesson = Lesson::find($lesson_id);
        if ($lesson) {
            $unit = $lesson->unit;
            if ($unit) {
                $program_id = $unit->program_id;
                $stage_id = $unit->stage_id;
            }
        }


        $games = Game::where('lesson_id', $lesson_id)
            ->join('lessons', 'lessons.id', 'games.lesson_id')
            ->join('units', 'units.id', 'lessons.unit_id')
            ->join('programs', 'programs.id', 'units.program_id')
            ->where('game_type_id', $game->game_type_id)
            ->select('games.*')
            ->get();

        $games_id = $games->pluck('id');

        $test = Test::create([
            'name' => $request->name,
            'lesson_id' => $lesson_id,
            'program_id' => $program_id,
            'type' => 1,
            'status' => 1,
            'stage_id' => $stage_id,
        ]);

        foreach ($games_id as $game_id) {
            TestQuestion::create([
                'game_id' => $game_id,
                'test_id' => $test->id,
            ]);
        }

        $current_time = now();
        foreach ($request->student_id ?? [] as $student_id) {
            StudentTest::create([
                'test_id' => $test->id,
                'student_id' => $student_id,
                'lesson_id' => $lesson_id,
                'program_id' => $program_id,
                'teacher_id' => $teacher_id,
                'start_date' => date('Y-m-d', strtotime($request->start_date)),
                'due_date' => date('Y-m-d', strtotime($request->due_date)),
                'status' => 0,

            ]);
        }

        foreach ($request->group_id ?? [] as $group_id) {
            $students_in_group = GroupStudent::where('group_id', $group_id)->get();
            $group = Group::find($group_id);
            if ($students_in_group->count() > 0) {
                foreach ($students_in_group as $student) {
                    StudentTest::create([
                        'test_id' => $test->id,
                        'group_id' => $group_id,
                        'student_id' => $student->student_id,
                        'lesson_id' => $lesson_id,
                        'program_id' => $program_id,
                        'teacher_id' => $teacher_id,
                        'start_date' => date('Y-m-d', strtotime($request->start_date)),
                        'due_date' => date('Y-m-d', strtotime($request->due_date)),
                        'status' => 0,

                    ]);
                }
            }

        }
        return response()->json(['message' => 'Test assigned successfully'], 201);
    }
}
