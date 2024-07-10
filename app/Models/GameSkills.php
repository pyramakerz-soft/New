<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSkills extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function game_type()
    {
        return $this->belongsTo(GameType::class);
    }
}
