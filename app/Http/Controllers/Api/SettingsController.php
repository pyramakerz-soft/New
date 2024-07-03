<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use App\Models\ppt;
use App\Models\ProfessionalDevelopment;
use App\Models\Setting;
use App\Models\TeacherEBook;
use App\Traits\HelpersTrait;
use Auth;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use HelpersTrait;

    public function index()
    {

        $settings = Setting::get();
        return $this->returnData('data', $settings, "All Settings");

    }
    public function professionalDevelopment( $id){
        
        $data = ProfessionalDevelopment::where('unit_id' ,$id)->get();
        return $this->returnData('data', $data, "All Data");

    }
    public function teacherEBook( $id){
        
        $data = TeacherEBook::where('unit_id' ,$id)->get();
        return $this->returnData('data', $data, "All Data");

    }
    public function lessonPlan( $id){
        
        $data = LessonPlan::where('unit_id' ,$id)->get();
        return $this->returnData('data', $data, "All Data");

    }
    public function ppt( $id){
        
        $data = ppt::where('unit_id' ,$id)->get();
        return $this->returnData('data', $data, "All Data");

    }
}
