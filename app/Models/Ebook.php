<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
class Ebook extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function hasFile()
    {
        return !is_null($this->file_path);
    }
    public function getFilePathAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : null;
    }
    protected static function boot()
    {
        parent::boot();
//https://ambernoak.co.uk/Fillament/public/storage/book/Ar-EC2-SB-Green/
        static::saved(function ($model) {
            $filePath = $model->file_path; // Path to the uploaded .zip file
            $tt = str_replace('https://ambernoak.co.uk/Fillament/public/storage/',"",$model->file_path);
            // dd($filePath,Storage::disk('public')->exists($tt));
            if ($filePath && Storage::disk('public')->exists($tt)) {
                $zip = new ZipArchive;
                $zipPath = Storage::disk('public')->path($tt);
                // Define the extraction path: public/storage/book/{zip_filename}
                $fileNameWithoutExtension = pathinfo($filePath, PATHINFO_FILENAME);
                $extractPath = Storage::disk('public')->path('book/');

                // Create the directory if it doesn't exist
                if (!Storage::disk('public')->exists('book/' . $fileNameWithoutExtension)) {
                    Storage::disk('public')->makeDirectory('book/' . $fileNameWithoutExtension);
                }

                // Extract the zip file
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();
// dd('asd');
                    // Optionally, delete the original .zip file after extraction
                    Storage::disk('public')->delete($tt);
                } else {
                    throw new \Exception('Failed to extract the zip file.');
                }
                // dd($zip,$zipPath,$extractPath,$fileNameWithoutExtension);
            }
            // dd($filePath);
        });
    }
    
}
