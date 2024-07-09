<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;

class UnitsController extends Controller
{
    use HelpersTrait;

    public function units(Request $request)
    {

        $units = Unit::select('id', 'name', 'program_id')->get();

        return $this->returnData('data', $units, "All units");
    }
}
