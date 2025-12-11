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
        if (!$request->filled('lesson_id')) {
            return response()->json(['message' => 'lesson_id is required'], 422);
        }

        $user = Auth::user();
        $isAdmin = ((int) $user->role === 1);
        $userStage = null;
        if (!$isAdmin) {
            $userStage = optional(
                UserDetails::where('user_id', $user->id)->select('stage_id')->first()
            )->stage_id;
        }

        // Load the requested lesson (with program chain for stage filter)
        $lesson = Lesson::with(['unit.program'])->find($request->lesson_id);
        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        // Build base query
        $gamesQuery = Game::with([
            'gameImages',
            'gameLetters',
            'gameTypes',
            'lesson.unit.program.course',
            'lesson.unit'
        ]);

        // Adaptive-aware filtering
        if ((int) ($lesson->adaptive_flag ?? 0) === 1) {
            $gamesQuery->where(function ($q) use ($lesson) {
                $q->where('adaptive_lesson_id', $lesson->id)
                    ->orWhere('sec_adaptive_lesson_id', $lesson->id)
                    ->orWhere('lesson_id', $lesson->id); // keep if some base-linked exist
            });
        } else {
            $gamesQuery->where('lesson_id', $lesson->id);
        }

        // Pin joins to the requested lesson to derive unit/program/stage reliably
        $gamesQuery
            ->join('lessons', function ($j) use ($lesson) {
                $j->on('lessons.id', '=', \DB::raw((int) $lesson->id));
            })
            ->join('units', 'units.id', '=', 'lessons.unit_id')
            ->join('programs', 'programs.id', '=', 'units.program_id')
            ->select('games.*');

        if (!$isAdmin && $userStage) {
            $gamesQuery->where('programs.stage_id', $userStage);
        }

        $lessonId = (int) $lesson->id;
        $gamesQuery->orderByRaw("
  CASE
    WHEN games.adaptive_lesson_id = ? THEN COALESCE(games.adaptive_order, COALESCE(games.number, games.id))
    WHEN games.sec_adaptive_lesson_id = ? THEN COALESCE(games.sec_adaptive_order, COALESCE(games.number, games.id))
    WHEN games.lesson_id = ? THEN COALESCE(games.number, games.id)
    ELSE games.id
  END ASC, games.id ASC
", [$lessonId, $lessonId, $lessonId]);


        $games = $gamesQuery->get();

        // Normalize payload to always reflect the requested lesson
        foreach ($games as $g) {
            $g->setAttribute('lesson_id', $lesson->id);
            $g->setRelation('lesson', $lesson);
        }

        $data['games'] = $games;
        $data['types'] = GameType::all();

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
        $user_id = Auth::id();
        $user = User::findOrFail($user_id);
        $gender = $user->gender;

        // ---------- A) Load the requested lesson fully (for lesson/unit/program/stage) ----------
        $lesson = null;
        if ($request->filled('lesson_id')) {
            $lesson = Lesson::with(['unit.program.course'])->find($request->lesson_id);
            if (!$lesson) {
                return response()->json(['message' => 'Lesson not found'], 404);
            }
        } else {
            return response()->json(['message' => 'lesson_id is required'], 422);
        }

        // Resolve a reference $game to anchor gameTypes + audio_flag (your original logic)
        if ($request->filled('game_id')) {
            $game = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'gameChoices'])
                ->find($request->game_id);
            if (!$game)
                return response()->json(['message' => 'Game not found'], 404);
        } else {
            $gameQuery = Game::with(['gameImages', 'gameLetters', 'gameTypes', 'gameChoices']);
            if ((int) ($lesson->adaptive_flag ?? 0) === 1) {
                $game = (clone $gameQuery)->where('adaptive_lesson_id', $lesson->id)->first()
                    ?: (clone $gameQuery)->where('sec_adaptive_lesson_id', $lesson->id)->first();
            } else {
                $game = (clone $gameQuery)->where('lesson_id', $lesson->id)->first();
            }
            if (!$game)
                return response()->json(['message' => 'No games found for the provided lesson'], 404);
        }

        $gameTypeName = optional($game->gameTypes)->name;
        $audioFlag = $game->audio_flag;

        // ---------- B) Build your list query (unchanged filters) ----------
        $gamesQuery = Game::with([
            'gameImages',
            'gameLetters',
            'gameChoices',
            'gameTypes',
            'lesson.unit.program.course',
            'lesson.unit',
            'studentDegrees'
        ])
            ->whereHas('gameTypes', fn($q) => $q->where('name', $gameTypeName))
            ->where('audio_flag', $audioFlag);

        if ((int) ($lesson->adaptive_flag ?? 0) === 1) {
            $gamesQuery->where(function ($q) use ($lesson) {
                $q->where('adaptive_lesson_id', $lesson->id)
                    ->orWhere('sec_adaptive_lesson_id', $lesson->id)
                    ->orWhere('lesson_id', $lesson->id); // optional: include any base-linked
            });
        } else {
            $gamesQuery->where('lesson_id', $lesson->id);
        }

        $games = $gamesQuery->get();

        // ---------- C) Normalize each game to ALWAYS reflect the requested lesson ----------
        $stageName = optional($lesson->unit?->program?->stage)->mob_stage_name;

        foreach ($games as $g) {
            // 1) Force lesson_id in the payload
            $g->setAttribute('lesson_id', $lesson->id);

            // 2) Replace/attach the lesson relation with the requested lesson
            //    (so "lesson" in the JSON is the requested lesson, not null)
            $g->setRelation('lesson', $lesson);

            // 3) If you need stage_names like before, source it from the requested lesson
            $g->setAttribute('stage_names', $stageName);
        }

        // Gender-specific post-processing (your logic)
        if ($gameTypeName === 'Choose_Gender' || $gameTypeName === 'Drag Clothes') {
            foreach ($games as $g) {
                // Just set in-memory for response, DO NOT save()
                $g->setAttribute('correct_ans', $gender);

                foreach ($g->gameImages as $img) {
                    $img->correct = ($img->gender && $img->gender == $gender) ? 1 : 0;
                }
            }
        }


        $data['games'] = $games;
        $data['types'] = GameType::all();

        return $this->returnData('data', $data, "Game");
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
     *             @OA\Property(property="stars", type="integer", example=3),
     *             @OA\Property(property="program_id", type="integer", example=10)
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
        $studentId = auth()->id();
        $programId = $request->filled('program_id') ? (int) $request->program_id : null;

        $gameIds = array_values(array_filter(array_map(function ($id) {
            return is_numeric($id) ? (int) $id : null;
        }, (array) $request->game_id)));

        $games = empty($gameIds)
            ? collect()
            : Game::with([
                'lesson.unit.program',
                'adaptiveLesson.unit.program',
                'secAdaptiveLesson.unit.program',
            ])->whereIn('id', $gameIds)->get()->keyBy('id');

        $lastDegree = null;

        foreach ($gameIds as $game_id) {
            /** @var Game|null $game */
            $game = $games->get($game_id);
            if (!$game) {
                // Skip silently if invalid game id (or you can return an error)
                continue;
            }

            $lessonId = $request->lesson_id;
            if (!$lessonId) {
                // Without a lesson we can't attribute progress
                continue;
            }

            // Fetch the lesson model
            $lesson = Lesson::find($lessonId);
            if (!$lesson) {
                // Invalid lesson ID
                continue;
            }

            // 1) Upsert student_degrees row with the resolved lesson context
            $degree = StudentDegree::firstOrNew([
                'student_id' => $studentId,
                'game_id' => $game_id,
                'lesson_id' => $lesson->id,
            ]);
            $degree->lesson_id = $lesson->id;
            $degree->stars = $request->stars; // assuming numeric or numeric string
            $degree->save();
            $lastDegree = $degree;

            // 2) Collect ALL games that reference THIS lesson via any pointer,
            //    then de-duplicate by (game_type_id, audio_flag) using MIN(id) as a representative.
            $games_id = Game::selectRaw('MIN(id) AS id')
                ->where(function ($q) use ($lesson) {
                    $q->where('lesson_id', $lesson->id)
                        ->orWhere('adaptive_lesson_id', $lesson->id)
                        ->orWhere('sec_adaptive_lesson_id', $lesson->id);
                })
                ->groupBy('game_type_id')
                ->groupBy('audio_flag')
                ->pluck('id');

            // 3) Compute stars over the unique game set for *this* student
            //    Guard for text stars by casting to int
            $studentStars = StudentDegree::where('student_id', $studentId)
                ->whereIn('game_id', $games_id)
                ->pluck('stars')
                ->map(function ($v) {
                    // cast "3", null, "" safely to int (null/"" -> 0)
                    return (int) ($v ?? 0);
                });

            $count_games = $studentStars->count();
            $max_games = $studentStars->sum();

            // Avoid division by zero; if no solved games yet, keep lesson stars as-is (or set 0)
            $lstars = $count_games > 0 ? (int) round($max_games / $count_games) : 0;

            // 4) Update lesson stars
            $lesson->stars = $lstars;
            $lesson->save();
        }

        return $this->returnData('data', $lastDegree, "Game Completed & Lesson Stars updated");
    }

    /**
     * Resolve the lesson that should be used for a solved game.
     *
     * If a program_id is provided we pick the first lesson whose unit belongs to that program.
     * Otherwise we fall back to the controller default order (base lesson -> adaptive -> secondary).
     */
    protected function resolveLessonForGame(Game $game, ?int $programId = null): ?Lesson
    {
        $candidates = [];

        $appendCandidate = function (?int $lessonId, ?Lesson $lesson = null) use (&$candidates) {
            if (!$lessonId) {
                return;
            }

            if (!$lesson) {
                $lesson = Lesson::with('unit.program')->find($lessonId);
            }

            if ($lesson) {
                $candidates[$lessonId] = $lesson;
            }
        };

        $appendCandidate($game->lesson_id, $game->relationLoaded('lesson') ? $game->lesson : null);
        $appendCandidate($game->adaptive_lesson_id, $game->relationLoaded('adaptiveLesson') ? $game->adaptiveLesson : null);
        $appendCandidate($game->sec_adaptive_lesson_id, $game->relationLoaded('secAdaptiveLesson') ? $game->secAdaptiveLesson : null);

        if ($programId) {
            foreach ($candidates as $lesson) {
                if (optional($lesson->unit)->program_id === $programId) {
                    return $lesson;
                }
            }
        }

        return reset($candidates) ?: null;
    }



}
