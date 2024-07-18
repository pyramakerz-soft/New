<?php

use Illuminate\Support\Facades\Route;
use App\Models\GameImage;
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
