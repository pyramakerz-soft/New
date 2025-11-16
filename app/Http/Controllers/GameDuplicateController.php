<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameLetter;
use App\Models\GameImage;
use App\Models\Choice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GameDuplicateController extends Controller
{
    public function duplicate(Request $request)
    {
        $data = $request->validate([
            'game_ids'              => ['required','array','min:1'],
            'game_ids.*'            => ['integer','distinct','exists:games,id'],
            'override'              => ['sometimes','array'],
            'override.lesson_id'    => ['nullable','integer','exists:lessons,id'],
            // Add more override fields as you need (e.g., game_type_id, audio_flag, etc.)
        ]);

        $gameIds   = $data['game_ids'];
        $overrides = $data['override'] ?? [];

        // We’ll return the mapping of old->new game IDs and per-game nested mappings
        $result = [
            'games' => [],                // [oldGameId => newGameId]
            'letters' => [],              // [oldGameId => [oldLetterId => newLetterId]]
            'images'  => [],              // [oldGameId => [oldImageId  => newImageId]]
            'choices' => [],              // [oldGameId => [oldChoiceId => newChoiceId]]
        ];

        // Eager-load relations once to minimize queries
        $games = Game::with([
            'letters',            // hasMany GameLetter
            'images',             // hasMany GameImage
            'choices',            // hasMany Choice
        ])->whereIn('id', $gameIds)->get()->keyBy('id');

        if ($games->isEmpty()) {
            return response()->json([
                'message' => 'No games found to duplicate.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // -------- Pass 1: create shallow clones of games & collect ID maps --------
            foreach ($games as $oldGameId => $game) {
                $newGame = $game->replicate([
                    // fields to IGNORE during replicate (leave empty to copy everything)
                    // e.g. you may exclude unique fields if you have any
                ]);

                // Apply allowed overrides (only if present)
                foreach (['lesson_id','adaptive_lesson_id','sec_adaptive_lesson_id'] as $k) {
                    if (array_key_exists($k, $overrides)) {
                        $newGame->{$k} = $overrides[$k];
                    }
                }

                // Reset fields that should not carry over (optional; adjust to your rules)
                // $newGame->is_edited = 0;

                $newGame->save();

                $result['games'][$oldGameId] = $newGame->id;

                // Prepare nested maps containers
                $result['letters'][$oldGameId] = [];
                $result['images'][$oldGameId]  = [];
                $result['choices'][$oldGameId] = [];
            }

            // -------- Pass 2: clone letters per game --------
            foreach ($games as $oldGameId => $game) {
                $newGameId = $result['games'][$oldGameId];

                foreach ($game->letters as $oldLetter) {
                    $newLetter           = $oldLetter->replicate();
                    $newLetter->game_id  = $newGameId;
                    $newLetter->save();

                    $result['letters'][$oldGameId][$oldLetter->id] = $newLetter->id;
                }
            }

            // -------- Pass 3: clone images per game (remap game_letter_id) --------
            foreach ($games as $oldGameId => $game) {
                $newGameId = $result['games'][$oldGameId];

                foreach ($game->images as $oldImg) {
                    $newImg           = $oldImg->replicate();
                    $newImg->game_id  = $newGameId;

                    // Remap if linked to a letter that was duplicated with this game
                    if (!empty($oldImg->game_letter_id) &&
                        isset($result['letters'][$oldGameId][$oldImg->game_letter_id])) {
                        $newImg->game_letter_id = $result['letters'][$oldGameId][$oldImg->game_letter_id];
                    } else {
                        // If the image references a letter outside this game, keep as-is
                        // or set to null depending on your data rules
                        // $newImg->game_letter_id = null;
                    }

                    $newImg->save();
                    $result['images'][$oldGameId][$oldImg->id] = $newImg->id;
                }
            }

            // -------- Pass 4: clone choices per game (remap question_id if it was a letter) --------
            foreach ($games as $oldGameId => $game) {
                $newGameId = $result['games'][$oldGameId];

                foreach ($game->choices as $oldChoice) {
                    $newChoice           = $oldChoice->replicate();
                    $newChoice->game_id  = $newGameId;
                    

                    $newChoice->save();
                    $result['choices'][$oldGameId][$oldChoice->id] = $newChoice->id;
                }
            }

            // -------- Pass 5: fix prev/next links among the *new* clones only --------
            // If the original game pointed to another original that is also being cloned,
            // point the *new* clone to that clone (not the old).
            foreach ($games as $oldGameId => $game) {
                $newGameId = $result['games'][$oldGameId];
                /** @var Game $newGame */
                $newGame = Game::findOrFail($newGameId);

                // prev
                if (!empty($game->prev_game_id) && isset($result['games'][$game->prev_game_id])) {
                    $newGame->prev_game_id = $result['games'][$game->prev_game_id];
                } else {
                    // optional: clear prev if original linked outside batch
                    // $newGame->prev_game_id = null;
                }

                // next
                if (!empty($game->next_game_id) && isset($result['games'][$game->next_game_id])) {
                    $newGame->next_game_id = $result['games'][$game->next_game_id];
                } else {
                    // optional: clear next if original linked outside batch
                    // $newGame->next_game_id = null;
                }

                $newGame->save();
            }

            DB::commit();

            return response()->json([
                'message'      => 'Games duplicated successfully.',
                'map'          => [
                    'game'    => $result['games'],
                    'letters' => $result['letters'],
                    'images'  => $result['images'],
                    'choices' => $result['choices'],
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Duplication failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
