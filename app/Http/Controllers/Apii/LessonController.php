<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\Question;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    use HelpersTrait;

    public function index($id)
    {
        // $units = Unit::all();
        $lessons = Lesson::where("unit_id", $id)->get();
        return $this->returnData('data', $lessons, "All lessons");
    }
    public function lessonQuestions($id){
        $data['questions'] = Question::join('lessons','questions.lesson_id','lessons.id')->where('lessons.id',$id)->select('questions.*')->get();
        return $this->returnData('data', $data, "All questions");
    }
}
