<?php
/**
 * @OA\OpenApi(
 *     @OA\SecurityRequirement(securityScheme="bearerAuth")
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT Bearer token **_only_**"
 * )
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\StudentTest;
use App\Models\TeacherProgram;
use App\Models\StudentLock;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\TeacherAssignmentResource;
use App\Traits\HelpersTrait;
use App\Traits\backendTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;

/**
 * @OA\Info(title="Mindbuzz APIs", version="99.9999")
 */
class AuthController extends Controller
{
    use HelpersTrait;
    use backendTraits;

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Log in a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(Request $request)
    {
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
        if (User::where('role', 2)->where('email', $request->email)->count() > 0) {
            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    "status" => false,
                    "message" => "Wrong Email or password!"
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Wrong Email or password!"
            ]);
        }

        $data['user'] = User::with('school')->where('email', $request->email)->first();
        $data['token'] = $token;
        $unreadCount = Notification::where('user_id', auth()->user()->id)
            ->where('is_read', 0)
            ->count();
        $data['user']->count = $unreadCount;

        $studentsDidAss = StudentTest::where('student_id', auth()->user()->id)
            ->where('student_tests.status', 0)
            ->orderBy('due_date', 'ASC')
            ->get();

        $data['assignments'] = TeacherAssignmentResource::make($studentsDidAss);

        return $this->returnData('data', $data, 'User Data to update');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/makeParentPin",
     *     summary="Make Parent Pin",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="parent_pin", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parent pin created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function makeParentPin(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $user->parent_pin = $request->parent_pin;
        $user->save();
        return $this->returnData('data', $user, 'Parent Pin');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/loginTeacher",
     *     summary="Login Teacher",
     *     tags={"Auth-Teach"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="teacher@example.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="role", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error")
     *         )
     *     )
     * )
     */
   public function loginTeacher(Request $request)
{
    $validate = Validator::make($request->all(), [
        'email' => 'required',
        'password' => 'required',
        'role' => 'required',
    ], [
        'email.required' => __('app/validation.email_required'),
        'password.required' => __('app/validation.password_required'),
        'role.required' => __('app/validation.role_required')
    ]);

    if ($validate->fails()) {
        $code = $this->returnCodeAccordingToInput($validate);
        return $this->returnValidationError($code, $validate);
    }

    $token = null;
    if ($request->role == 1) {
        if (User::where('role', 1)->where('email', $request->email)->count() > 0) {
            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    "status" => false,
                    "message" => "Wrong Email or password"
                ]);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Wrong Email or password!"
            ]);
        }
    }

    $data['user'] = User::with(['school'])->where('email', $request->email)->first();
    $loc = asset('storage/');

    $data['program_data'] = TeacherProgram::with([
        'program.units' => function($query) {
            $query->where('is_active', 1); // Only get active units
        }, 
        'program.units.lessons', 
        'stage'
    ])
    ->where('teacher_id', $data['user']->id)
    ->whereHas('program', function ($query) {
        $query->where('is_active', 1); // Only get active programs
    })
    ->get()
    ->map(function ($teacherProgram) {
        // Append program and stage name
        $teacherProgram->program_name = $teacherProgram->program->name . ' - ' . $teacherProgram->program->stage->name;
        $teacherProgram->image = $teacherProgram->program->image;

        // Units are already filtered by the query, no need to filter again
        return $teacherProgram;
    });

    $data['token'] = $token;

    return $this->returnData('data', $data, 'User Data to update');
}


    /**
     * @OA\Get(
     *     path="/api/getuserdata",
     *     summary="Get User Data",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User data retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getUserData()
    {
        $data['user'] = User::with(['school', 'details.stage', 'teacher_programs.program.stage', 'teacher_programs.program.units.lessons'])->find(auth()->user()->id);
        $studentsDidAss = StudentTest::where('student_id', auth()->user()->id)
            ->where('student_tests.status', 0)
            ->orderBy('due_date', 'ASC')
            ->get();
        $unreadCount = Notification::where('user_id', auth()->user()->id)
            ->where('is_read', 0)
            ->count();
        $data['user']->count = $unreadCount;


        $data['assignments'] = TeacherAssignmentResource::make($studentsDidAss);
        return $this->returnData('data', $data, 'User Data');
    }

    /**
     * @OA\Get(
     *     path="/api/getTeacherData",
     *     summary="Get Teacher Data",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Teacher data retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Not a teacher",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not a teacher!")
     *         )
     *     )
     * )
     */
    public function getTeacherData()
    {
        $data = User::with(['school', 'details.stage', 'teacher_programs'])->where('role', 1)->find(auth()->user()->id);
        if (!$data) {
            return response()->json([
                "status" => false,
                "message" => "You are not a teacher!"
            ]);
        }

        $data = TeacherResource::make($data);
        return $this->returnData('data', $data, 'Teacher Data');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/updateProfile",
     *     summary="Update Profile",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+123456789"),
     *             @OA\Property(property="country_code", type="string", example="US"),
     *             @OA\Property(property="photo", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $validate = Validator::make($request->all(), [
            'name' => 'nullable',
            'email' => 'nullable',
            'phone' => 'nullable',
            'country_code' => 'nullable',
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }
        if ($request->filled('name'))
            $user->name = $request->name;
        if ($request->filled('email'))
            $user->email = $request->email;
        if ($request->filled('phone'))
            $user->parent_phone = $request->phone;
        if ($request->filled('country_code'))
            $user->country_code = $request->country_code;
        if ($request->photo) {
            $user->parent_image = $this->upploadImage($request->photo, 'profile_images');
        }
        $user->save();
        return $this->returnData('data', $user, 'User Data to update');
    }
}
