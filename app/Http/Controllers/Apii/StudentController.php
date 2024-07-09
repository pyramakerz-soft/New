<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Program;
use App\Models\User;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use HelpersTrait;
    public function index($email)
    {
        // $students = User::where('email',$email)->first();
        // // $groupStudent = GroupStudent::where("student_id",$students->id)->get()->pluck('group_id');
        // $groupStudent = $students->groups->pluck('program.name', 'name');

        // return $this->returnData('data', $groupStudent, "All students");
        $students = User::where('email', $email)->first();
        $groupStudent = GroupStudent::where("student_id", $students->id)->get()->pluck('group_id');
        $arr1 = array();
        $data = array();
        foreach ($groupStudent as $group) {
            $groups = Group::find($group);
            $program = Group::where('program_id',$groups->program_id)->with(['program','program.beginning.test','program.benchmark.test','program.ending.test','program.units.lessons'])->first();
            // dd($program);
            array_push($arr1,$program);
        }

        // $groupNames = [];
        // $arr = array();
        // foreach ($groupStudent as $groupId) {
        //     $group = Group::find($groupId);
        //     if ($group) {
        //         $groupNames[] = $group->name;
        //     }
        // }
        array_push($data, $arr1);
            // $data = StudentResource::make($data);
        return $this->returnData('data', $data, "All groups for the student");
    }

    public function studentPrograms(){
        $programs = User::with(['userCourses.program','userCourses.program.course'])->where('id',auth()->user()->id)->first();
        return $this->returnData('data', $programs, "All groups for the student");

    }
}
