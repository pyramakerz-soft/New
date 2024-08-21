<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLetter extends Model
{
    use HasFactory;
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
        public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . basename($val)) : "";

    }

}
