<?php

namespace App\Http\Resources;

use App\Models\Unit;
use App\Models\GameType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $arr = [];
        foreach ($this->resource as $data) {
            $type = '';
            if ($data->type == '0') {
                $type = 'Checkpoint';
            } elseif ($data->type == '1') {
                $type = 'Review';
            } elseif ($data->type == '2') {
                $type = 'Assessment';
            } else {
                $type = null;
            }

            // Retrieve games and their stars
            $gamesWithStars = [];
            $total_stars =0;
            foreach ($data->game as $game) {
                $stars = 0;
                
                    $stars = isset($game->studentDegrees[0]->stars) ? (int)$game->studentDegrees[0]->stars : 0;
                    $total_stars += $stars;
                $gamesWithStars[] = [
                    'id' => $game->id,
                    'name' => $game->name,
                    'lesson_id' => $game->lesson_id,
                    'inst' => $game->inst,
                    'game_type_id' => $game->game_type_id,
                    'audio_flag' => $game->audio_flag,
                    'num_of_letters' => $game->num_of_letters,
                    'num_of_letter_repeat' => $game->num_of_letter_repeat,
                    'num_of_trials' => $game->num_of_trials,
                    'created_at' => $game->created_at,
                    'updated_at' => $game->updated_at,
                    'main_letter' => $game->main_letter,
                    'stars' => $stars,
                    'prev_game_id' => $game->prev_game_id,
                    'next_game_id' => $game->next_game_id,
                    'correct_ans' => $game->correct_ans,
                    'is_edited' => $game->is_edited,
                    'game_types' => $game->gameTypes,
                ];
            }
            $lesson_stars = ceil($total_stars/sizeof($gamesWithStars));
            $arr[] = [
                'id' => $data->id,
                'name' => $data->name,
                'number' => $data->number,
                'main_letter' => $data->main_letter,
                'warmup_id' => $data->warmup_id,
                'unit_id' => $data->unit_id,
                'stars' => $data->studentDegrees->stars ?? 0,
                'chapter' => Unit::find($data->unit_id),
                'type' => $type,
                'games' => $gamesWithStars,
                'lesson_stars' => $lesson_stars,
                // 'beginningEtestName' => $data->program->beginning->test ? $data->program->beginning->test->name : null,
            ];
        }
        return $arr;
    }
}
