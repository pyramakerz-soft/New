<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function program()
    {
        return $this->belongsTo(Program::class , 'program_id');
    }
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
    public function group_students()
    {
        return $this->hasMany(GroupStudent::class);
    }
    
    public function  getImageAttribute($val)
    {
        return ($val !== null) ? asset('class_images/' . $val) : "";
    }

}
