<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;

class UnitsController extends Controller
{
    use HelpersTrait;

    public function index($id)
    {

        $units = Unit::where("program_id", $id)->get();

        return $this->returnData('data', $units, "All units");
    }
}
