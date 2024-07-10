<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Game;
use App\Models\Group;
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
        $group = Group::findOrFail($request->group_id);
        $lesson_id = $game->lesson_id;

        $program_id = null;
        $stage_id = null;

        if (!empty($request->group_id)) {
            $group = Group::find($request->group_id[0]);
            $program_id = $group->program_id;
            $stage_id = $group->stage_id;
        }

        $games = Game::where('lesson_id', $lesson_id)
            ->join('lessons', 'lessons.id', 'games.lesson_id')
            ->join('units', 'units.id', 'lessons.unit_id')
            ->join('programs', 'programs.id', 'units.program_id')
            ->where('game_type_id', $game->game_type_id)
            ->select('games.*')
            ->get();

        $games_id = $games->pluck('id');
        $ids_array = [];
        foreach ($games_id as $game_id) {
            $ids_array[] = $game_id;
        }
        // dd($game_id);
        $test = Test::create([
            'name' => $request->name,
            'lesson_id' => $lesson_id,
            'program_id' => $program_id,
            'type' => 1,
            'status' => 1,
            'stage_id' => $stage_id,
            'game_id' => $request->game_id
        ]);

        TestQuestion::create([
            'game_id' => $ids_array,
            'test_id' => $test->id,
        ]);

        $current_time = now();
        foreach ($request->student_id ?? [] as $student_id) {
            StudentTest::create([
                'test_id' => $test->id,
                'student_id' => $student_id,
                'lesson_id' => $lesson_id,
                'program_id' => $program_id,
                'teacher_id' => $teacher_id,
                'start_date' => $current_time,
            ]);
        }

        foreach ($request->group_id ?? [] as $group_id) {
            dd($group_id);
            $group = Group::find($group_id);
            StudentTest::create([
                'test_id' => $test->id,
                'group_id' => $group_id,
                'student_id' => $student_id,
                'lesson_id' => $lesson_id,
                'program_id' => $group->program_id,
                'teacher_id' => $teacher_id,
                // 'stage_id' => $group->stage_id,
                'start_date' => $current_time,
            ]);
        }
        return response()->json(['message' => 'Test assigned successfully'], 201);
    }
}
