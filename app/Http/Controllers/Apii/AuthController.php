<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{

    use HelpersTrait;
    //

    public function login(Request $request)
    {
        // dd('Mohamed');
        $validate = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => __('app/validation.email_required'),
            'password.required' => __('app/validation.password_required')
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }
        $token = null;
        if(User::where('role',2)->where('email',$request->email)->count() > 0){
        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json([
                "status" => false,
                "message" => "Unauthorized"
            ]);
        }
    }else
    return response()->json([
        "status" => false,
        "message" => "Wrong Account !"
    ]);
    $user = User::where('email',$request->email)->first();
        return response()->json([
            "status" => true,
            "user" => $user,
            "token" => $token
        ]);
    } // end of login

}
