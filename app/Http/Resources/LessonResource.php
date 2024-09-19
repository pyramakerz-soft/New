<?php
namespace App\Http\Resources;
use App\Models\Unit;
use App\Models\Lesson;
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
            $total_stars = 0;
            $solved_games_count = 0;
            $types = array();
            foreach ($data->game as $game) {
                $stars = 0;
                
                // Check if student degree exists for the authenticated user+
                // if($game->id == 2230)
                // dd(isset($game->studentDegrees[0]) && $game->studentDegrees[0]->student_id == auth()->id()) ;
                if (isset($game->studentDegrees->where('student_id',auth()->user()->id)->where('game_id',$game->id)->first()->stars)) {
                    $stars = isset($game->studentDegrees->where('student_id',auth()->user()->id)->where('game_id',$game->id)->first()->stars) ? (int)$game->studentDegrees->where('student_id',auth()->user()->id)->where('game_id',$game->id)->first()->stars : 0;
                }

                // Only count stars from solved games (games where stars > 0)
                // if ($stars > 0) {
                //     $total_stars += $stars;
                //     $solved_games_count++;
                // }
                if(!in_array($game->game_type_id,$types)){
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
                    'is_active' => $game->is_active,
                    'number' => $game->number,
                ];
                $total_stars += $stars;
                    $solved_games_count++;
                }
                 $gamesWithStarss[] = [
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
                    'is_active' => $game->is_active,
                    'number' => $game->number,
                ];
                
                $types[] = $game->game_type_id;
                
            }

            // Calculate the average stars only for solved games
            $lesson_stars = $solved_games_count > 0 ? round($total_stars / $solved_games_count) : 0;

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
                'games' => $gamesWithStars,
                'lesson_stars' => $lesson_stars,
            ];
        }

        return $arr;
    }
}