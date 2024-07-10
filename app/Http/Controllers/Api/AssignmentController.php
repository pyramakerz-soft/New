<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function assign(Request $request)
    {



        // $validator = Validator::make($request->all(), [
        //     'game_id' => 'required|exists:games,id',
        //     'student_ids' => 'nullable|array',
        //     'student_ids.*' => 'exists:users,id',
        //     'group_ids' => 'nullable|array',
        //     'group_ids.*' => 'exists:groups,id',
        //     'name' => 'required|string',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }

        // if (!$request->has('student_ids') && !$request->has('group_ids')) {
        //     return response()->json(['error' => 'You must provide either student_ids or group_ids.'], 400);
        // }

        // if ($request->has('student_ids') && $request->has('group_ids')) {
        //     return response()->json(['error' => 'You cannot provide both student_ids and group_ids.'], 400);
        // }

        // $game_id = $request->game_id;
        // $name = $request->name;
        // $assignments = [];

        // if ($request->has('student_ids')) {
        //     foreach ($request->student_ids as $student_id) {
        //         $assignments[] = Assignment::create([
        //             'game_id' => $game_id,
        //             'user_id' => $student_id,
        //             'name' => $name,
        //         ]);
        //     }
        // }

        // if ($request->has('group_ids')) {
        //     foreach ($request->group_ids as $group_id) {
        //         $assignments[] = Assignment::create([
        //             'game_id' => $game_id,
        //             'group_id' => $group_id,
        //             'name' => $name,
        //         ]);
        //     }
        // }

        // return response()->json(['message' => 'Assignments created successfully', 'assignments' => $assignments], 201);


        // $validator = Validator::make($request->all(), [
        //     'game_id' => 'required|exists:games,id',
        //     'student_ids' => 'nullable|array',
        //     'student_ids.*' => 'exists:users,id',
        //     'group_ids' => 'nullable|array',
        //     'group_ids.*' => 'exists:groups,id',
        //     'name' => 'required|string',
        // ]);
        // $game_id = $request->game_id;
        // $name = $request->name;
        // $assignments = [];
        // foreach ($request->student_ids as $student_id) {
        //     $assignments[] = Test::create([
        //         'game_id' => $game_id,
        //         'user_id' => $student_id,
        //         'name' => $name,
        //     ]);
        // }
        // return response()->json(['message' => 'Assignments created successfully', 'assignments' => $assignments], 201);


    }
}
