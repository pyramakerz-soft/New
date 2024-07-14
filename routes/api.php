<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfessionalDevelopmentsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\UnitsController;
use App\Http\Controllers\Api\TeachersController;
use App\Http\Controllers\Api\GameController;

use App\Http\Controllers\Api\AssignmentController;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api', 'prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, "login"]);
    Route::post('/loginTeacher', [AuthController::class, "loginTeacher"]);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/confirm-phone', [AuthController::class, 'confirmPhone']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('/updateProfile', [AuthController::class, 'updateProfile']);
        Route::post('/makeParentPin', [AuthController::class, 'makeParentPin']);
    });
});



Route::group(['namespace' => 'Api', 'middleware' => 'auth:api'], function () {

    // Route::get("units", [UnitsController::class, "units"])->name("units");
    Route::get("student-profile/{email}", [StudentController::class, "index"])->name("student-profile");
    Route::get("student_programs", [StudentController::class, "studentPrograms"])->name("student_programs");
    Route::get("student_programs_assign", [StudentController::class, "studentProgramsAssign"])->name("student_programs_assign");
    Route::post("student_programs_test", [StudentController::class, "studentPrograms_test"])->name("student_programs_test");

    Route::get("lessons/{id}", [LessonController::class, "index"])->name("lessons");
    Route::get("lesson_questions/{id}", [LessonController::class, "lessonQuestions"])->name("lesson_questions");
    Route::get("units/{id}", [UnitsController::class, "index"])->name("units");
    Route::get("getuserdata", [AuthController::class, "getUserData"])->name("getuserdata");
    Route::get("getTeacherData", [AuthController::class, "getTeacherData"])->name("getTeacherData");
    Route::post("student_progress", [StudentController::class, "StudentProgress"])->name("student_progress");
    Route::post("student_progress_by_group", [StudentController::class, "StudentProgressByGroup"])->name("student_progress_by_group");
    Route::post("assignAssessment", [StudentController::class, "assignAssessment"])->name("assignAssessment");
    Route::get("studentAssignments", [StudentController::class, "studentAssignments"])->name("studentAssignments");
    Route::get("teacherAssignments", [TeachersController::class, "teacherAssignments"])->name("teacherAssignments");
    Route::post("studentsInClass", [StudentController::class, "studentsInClass"])->name("studentsInClass");
    Route::post("TeacherAssignmentFilter", [TeachersController::class, "TeacherAssignmentFilter"])->name("TeacherAssignmentFilter");
    Route::post("teacherClasses", [TeachersController::class, "teacherClasses"])->name("teacherClasses");
    Route::post("game", [GameController::class, "game"])->name("game");
    Route::post("gameType", [GameController::class, "gameType"])->name("gameType");

    Route::post("gamebyId", [GameController::class, "gamebyId"])->name("gamebyId");
    Route::post("add_assignment_to_group", [TeachersController::class, "addAssignmentToGroup"])->name("add_assignment_to_group");
    Route::post("student_stats", [TeachersController::class, "StudentStats"])->name("student_stats");
    Route::post("testQuestions", [TeachersController::class, "testQuestions"])->name("testQuestions");
    Route::post("solveData", [GameController::class, "solveData"])->name("solveData");
    Route::post("editGame/{game_id}/{assign_id}", [TeachersController::class, "editGame"])->name("editGame");


    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('categories/{id}/all-data', [CategoryController::class, 'getAllCategoryData']);
    Route::post('/assignments', [AssignmentController::class, 'assign']);



    /////////////////////// Teacher Reports /////////////////////////////////
        Route::post('/teach-comp-report', [TeachersController::class, 'completionReport']);
        Route::post('/teach-mastery-report', [TeachersController::class, 'masteryReport']);
        Route::post('/teach-trials-report', [TeachersController::class, 'numOfTrialsReport']);
        Route::post('/teach-skill-report', [TeachersController::class, 'skillReport']); 
});
