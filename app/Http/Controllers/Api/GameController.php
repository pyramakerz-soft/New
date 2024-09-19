<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameType;
use App\Models\StudentDegree;
use App\Models\UserDetails;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use App\Http\Resources\GameTypesResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    use HelpersTrait;

    /**
     * @OA\Post(
     *     path="/api/game",
     *     summary="Get Games by Lesson ID",
     *     tags={"Game"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="lesson_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Games retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function game(Request $request)
    {
        $user_id = Auth::user()->id;
        if(auth()->user()->role != 1)
        $userStage = UserDetails::where('user_id', $user_id)->select('stage_id')->first()->stage_id;
        
        if(auth()->user()->role != 1)
        $data['games'] = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit'])
            ->where('lesson_id', $request->lesson_id)
            ->orderBy('prev_game_id', 'asc')
            ->join('lessons', 'lessons.id', 'games.lesson_id')
            ->join('units', 'units.id', 'lessons.unit_id')
            ->join('programs', 'programs.id', 'units.program_id')
            ->where('programs.stage_id', $userStage)
            ->select('games.*')
            ->get();
            else
        $data['games'] = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit'])
            ->where('lesson_id', $request->lesson_id)
            ->orderBy('prev_game_id', 'asc')
            ->join('lessons', 'lessons.id', 'games.lesson_id')
            ->join('units', 'units.id', 'lessons.unit_id')
            ->join('programs', 'programs.id', 'units.program_id')
            // ->where('programs.stage_id', $userStage)
            ->select('games.*')
            ->get();
            
            
        $data['types'] = GameType::all();
            // $data['gameTypes'] = GameTypesResource::make($data['games']);
            
        return $this->returnData('data', $data, "Game");
    }


    /**
     * @OA\Post(
     *     path="/api/gamebyId",
     *     summary="Get Game by ID",
     *     tags={"Game"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="game_id", type="integer", example=1),
     *             @OA\Property(property="lesson_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Game retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Game not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Game not found")
     *         )
     *     )
     * )
     */
    public function gamebyId(Request $request)
    {
        
              $user_id = Auth::user()->id;
        $user = User::find($user_id);
        $gender = $user->gender;
        // if(auth()->user()->role != 1)
        $userStage = UserDetails::where('user_id', $user_id)->select('stage_id')->first()->stage_id;


        if ($request->filled('game_id')) {
            $game = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'gameChoices'])->where('id', $request->game_id)->first();
            if (!$game) {
                return response()->json(['message' => 'Game not found'], 404);
            }
        } elseif ($request->filled('lesson_id') && !$request->filled('game_id')) {
            $game = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'gameChoices'])->where('lesson_id', $request->lesson_id)->first();
        }
        $gameTypeName = $game->gameTypes->name;
        $audioFlag = $game->audio_flag;
        // if(auth()->user()->role != 1)
        $games = Game::with(['gameImages', 'gameLetters','gameChoices', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit','studentDegrees'])
            ->whereHas('gameTypes', function ($query) use ($gameTypeName) {
                $query->where('name', $gameTypeName);
            })->where('lesson_id', $request->lesson_id)->where('audio_flag', $audioFlag)
            ->join('lessons', 'lessons.id', 'games.lesson_id')
            ->join('units', 'units.id', 'lessons.unit_id')
            ->join('programs', 'programs.id', 'units.program_id')
            ->join('stages','programs.stage_id','stages.id')
            // ->where('programs.stage_id', $userStage)
            ->select('games.*','stages.mob_stage_name as stage_names')
            ->get();
        //     else
        // $games = Game::with(['gameImages', 'gameLetters','gameChoices', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit','studentDegrees'])
        //     ->whereHas('gameTypes', function ($query) use ($gameTypeName) {
        //         $query->where('name', $gameTypeName);
        //     })->where('lesson_id', $request->lesson_id)->where('audio_flag', $audioFlag)
        //         ->join('lessons', 'lessons.id', 'games.lesson_id')
        //     ->join('units', 'units.id', 'lessons.unit_id')
        //     ->join('programs', 'programs.id', 'units.program_id')
        //     ->join('stages','programs.stage_id','stages.id')
        //     ->where('programs.stage_id', $userStage)
        //     ->select('games.*','stages.mob_stage_name as stage_name')
        //     ->select('games.*')
        //     ->get();
        if ($gameTypeName == 'Choose_Gender') {
            foreach ($games as $game) {
                $game->correct_ans = $gender;
                $game->save();
            }
        }
        //     else
        // $games = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit','studentDegrees'])
        //     ->whereHas('gameTypes', function ($query) use ($gameTypeName) {
        //         $query->where('name', $gameTypeName);
        //     })->where('lesson_id', $request->lesson_id)->where('audio_flag', $audioFlag)
        //     ->join('lessons', 'lessons.id', 'games.lesson_id')
        //     ->join('units', 'units.id', 'lessons.unit_id')
        //     ->join('programs', 'programs.id', 'units.program_id')
        //     // ->where('programs.stage_id', $userStage)
        //     ->select('games.*')
        //     ->get();

        $data['games'] = $games;
        $data['types'] = GameType::all();
        return $this->returnData('data', $data, "Game");
        
        
        
        // $user_id = Auth::user()->id;
        // // if(auth()->user()->role != 1)
        // $userStage = UserDetails::where('user_id', $user_id)->select('stage_id')->first()->stage_id;

        
        // if($request->filled('game_id')){
        // $game = Game::with(['gameImages', 'gameLetters', 'gameTypes','gameChoices'])->where('id', $request->game_id)->first();
        // if (!$game) {
        //     return response()->json(['message' => 'Game not found'], 404);
        // }
        // }elseif($request->filled('lesson_id') && !$request->filled('game_id')){
        //     $game = Game::with(['gameImages', 'gameLetters', 'gameTypes','gameChoices'])->where('lesson_id', $request->lesson_id)->first();
        // }
        // $gameTypeName = $game->gameTypes->name;
        // $audioFlag = $game->audio_flag;
        // // if(auth()->user()->role != 1)
        // $games = Game::with(['gameImages', 'gameLetters','gameChoices', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit','studentDegrees'])
        //     ->whereHas('gameTypes', function ($query) use ($gameTypeName) {
        //         $query->where('name', $gameTypeName);
        //     })->where('lesson_id', $request->lesson_id)->where('audio_flag', $audioFlag)
        //     ->join('lessons', 'lessons.id', 'games.lesson_id')
        //     ->join('units', 'units.id', 'lessons.unit_id')
        //     ->join('programs', 'programs.id', 'units.program_id')
        //     ->join('stages','programs.stage_id','stages.id')
        //     ->where('programs.stage_id', $userStage)
        //     ->select('games.*','stages.mob_stage_name as stage_name')
        //     ->get();
        // //     else
        // // $games = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'lesson.unit.program.course', 'lesson.unit','studentDegrees'])
        // //     ->whereHas('gameTypes', function ($query) use ($gameTypeName) {
        // //         $query->where('name', $gameTypeName);
        // //     })->where('lesson_id', $request->lesson_id)->where('audio_flag', $audioFlag)
        // //     ->join('lessons', 'lessons.id', 'games.lesson_id')
        // //     ->join('units', 'units.id', 'lessons.unit_id')
        // //     ->join('programs', 'programs.id', 'units.program_id')
        // //     // ->where('programs.stage_id', $userStage)
        // //     ->select('games.*')
        // //     ->get();
            
        // $data['games'] = $games;
        // $data['types'] = GameType::all();
        // return $this->returnData('data', $data, "Game");
    }

    /**
     * @OA\Post(
     *     path="/api/game/complete",
     *     summary="Complete a Game",
     *     tags={"Game"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="game_id", type="integer", example=1),
     *             @OA\Property(property="stars", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Game completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function completeGame(Request $request)
    {
        $game = Game::find($request->game_id);
        $game->stars = $request->stars;
        $game->save();
        
        
        return $this->returnData('data', $game, "Game Completed");
    }

    /**
     * @OA\Post(
     *     path="/api/solveData",
     *     summary="Submit Game Solutions",
     *     tags={"Game"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="game_id", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *             @OA\Property(property="stars", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Game solutions submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
public function solveData(Request $request)
{
    
    //  $games = Game::where('lesson_id', $lesson_id)
    //         ->join('lessons', 'lessons.id', 'games.lesson_id')
    //         ->join('units', 'units.id', 'lessons.unit_id')
    //         ->join('programs', 'programs.id', 'units.program_id')
    //         ->where('game_type_id', $game->game_type_id)
    //         ->select('games.*')
    //         ->get();
    foreach($request->game_id as $game_id) {
        if(StudentDegree::where('student_id', auth()->user()->id)->where('game_id', $game_id)->count() > 0) {
            $new = StudentDegree::where('student_id', auth()->user()->id)->where('game_id', $game_id)->first();
            $new->game_id = $game_id;
            $new->stars = $request->stars;
            $new->student_id = auth()->user()->id;
            $new->update();
        } else {
            $new = new StudentDegree();
            $new->game_id = $game_id;
            $new->stars = $request->stars;
            $new->student_id = auth()->user()->id;
            $new->save();
        }

        $lesson = Lesson::find(Game::find($game_id)->lesson_id);

        // Get unique game IDs by game_type_id using a subquery
        $games_id = Game::selectRaw('MIN(id) as id')
                        ->where('lesson_id', $lesson->id)
                        ->groupBy('game_type_id')->groupBy('audio_flag')
                        ->pluck('id');

        $count_games = StudentDegree::whereIn('game_id', $games_id)
                                    ->where('student_id', auth()->user()->id)
                                    ->count();

        $max_games = StudentDegree::whereIn('game_id', $games_id)
                                  ->where('student_id', auth()->user()->id)
                                  ->sum('stars');

        $lstars = round($max_games / $count_games);
        // dd($lstars,$max_games,$count_games);
        $lesson->stars = $lstars;
        $lesson->update();
    }
    return $this->returnData('data', $new, "Game Completed & Lesson Stars updated");
}


}
