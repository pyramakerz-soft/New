<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use App\Http\Resources\UnitResource;


class UnitsController extends Controller
{
    use HelpersTrait;

    /**
 * @OA\Get(
 *     path="/api/units/{id}",
 *     summary="Get units by program ID",
 *     tags={"Units"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Program ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Units fetched successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="message", type="string")
 *         )
 *     )
 * )
 */
    public function index($id)
    {

        $units = Unit::where("program_id", $id)->orderBy('number','ASC')->get();
        $units = UnitResource::make($units);
        return $this->returnData('data', $units, "All units");
    }
}
