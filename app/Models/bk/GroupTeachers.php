<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class GroupTeachers extends Model
{
    protected $fillable = ['group_id', 'teacher_id', 'co_teacher_id', 'program_id', 'stage_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function coTeacher()
    {
        return $this->belongsTo(User::class, 'co_teacher_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}

