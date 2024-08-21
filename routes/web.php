<?php

use Illuminate\Support\Facades\Route;
use App\Models\GameImage;
use App\Models\GameLetter;
use App\Models\Choice;
use App\Models\Game;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('view-video', function () {
    return view('filament.pages.view-video');
});



Route::get('/copy-game-data/{lesson_id}/{dest_lesson_id}', function ($lesson_id, $dest_lesson_id) {
    // Retrieve all games from the source lesson
    $games = Game::where('lesson_id', $lesson_id)->get();

    // Initialize an array to map old game IDs to new game IDs
    $oldToNewGameIds = [];

    // First, replicate all games to the destination lesson
    foreach ($games as $game) {
        $newGame = $game->replicate();
        $newGame->lesson_id = $dest_lesson_id;
        $newGame->save();

        // Map the old game ID to the new game ID
        $oldToNewGameIds[$game->id] = $newGame->id;
    }

    // Now, loop through the original games and replicate related data
    foreach ($games as $game) {
        $dest_game_id = $oldToNewGameIds[$game->id];

        // Create a map to track old to new IDs for game letters and images
        $oldToNewLetterIds = [];
        $oldToNewImageIds = [];

        // Copy Game Letters
        $gameLetters = GameLetter::where('game_id', $game->id)->get();
        foreach ($gameLetters as $letter) {
            $newLetter = $letter->replicate();
            $newLetter->game_id = $dest_game_id;
            $newLetter->save();

            // Track the new letter ID
            $oldToNewLetterIds[$letter->id] = $newLetter->id;
        }

        // Copy Game Images
        $gameImages = GameImage::where('game_id', $game->id)->get();
        foreach ($gameImages as $image) {
            $newImage = $image->replicate();
            $newImage->game_id = $dest_game_id;
            
            // If the image is related to a game letter, update the game_letter_id
            if (isset($oldToNewLetterIds[$image->game_letter_id])) {
                $newImage->game_letter_id = $oldToNewLetterIds[$image->game_letter_id];
            }
            
            $newImage->save();

            // Track the new image ID
            $oldToNewImageIds[$image->id] = $newImage->id;
        }

        // Copy Game Choices
        $gameChoices = Choice::where('game_id', $game->id)->get();
        foreach ($gameChoices as $choice) {
            $newChoice = $choice->replicate();
            $newChoice->game_id = $dest_game_id;
            
            // If the choice is related to a question or tool, update the IDs accordingly
            if (isset($oldToNewLetterIds[$choice->question_id])) {
                $newChoice->question_id = $oldToNewLetterIds[$choice->question_id];
            }
            
            $newChoice->save();
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Games and related data copied successfully!'
    ]);
});




Route::get('/unused', function () {
    // Step 1: Retrieve all images from the database
    $dbImages = GameImage::pluck('image')->toArray(); // Assuming 'filename' is the column storing image names
    $dbImages = str_replace('https://ambernoak.co.uk/Fillament/public/storage/','',$dbImages);
    
    // Step 2: Get all images from the public/storage folder
    $storagePath = public_path('/storage'); // The base storage path
    $folder = 'games'; // The specific folder within storage
    // $allFiles = File::allFiles($storagePath . '/' . $folder);
    $allFiles = File::allFiles($storagePath);

    // Step 3: Compare the two sets to find unused images
    $unusedImages = [];
    foreach ($allFiles as $file) {
        $fileName = $file->getFilename();
        if (!in_array($fileName, $dbImages)) {
            $unusedImages[] = $file;
        }
    }
    // dd($unusedImages);
    // dd(sizeof($unusedImages));

    // Step 4: Delete the unused images from the folder
    foreach ($unusedImages as $unusedImage) {
        File::delete($unusedImage->getPathname());
    }

    return response()->json([
        'deleted_images_count' => count($unusedImages),
        'deleted_images' => array_map(function ($file) {
            return $file->getFilename();
        }, $unusedImages),
    ]);
});
