<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAssignmentResource;
use App\Http\Resources\TestResource;
use App\Http\Resources\StudentProgressResource;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\Game;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Notification;
use App\Models\StudentDegree;
use App\Models\Unit;
use App\Models\Program;
use App\Models\StudentTest;
use App\Models\Lesson;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestTypes;
use App\Models\User;
use App\Models\Stage;
use App\Models\Course;
use App\Models\StudentProgress;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class StudentController extends Controller
{
    use HelpersTrait;
    /**
     * @OA\Get(
     *     path="/api/student-profile/{email}",
     *     summary="Get all groups for the student by email",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Email of the student",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All groups for the student", 
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function index($email)
    {
        // $students = User::where('email',$email)->first();
        // // $groupStudent = GroupStudent::where("student_id",$students->id)->get()->pluck('group_id');
        // $groupStudent = $students->groups->pluck('program.name', 'name');

        // return $this->returnData('data', $groupStudent, "All students");
        $students = User::where('email', $email)->where('email', '!=', 'dummy@hidden.com')->first();

        $groupStudent = GroupStudent::where("student_id", $students->id)->get()->pluck('group_id');
        $arr1 = array();
        $data = array();
        foreach ($groupStudent as $group) {
            $groups = Group::find($group);
            $program = Group::where('program_id', $groups->program_id)->with(['program', 'program.beginning.test', 'program.benchmark.test', 'program.ending.test', 'program.units.lessons'])->first();
            array_push($arr1, $program);
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
    /**
     * @OA\Get(
     *     path="/api/studentAssignments",
     *     summary="Get assignments for the student",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Student assignments",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function studentAssignments()
    {

        $studentsDidAss = StudentTest::where('student_id', auth()->user()->id)->where('student_tests.status', 0)->where('student_tests.due_date', '>=', date('Y-m-d', strtotime(now())))->where('student_tests.start_date', '<=', date('Y-m-d', strtotime(now())))->get();

        $data = TeacherAssignmentResource::make($studentsDidAss);

        return $this->returnData('data', $data, "Student Assignments ");
    }
    public function studentAssignmentsGames(Request $request)
    {
        // $studentTest = StudentTest::where('student_id', auth()->user()->id)->select('test_id')->get();
        $studentsDidAss = StudentTest::where('student_tests.program_id', $request->program_id)
        ->where('student_tests.id', $request->test_id)
        ->where('student_id', auth()->user()->id)->where('student_tests.status', 0)
        ->where('student_tests.due_date', '>=', date('Y-m-d', strtotime(now())))
        ->where('student_tests.start_date', '<=', date('Y-m-d', strtotime(now())))
        ->join('tests','student_tests.test_id','tests.id')
        ->join('programs', 'programs.id', 'student_tests.program_id')
        ->join('stages','programs.stage_id','stages.id')
        ->select('student_tests.*','stages.mob_stage_name as stage_name')->get();
        // dd($studentsDidAss);

        $testQuestions = TestQuestion::whereIn('test_id', $studentsDidAss->pluck('test_id'))->with(['game.gameImages', 'game.gameLetters', 'game.gameTypes','game.gameChoices','game.lesson.unit.program.course'])
        ->join('tests','test_questions.test_id','tests.id')
        ->join('programs', 'programs.id', 'tests.program_id')
        ->join('stages','programs.stage_id','stages.id')
        ->select('test_questions.*','stages.mob_stage_name as stage_name')
        ->get();

        $arr = [];
        $games = [];
    $i=0;
        foreach ($testQuestions as $question) {
            $arr[] = $question;
            if ($question->game) {
                
                $games[] = $question->game;
                $games[$i]['stage_name'] =  $question->stage_name;
                // array_push($games,['stage_name' => $question->stage_name]);
            $i++;
                
            }
        }
        $studentAssignGames = $games;

        return $this->returnData('data', $studentAssignGames, "Student Assignments ");
    }

    /**
     * @OA\Get(
     *     path="/api/student_programs",
     *     summary="Get programs for the student",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Student programs",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function studentPrograms()
    {
        // $data['programs'] = User::with(['userCourses.program','userCourses.program.course','userCourses.program.student_tests'])->where('id',auth()->user()->id)->first();
        // $data['programs'] = User::with([
        //     'userCourses.program',
        //     'userCourses.program.course',
        //     'userCourses.program.student_tests' => function ($query) {
        //         $query->where('student_id', auth()->user()->id)
        //             ->where('status', 0)->where('start_date', '<=', date('Y-m-d', strtotime(now())))->where('due_date', '>=', date('Y-m-d', strtotime(now())));
        //     }
        // ])
        //     ->where('id', auth()->user()->id)
        //     ->first();

        // // Filter to ensure unique lesson_id in student_tests
        // $data['programs']->userCourses->each(function ($course) {
        //     $course->program->student_tests = $course->program->student_tests->unique('lesszon_id')->where('due_date', '>=', date('Y-m-d', strtotime(now())))->where('start_date', '<=', date('Y-m-d', strtotime(now())));
        //     $course->program->student_tests->each(function ($student_test) {
        //         // $student_test->assignment_name = $student_test->tests->name ?? 'N/A';
        //         $student_test->assignment_name = $student_test->tests->name ?? 'N/A';

        //     });
        // });






        // $data['test_types'] = TestTypes::all();
        // $data['count'] = StudentTest::where('student_id', auth()->user()->id)->where('status', '=', '0')->where('due_date', '>=', date('Y-m-d', strtotime(now())))->where('start_date', '<=', date('Y-m-d', strtotime(now())))->count();
        // return $this->returnData('data', $data, "All groups for the student");

        $data['programs'] = User::with([
            'userCourses.program',
            'userCourses.program.course',
            'userCourses.program.student_tests' => function ($query) {
                $query->where('student_id', auth()->user()->id)
                    ->where('status', 0)
                    ->where('start_date', '<=', date('Y-m-d', strtotime(now())))
                    ->where('due_date', '>=', date('Y-m-d', strtotime(now())));
            },

        ])
            ->where('id', auth()->user()->id)
            ->first();

        // Filter to ensure unique lesson_id in student_tests
        $data['programs']->userCourses->each(function ($course) {
            $course->program->student_tests = $course->program->student_tests->where('status', '!=', 1)
                ->where('student_id', auth()->user()->id);
            if (isset($course->program->student_tests[0]))
                foreach ($course->program->student_tests as $test) {
                    // if($test->id == '1006')
                    // dd($test,Test::find($test->test_id)->name);
                    $test->assignment_name = Test::find($test->test_id)->name;

                    // dd($test);
                    // array_push($test,['assignment_name' => Test::find($test->test_id)->name]);
                    // if(isset($course->program->student_tests[0]))
                    // dd($test);

                }
            // dd($course->program->student_tests);
            // $course->program->student_tests->each(function ($student_test) {
            //     // if(!$student_test->tests->name)
            //     // dd($student_test->tests);
            //     $student_test->assignment_name = $student_test->tests->name ?? '-';
            // });

        });

        $data['test_types'] = TestTypes::all();
        $data['count'] = StudentTest::where('student_id', auth()->user()->id)
            ->where('status', '=', '0')
            ->where('due_date', '>=', date('Y-m-d', strtotime(now())))
            ->where('start_date', '<=', date('Y-m-d', strtotime(now())))
            ->count();

        return $this->returnData('data', $data, "All groups for the student");

    }

    public function getNotification(Request $request)
    {
        $request->validate([
            'is_read' => 'required',
        ]);
        $userId = auth()->user()->id;
        $notifications = Notification::where('user_id', $userId)->where('is_read', $request->is_read)->get();

        $unreadCount = Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
        // Notification::where('user_id', auth()->user()->id)->where('is_read', 0)->update(['is_read' => 1]);
        if ($request->is_read == 0) {
            foreach ($notifications as $notification) {
                $notification->is_read = 1;
                $notification->save();
            }
        }


        return $this->returnData('data', [
            'notifications' => $notifications,
            'count' => $unreadCount,
        ], "All notifications for the student");
    }
    
    public function studentProgramsAssign()
    {
        // Fetch the user and related programs with courses and student tests
        $user = User::with([
            'userCourses.program',
            'userCourses.program.course',
            'userCourses.program.student_tests' => function ($query) {
                $query->where('student_id', auth()->user()->id)
                    ->where('status', 0)
                    ->where('start_date', '<=', now()->format('Y-m-d'))
                    ->where('due_date', '>=', now()->format('Y-m-d'));
            }
        ])->where('id', auth()->user()->id)->first();

        if (!$user) {
            return $this->returnData('data', [], 'User not found');
        }


        // Filter programs to ensure they have student tests and handle unique lesson_id
        $filteredPrograms = $user->userCourses->filter(function ($course) {
            $course->program->student_tests = $course->program->student_tests
                ->where('due_date', '>=', now()->format('Y-m-d'))
                ->where('start_date', '<=', now()->format('Y-m-d'))
                ->unique('lesson_id');
            return $course->program->student_tests->isNotEmpty();
        });

        $data['programs'] = $filteredPrograms->values();


        // Fetch test types and count of pending student tests
        $data['test_types'] = TestTypes::all();
        $data['count'] = StudentTest::where('student_id', auth()->user()->id)
            ->where('status', '=', 0)
            ->where('due_date', '>=', now()->format('Y-m-d'))
            ->where('start_date', '<=', now()->format('Y-m-d'))
            ->count();

        return $this->returnData('data', $data, "All groups for the student");
    }

    /**
     * @OA\Post(
     *     path="/api/studentsInClass",
     *     summary="Get all students in a class",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="group_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All students in the class",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function studentsInClass(Request $request)
{
    $students_in_group = GroupStudent::where('group_id', $request->group_id)->get();
    $data['group'] = Group::find($request->group_id)->name;
    
    $students = array();
    foreach ($students_in_group as $student) {
        // Retrieve user data
        $user = User::where('id', $student->student_id)->first()->toArray();
        
        // Initialize latest as null
        $latest = null;

        // Retrieve the latest student data
        $student_degree = StudentDegree::where('student_id', $student->student_id)->orderBy('id', 'desc')->first();
        if (isset($student_degree->game_id)) {
            $latest_game = $student_degree->game_id;
            $latest_lesson_id = Game::find($latest_game)->lesson_id;
            $latest_lesson = Lesson::find($latest_lesson_id)->name;
            $latest_unit = Unit::find(Lesson::find($latest_lesson_id)->unit_id)->name;
            $latest = $latest_unit . " " . $latest_lesson;
        }

        // Add the latest information to the user data
        $user['latest'] = $latest;
        
        // Push the combined data into the students array
        array_push($students, $user);
    }
    
    $data['students'] = $students;
    return $this->returnData('data', $data, "All students");
}


    /**
     * @OA\Post(
     *     path="/api/student_programs_test",
     *     summary="Get student programs test",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="future", type="integer"),
     *             @OA\Property(property="from_date", type="string"),
     *             @OA\Property(property="to_date", type="string"),
     *             @OA\Property(property="program_id", type="integer"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="types", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student programs test",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function studentPrograms_test(Request $request)
    {
        // Initialize query builder
        $query = StudentTest::with('tests')->where('student_id', auth()->user()->id);

        if ($request->filled('future') && $request->future != NULL) {
            if ($request->future == 1) {
                // No additional conditions needed
            } elseif ($request->future == 0) {
                $query->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }
        } else {
            $query->where('start_date', '<=', date('Y-m-d', strtotime(now())));
        }

        // Filter by from and to date if provided
        if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $query->whereBetween('due_date', [$fromDate, $toDate]);
        }

        // Filter by program ID if provided
        if ($request->filled('program_id') && $request->program_id != NULL) {
            $query->where('program_id', $request->program_id);
        }
        $allTests = $query->orderBy('due_date', 'DESC')->get();
        $totalAllTests = $allTests->count();
        $finishedCount = $allTests->where('status', 1)->count();
        $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
            ->where('status', '!=', 1)
            ->count();
        $pendingCount = $totalAllTests - $finishedCount - $overdueCount;
        // dd($totalAllTests);
        //   dd($overdueCount,$finishedCount,$pendingCount);

        // Calculate percentages as integers
        $finishedPercentage = $totalAllTests > 0 ? round(($finishedCount / $totalAllTests) * 100, 2) : 0;
        $overduePercentage = $totalAllTests > 0 ? round(($overdueCount / $totalAllTests) * 100, 2) : 0;
        $pendingPercentage = $totalAllTests > 0 ? round(($pendingCount / $totalAllTests) * 100, 2) : 0;

        // Filter by status if provided
        if ($request->filled('status') && $request->status != NULL) {
            $now = \Carbon\Carbon::now();
            $status = $request->status;
            switch ($status) {
                case 'Completed':
                    $query->where('status', '1');
                    break;
                case 'Overdue':
                    $query->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                    break;

                case 'Pending':
                    $query->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                    break;
                default:
                    // Invalid status provided
                    break;
            }
        }

        // Filter by assignment types if provided
        if ($request->filled('types') && $request->types != NULL) {
            $assignmentTypes = $request->types;
            $query->whereHas('tests', function ($q) use ($assignmentTypes) {
                $q->join('test_types', 'tests.type', '=', 'test_types.id')
                    ->whereIn('test_types.id', $assignmentTypes);
            });
        }

        // Execute the query
        $tests = $query->orderBy('due_date', 'DESC')->get();

        // Calculate status counts
        $totalTests = $tests->count();

        // Prepare response data
        $test_types = TestTypes::all();
        $data['test_types'] = TestResource::make($test_types);
        $data['tests'] = StudentAssignmentResource::make($tests);
        $user_id = auth()->user()->id;

        $courses = DB::table('user_courses')
            ->join('programs', 'user_courses.program_id', '=', 'programs.id')
            ->join('courses', 'programs.course_id', '=', 'courses.id')
            ->where('user_courses.user_id', $user_id)
            ->select('programs.id as program_id', 'courses.name as course_name')
            ->get();

        // Add the "all programs" entry
        $allProgramsEntry = (object) [
            'program_id' => null,
            'course_name' => 'All Programs'
        ];

        $courses->prepend($allProgramsEntry);

        $data['courses'] = $courses;

        $data['counts'] = [
            'completed' => $finishedCount,
            'overdue' => $overdueCount,
            'pending' => $pendingCount,
        ];
        $data['assignments_percentages'] = [
            'completed' => $finishedPercentage,
            'overdue' => $overduePercentage,
            'pending' => $pendingPercentage,
        ];

        // Return response
        return $this->returnData('data', $data, "All groups for the student");
    }




    /**
     * @OA\Post(
     *     path="/api/student_progress",
     *     summary="Get student progress",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="month", type="string"),
     *             @OA\Property(property="type", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student progress",
     *         @OA\JsonContent()
     *     )
     * )
     */
    //     public function StudentProgress(Request $request){
//         $progress = StudentProgress::where('student_id',auth()->user()->id);
//         // Filter by month of created_at date if provided
// if ($request->filled('month')) {
//     $month = $request->month;
//     $progress = $progress->whereMonth('student_progress.created_at', Carbon::parse($month)->month);
// }


    // // Filter by test_types if provided
// if ($request->filled('type')) {
//     $type = $request->type;
//     $progress->join('tests', 'student_progress.test_id', '=', 'tests.id')
//           ->where('tests.type', $type);
// }
//  if ($request->filled('from_date') && $request->filled('to_date')) {
//             $from_date = Carbon::parse($request->from_date)->startOfDay();
//             $to_date = Carbon::parse($request->to_date)->endOfDay();
//             $progress = $progress->whereBetween('student_progress.created_at', [$from_date, $to_date]);
//         }
// $progress = $progress->orderBy('created_at','ASC')->select('student_progress.*')->get();

    // if($progress){
//         foreach ($progress as $course) {
//             // dd($course);
//         $createdDate = Carbon::parse($course->created_at);
//             $monthYear = $createdDate->format('Y-m');

    //             // Calculate the score for each test
//             $testScore = [
//                 'test_name' => $course->test_name,
//                 'test_id' => $course->test_id,
//                 'score' => $course->score,
//             ];

    //             // Add the test score to the respective month
//             if (!isset($monthlyScores[$monthYear])) {
//                 $monthlyScores[$monthYear] = [
//                     'month' => $createdDate->format('M'),
//                     'total_score' => 0,

    //                     // 'tests' => [],
//                 ];
//             }

    //             // $monthlyScores[$monthYear]['tests'][] = $testScore;
//             $monthlyScores[$monthYear]['total_score'] += $course->score;
//         $data['tprogress'] = array_values($monthlyScores);
//         }
// }
//   if ($request->filled('stars')) {
//         $stars = $request->stars;
//         $data['tprogress'] = array_filter($data['tprogress'], function($progress) use ($stars) {
//             return in_array($progress['star'], (array)$stars);
//         });
//     }




    //         $data['progress'] = StudentProgressResource::make($progress);

    //         $test_types = TestTypes::all();
//     $data['test_types'] = TestResource::make($test_types);
//          return $this->returnData('data', $data,'Student Progress');
//     }
    public function StudentProgress(Request $request)
    {
        // Validate the request to ensure program_id is required
        $request->validate([
            'program_id' => 'required|integer',
        ]);

        // Get the authenticated student's ID
        $studentId = auth()->user()->id;

        // Initialize query builder with student ID and program ID
        $progressQuery = StudentProgress::where('student_id', $studentId)
            ->where('program_id', $request->program_id)->where('is_done', 1);

        // Filter by month of created_at date if provided
        if ($request->filled('month')) {
            $month = $request->month;
            $progressQuery->whereMonth('student_progress.created_at', Carbon::parse($month)->month);
        }

        // Filter by test_types if provided
        if ($request->filled('type')) {
            $type = $request->type;
            $progressQuery->join('tests', 'student_progress.test_id', '=', 'tests.id')
                ->where('tests.type', $type);
        }

        // Filter by date range if provided
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::parse($request->from_date)->startOfDay();
            $to_date = Carbon::parse($request->to_date)->endOfDay();
            $progressQuery->whereBetween('student_progress.created_at', [$from_date, $to_date]);
        }

        // Filter by stars if provided
        if ($request->filled('stars')) {
            $stars = (array) $request->stars;
            $progressQuery->whereIn('stars', $stars);
        }

        // Get the progress data
        $progress = $progressQuery->orderBy('created_at', 'ASC')
            ->select('student_progress.*')->where('is_done',1)
            ->get();

        // Initialize monthlyScores and starCounts arrays
        $monthlyScores = [];
        $starCounts = [];

        foreach ($progress as $course) {
            $createdDate = Carbon::parse($course->created_at);
            $monthYear = $createdDate->format('Y-m');

            // Calculate the score for each test
            $testScore = [
                'test_name' => $course->test_name,
                'test_id' => $course->test_id,
                'score' => $course->score,
                'star' => $course->stars,  // Include star in the testScore for filtering
            ];

            // Add the test score to the respective month
            if (!isset($monthlyScores[$monthYear])) {
                $monthlyScores[$monthYear] = [
                    'month' => $createdDate->format('M'),
                    'total_score' => 0,
                    'star' => $course->stars,
                    'tests' => [],
                ];
            }

            $monthlyScores[$monthYear]['tests'][] = $testScore;
            $monthlyScores[$monthYear]['total_score'] += $course->score;






            // Count stars
            $star = $course->stars;
            if (isset($starCounts[$star])) {
                $starCounts[$star]++;
            } else {
                $starCounts[$star] = 1;
            }
        }
        $totalDisplayedStars = array_sum($starCounts);




        $oneStarDisplayedCount = isset($starCounts[1]) ? $starCounts[1] : 0;
        $twoStarDisplayedCount = isset($starCounts[2]) ? $starCounts[2] : 0;
        $threeStarDisplayedCount = isset($starCounts[3]) ? $starCounts[3] : 0;
        // Filter progress by stars if provided
        if ($request->filled('stars')) {
            $stars = (array) $request->stars;
            $data['tprogress'] = array_filter($monthlyScores, function ($monthlyScore) use ($stars) {
                foreach ($monthlyScore['tests'] as $test) {
                    if (in_array($test['star'], $stars)) {
                        return true;
                    }
                }
                return false;
            });
        } else {
            $data['tprogress'] = array_values($monthlyScores);
        }



        $oneStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($oneStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;
        $twoStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($twoStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;
        $threeStarDisplayedPercentage = $totalDisplayedStars > 0 ? round(($threeStarDisplayedCount / $totalDisplayedStars) * 100, 2) : 0;

        // Prepare response data
        $data['progress'] = StudentProgressResource::make($progress);

        if ($request->filled('stars')) {
            $data['counts'] = StudentProgress::where('stars', $request->stars)->count();
        } else {
            $data['counts'] = StudentProgress::where('student_id', $studentId)
                ->where('program_id', $request->program_id)
                ->count();
        }
        $division = StudentProgress::where('student_id', $studentId)->where('is_done',1)->where('program_id', $request->program_id)
            ->count();
        if ($division == 0)
            $division = 1;
        if (!$request->filled('from_date') && !$request->filled('to_date')){
             $threestars = StudentProgress::where('stars', 3)->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();
            $twostars = StudentProgress::where('stars', 2)->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();
            $onestar = StudentProgress::where('stars', 1)->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();
                
                if ($division == 0) {
                $division = 1;
            }
            $data['reports_percentages'] = [
                'three_star' => round(($threestars / $division) * 100),
                'two_star' => round(($twostars / $division) * 100),
                'one_star' => round(($onestar / $division) * 100),

            ];
            
        }
        else {

            $threestars = StudentProgress::where('stars', 3)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();
            $twostars = StudentProgress::where('stars', 2)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();
            $onestar = StudentProgress::where('stars', 1)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                ->where('program_id', $request->program_id)->where('is_done', 1)->count();

            $division = StudentProgress::where('student_id', $studentId)->where('is_done', 1)->where('program_id', $request->program_id)
                ->whereBetween('student_progress.created_at', [$from_date, $to_date])->count();


            if ($division == 0) {
                $division = 1;
            }
            $data['reports_percentages'] = [
                'three_star' => round(($threestars / $division) * 100),
                'two_star' => round(($twostars / $division) * 100),
                'one_star' => round(($onestar / $division) * 100),

            ];
        
        }
        $test_types = TestTypes::all();
        $data['test_types'] = TestResource::make($test_types);

        return $this->returnData('data', $data, 'Student Progress');
    }






    /**
     * @OA\Post(
     *     path="/api/student_progress_by_group",
     *     summary="Get student progress by group",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="month", type="string"),
     *             @OA\Property(property="type", type="integer"),
     *             @OA\Property(property="student_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student progress by group",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function StudentProgressByGroup(Request $request)
    {
        $progress = StudentProgress::latest()->where('is_done', 1);
        // Filter by month of created_at date if provided
        if ($request->filled('month')) {
            $month = $request->month;
            $progress = $progress->whereMonth('student_progress.created_at', Carbon::parse($month)->month);
        }


        // Filter by test_types if provided
        if ($request->filled('type')) {
            $type = $request->type;
            $progress->join('tests', 'student_progress.test_id', '=', 'tests.id')
                ->where('tests.type', $type);
        }
        $progress = $progress->where('student_id', $request->student_id)->select('student_progress.*')->get();

        if ($progress) {
            foreach ($progress as $course) {
                // dd($course);
                $createdDate = Carbon::parse($course->created_at);
                $monthYear = $createdDate->format('Y-m');

                // Calculate the score for each test
                $testScore = [
                    'test_name' => $course->test_name,
                    'test_id' => $course->test_id,
                    'score' => $course->score,
                ];

                // Add the test score to the respective month
                if (!isset($monthlyScores[$monthYear])) {
                    $monthlyScores[$monthYear] = [
                        'month' => $createdDate->format('M'),
                        'total_score' => 0,
                        // 'tests' => [],
                    ];
                }

                // $monthlyScores[$monthYear]['tests'][] = $testScore;
                $monthlyScores[$monthYear]['total_score'] += $course->score;
                $data['tprogress'] = array_values($monthlyScores);
            }
        }




        $data['progress'] = StudentProgressResource::make($progress);

        $test_types = TestTypes::all();
        $data['test_types'] = TestResource::make($test_types);
        return $this->returnData('data', $data, 'Student Progress');
    }
    /**
     * @OA\Post(
     *     path="/api/assignAssessment",
     *     summary="Assign an assessment to students",
     *     tags={"Student"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="students_id", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="test_id", type="integer"),
     *             @OA\Property(property="start_date", type="string"),
     *             @OA\Property(property="due_date", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test Assigned",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function finishAssignment(Request $request)
    {
        $student_id = auth()->user()->id;
        $test_id = $request->test_id;
        $assignment_id = $request->assignment_id;
        $stars = $request->stars;
        $s_test = StudentTest::find($assignment_id);
        // Retrieve related data
        $test = Test::find($test_id);
        $lesson = Lesson::find($test->lesson_id);
        $program_id = $test->program_id;
        $unit_id = $lesson->unit_id;

        // Check if the progress record exists
        $progress = StudentProgress::where('student_id', $student_id)
            ->where('test_id', $test_id)
            ->where('program_id', $program_id)
            ->where('unit_id', $unit_id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if ($progress) {
            // If the progress record exists, update the mistake_count and other fields
            if ($stars == 0) {
                $progress->score = 10;
                $progress->is_done = 0;
                $progress->mistake_count += 1;
                $s_test->status = 0;
            } elseif ($stars == 1) {
                $progress->score = 30;
            } elseif ($stars == 2) {
                $progress->score = 60;
            } elseif ($stars == 3) {
                $progress->score = 100;
            }
        } else {
            // If the progress record does not exist, create a new one
            $progress = new StudentProgress();
            $progress->student_id = $student_id;
            $progress->program_id = $program_id;
            $progress->unit_id = $unit_id;
            $progress->lesson_id = $lesson->id;
            $progress->test_id = $test_id;
            $progress->mistake_count = 0;

            if ($stars == 0) {
                $progress->score = 10;
                $progress->is_done = 0;
                $progress->mistake_count = 1;
                $s_test->status = 0;
            } elseif ($stars == 1) {
                $progress->score = 30;
            } elseif ($stars == 2) {
                $progress->score = 60;
            } elseif ($stars == 3) {
                $progress->score = 100;
            }
        }

        $progress->time = 10;
        $progress->is_correct = 1;
        $progress->stars = $stars;
        $progress->is_done = ($stars != 0) ? 1 : 0;
        $progress->save();

        // Update the assignment status

        $s_test->status = 1;
        $s_test->update();

        return $this->returnData('data', $progress, 'Progress Saved!');
    }

}
