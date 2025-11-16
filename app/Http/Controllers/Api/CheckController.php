<?php

namespace App\Http\Controllers\Api;
use App\Models\CheckUpdate;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use App\Http\Controllers\Controller;
class CheckController extends Controller
{
    use HelpersTrait;
    public function checkUpdate(){
        $data['student'] =CheckUpdate::orderBy('created_at','DESC')->where('teach_or_stud','Student')->first();
        $data['teacher'] =CheckUpdate::orderBy('created_at','DESC')->where('teach_or_stud','Teacher')->first();
        return $this->returnData('data', $data, "Checker");
    }
}
