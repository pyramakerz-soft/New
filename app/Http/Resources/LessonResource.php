<?php

namespace App\Http\Resources;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // dd($this->resource); // <- remove this
        $arr = [];
        $i = 0;

        foreach ($this->resource as $data) {
            $i++;

            $type = match ($data->type ?? null) {
                '0' => 'Checkpoint',
                '1' => 'Review',
                '2' => 'Assessment',
                default => null,
            };

            // 🔹 Merge games from both relationships
            $games = ($data->relationLoaded('game') ? $data->game : collect())
                ->concat($data->relationLoaded('adaptiveGame') ? $data->adaptiveGame : collect())
                ->concat($data->relationLoaded('secAdaptiveGame') ? $data->secAdaptiveGame : collect())
                ->values(); // reindex keys
                // after: $games = (...) ->values();  // reindex keys

$lessonId = (int) ($data->id ?? 0);

$games = $games->sortBy(function ($g) use ($lessonId) {
    // numeric-or-null → int, with "null last"
    $nn = function ($v, $fallback = 999999999) {
        return is_numeric($v) ? (int) $v : $fallback;
    };

    // Determine which pointer matches this lesson, assign a priority bucket
    if ((int) $g->adaptive_lesson_id === $lessonId) {
        $priority = 0;
        $primary  = $nn($g->adaptive_order, $nn($g->number, $g->id));
    } elseif ((int) $g->sec_adaptive_lesson_id === $lessonId) {
        $priority = 1;
        $primary  = $nn($g->sec_adaptive_order, $nn($g->number, $g->id));
    } elseif ((int) $g->lesson_id === $lessonId) {
        $priority = 2;
        $primary  = $nn($g->number, $g->id);
    } else {
        $priority = 3;
        $primary  = $nn($g->id);
    }

    // tie-breaker by id for stable ordering
    $secondary = $nn($g->id);

    // return a sortable key; zero-pad to keep lexicographic order correct
    return sprintf('%02d-%010d-%010d', $priority, $primary, $secondary);
})->values();


            $gamesWithStars = [];
            $gamesWithStarss = [];
            $total_stars = 0;
            $solved_games_count = 0;
            $types = [];

            foreach ($games as $game) {
                $stars = 0;

                // avoid repeated where() calls
                $degree = optional($game->studentDegrees)
                    ?->where('student_id', auth()->id())
                    ->where('game_id', $game->id)
                    ->first();

                $stars = (int) ($degree->stars ?? 0);

                if (!in_array($game->game_type_id, $types, true)) {
                    $gamesWithStars[] = [
                        'id' => $game->id,
                        'name' => $game->name,
                        // NOTE: for adaptive records, lesson_id might point to the base lesson.
                        // If you want the "current" lesson, you can also add: 'effective_lesson_id' => $data->id
                        'lesson_id' => $data->id,
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
                        'is_active' => $game->is_active,
                        'number' => $game->number,
                        'game_voice' => $game->game_voice,
                        'voice_flag' => isset($game->game_voice) ? '1' : '0',
                    ];
                    $total_stars += $stars;
                    $solved_games_count++;
                }

                $gamesWithStarss[] = [
                    'id' => $game->id,
                    'name' => $game->name,
                    'lesson_id' => $data->id,
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
                    'is_active' => $game->is_active,
                    'number' => $game->number,
                    'game_voice' => $game->game_voice,
                    'voice_flag' => isset($game->game_voice) ? '1' : '0',
                ];

                $types[] = $game->game_type_id;
            }

            $lesson_stars = $solved_games_count > 0 ? (int) round($total_stars / $solved_games_count) : 0;

            $arr[] = [
                'id' => $data->id,
                'name' => $data->name,
                'number' => $data->number,
                'main_letter' => $data->main_letter,
                'warmup_id' => $data->warmup_id,
                'unit_id' => $data->unit_id,
                'stars' => $lesson_stars,
                'chapter' => Unit::find($data->unit_id),
                'type' => $type,
                // 🔹 Expose the merged list under "games"
                'games' => $gamesWithStarss,
                'lesson_stars' => $lesson_stars,
            ];
        }

        return $arr;
    }
}
