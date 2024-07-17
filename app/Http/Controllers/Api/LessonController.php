<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\GameType;
use App\Models\Question;
use App\Models\Unit;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    use HelpersTrait;

    /**
     * @OA\Get(
     *     path="/api/lessons/{id}",
     *     summary="Get lessons by unit ID",
     *     tags={"Lesson"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Unit ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lessons fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index($id)
    {
        // $units = Unit::all();
        $data['lessons'] = Lesson::with(['game', 'game.gameTypes'])->where("unit_id", $id)->orderBy('number')->get();
        $data['lessons']->each(function ($lesson) {
            $lesson->game = $lesson->game->sortBy('number');
        });
        $resource = LessonResource::make($data['lessons']);
        $data['types'] = GameType::all();
        return $this->returnData('data', $resource, "All lessons");
    }

    /**
     * @OA\Get(
     *     path="/api/lesson_questions/{id}",
     *     summary="Get questions of a lesson",
     *     tags={"Lesson"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lesson ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Questions fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function lessonQuestions($id)
    {
        $data['questions'] = Question::join('lessons', 'questions.lesson_id', 'lessons.id')->where('lessons.id', $id)->select('questions.*')->get();
        return $this->returnData('data', $data, "All questions");
    }
    /**
     * @OA\Get(
     *     path="/api/lesson/get-complete-status/{id}",
     *     summary="Get complete status of a lesson",
     *     tags={"Lesson"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lesson ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson status fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function getCompleteStatus($id)
    {
        $data['status'] = Lesson::find($id)->status;
        return $this->returnData('data', $data, "Lesson Status");
    }
}
