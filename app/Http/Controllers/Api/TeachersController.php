<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAssignmentResource;
use App\Http\Resources\TestResource;
use App\Http\Resources\StudentProgressResource;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\Unit;
use App\Models\Group;
use App\Models\StudentTest;
use App\Models\TestQuestion;
use App\Models\StudentProgress;
use App\Models\GroupStudent;
use App\Models\StudentDegree;
use App\Models\Test;

use App\Models\User;
use App\Models\Game;
use App\Models\GameType;
use App\Models\Lesson;
// use App\Models\Unit;
use App\Models\TestTypes;
use App\Models\GameSkills;
use App\Models\Skill;

use App\Http\Resources\TeacherAssignmentFilterResource;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use App\Traits\backendTraits;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SkillsExport;

class TeachersController extends Controller
{
    use HelpersTrait;
    use backendTraits;
    /**
     * @OA\Get(
     *     path="/api/teacherAssignments",
     *     summary="Get teacher assignments",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Teacher Assignments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function teacherAssignments()
    {

        $studentsDidAss = StudentTest::where('teacher_id', auth()->user()->id)->where('student_tests.status', 0)->where('due_date', '>=', date('Y-m-d', strtotime(now())))->orderBy('due_date', 'ASC')->get();

        $data = TeacherAssignmentResource::make($studentsDidAss);
        return $this->returnData('data', $data, "Teacher Assignments ");
    }

    /**
     * @OA\Post(
     *     path="/api/testQuestions",
     *     summary="Get assignment questions",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="assign_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment Questions",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function testQuestions(Request $request)
    {
        $data['games'] = TestQuestion::with(['game.gameLetters', 'game.gameImages'])->where('test_id', $request->assign_id)->where('game_id', '!=', null)->get();
        return $this->returnData('data', $data, "Assignment Questions ");
    }
    public function assignAssessment(Request $request)
    {
        foreach ($request->students_id as $student_id) {
            $assign_test = new StudentTest();
            $assign_test->student_id = $student_id;
            $assign_test->test_id = $request->test_id;
            $assign_test->lesson_id = Test::find($request->test_id)->lesson_id;
            $assign_test->status = 0;
            $assign_test->program_id = Test::find($request->test_id)->program_id;
            $assign_test->teacher_id = auth()->user()->id;
            $assign_test->start_date = date('Y-m-d', strtotime($request->start_date));
            $assign_test->due_date = date('Y-m-d', strtotime($request->due_date));
            // $assign_test->due_date = date('Y-m-d',strtotime($request->due_date));
            $assign_test->save();
        }
        return $this->returnSuccessMessage('Test Assigned');
    }
    /**
     * @OA\Post(
     *     path="/api/editGame/{game_id}/{assign_id}",
     *     summary="Edit game",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="game_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="assign_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="inst", type="string"),
     *             @OA\Property(property="repeat", type="integer"),
     *             @OA\Property(property="trials", type="integer"),
     *             @OA\Property(property="correct_ans", type="string"),
     *             @OA\Property(property="audio_flag", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test and related games replicated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function editGame($game_id, $assign_id, Request $request)
    {
        // Find the TestQuestion where game_id and test_id match the sent variables
        $testQuestion = TestQuestion::where('game_id', $game_id)
            ->where('test_id', $assign_id)
            ->first();

        if (!$testQuestion) {
            return response()->json(['error' => 'TestQuestion not found'], 404);
        }

        // Get the Test where test_id matches
        $test = Test::find($assign_id);

        if (!$test) {
            return response()->json(['error' => 'Test not found'], 404);
        }

        // Replicate the test and assign its owner_id and user_id to the logged-in teacher
        $newTest = $test->replicate();
        $newTest->owner_id = auth()->user()->id;
        $newTest->user_id = auth()->user()->id;
        $newTest->save();

        // Find the Game where id matches the game_id
        $game = Game::find($game_id);

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        // Replicate the game and assign it to the replicated test
        $newGame = $game->replicate();
        $newGame->inst = $request->inst ?? $game->inst;
        $newGame->num_of_letter_repeat = $request->repeat ?? $game->num_of_letter_repeat;
        $newGame->num_of_trials = $request->trials ?? $game->num_of_trials;
        $newGame->correct_ans = $request->correct_ans ?? $game->correct_ans;
        $newGame->audio_flag = $request->audio_flag ?? $game->audio_flag;
        $newGame->is_edited = 1;
        $newGame->save();

        // Update the TestQuestion to be assigned to the new game
        $newTestQuestion = $testQuestion->replicate();
        $newTestQuestion->game_id = $newGame->id;
        $newTestQuestion->test_id = $newTest->id;
        $newTestQuestion->save();

        // Find all other games related to the test_id variable in the body
        $relatedGames = Game::where('id', '!=', $game_id)
            ->get();
        foreach ($relatedGames as $relatedGame) {
            // Replicate each related game

            $newRelatedTestQuestion = TestQuestion::where('game_id', $relatedGame->id)
                ->where('test_id', $assign_id)
                ->first();
            if ($newRelatedTestQuestion)
                $newRelatedTestQuestion = $newRelatedTestQuestion->replicate();
            else
                continue;


            $newRelatedGame = $relatedGame->replicate();
            $newRelatedGame->save();

            // Create a new TestQuestion for each replicated game

            $newRelatedTestQuestion->game_id = $newRelatedGame->id;
            $newRelatedTestQuestion->test_id = $newTest->id;
            $newRelatedTestQuestion->save();
        }

        return response()->json(['message' => 'Test and related games replicated successfully'], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/TeacherAssignmentFilter",
     *     summary="Filter teacher assignments",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="program_id", type="integer"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="diff", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered Teacher Assignments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function TeacherAssignmentFilter(Request $request)
    {

        $studentsDidAss = StudentTest::where('teacher_id', auth()->user()->id)->where('student_tests.status', 0)->where('student_tests.due_date', '>=', now());
        if ($request->filled('program_id')) {
            $studentsDidAss->where('student_tests.program_id', $request->program_id);
        }
        if ($request->filled('type') && $request->filled('diff')) {
            $type = $request->type;
            $diff = $request->diff;
            $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
                ->where('tests.type', $type)->where('tests.difficulty_level', $diff);
        } elseif ($request->filled('diff')) {
            $diff = $request->diff;
            $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
                ->where('tests.difficulty_level', $diff);
        } elseif ($request->filled('type')) {
            $type = $request->type;
            $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
                ->where('tests.type', $type);
        }

        $studentsDidAss = $studentsDidAss->select('student_tests.*')->get();
        $data['assignments'] = TeacherAssignmentFilterResource::make($studentsDidAss);
        $test_types = TestTypes::all();
        $data['test_types'] = TestResource::make($test_types);
        return $this->returnData('data', $data, "Teacher Assignments ");
    }
    /**
     * @OA\Post(
     *     path="/api/add_assignment_to_group",
     *     summary="Add an assignment to a group",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="test_id", type="integer"),
     *             @OA\Property(property="group_id", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="start_date", type="string"),
     *             @OA\Property(property="due_date", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignment assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function addAssignmentToGroup(Request $request)
    {
        foreach ($request->group_id as $group_id) {
            $students_in_group = GroupStudent::where('group_id', $group_id)->get();
            if ($students_in_group->count() > 0) {
                foreach ($students_in_group as $student) {
                    $assignment = new StudentTest();

                    $assignment->lesson_id = Test::find($request->test_id)->lesson_id;
                    $assignment->program_id = Test::find($request->test_id)->program_id;
                    $assignment->teacher_id = auth()->user()->id;
                    $assignment->group_id = $group_id;
                    $assignment->student_id = $student->student_id;
                    $assignment->test_id = $request->test_id;
                    $assignment->status = 0;
                    $assignment->start_date = date('Y-m-d', strtotime($request->start_date));
                    $assignment->due_date = date('Y-m-d', strtotime($request->to_date));
                    $assignment->save();
                }
            } else
                return $this->returnError('404', 'There are no students in this group');
        }

        return $this->returnData('data', $assignment, "Assignment Assigned ");
    }
    /**
     * @OA\Post(
     *     path="/api/teacherClasses",
     *     summary="Get classes of a teacher",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="stage_id", type="integer"),
     *             @OA\Property(property="program_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher classes fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function teacherClasses(Request $request)
    {
        $data['classes'] = Group::where('teacher_id', auth()->user()->id)->where('stage_id', $request->stage_id)->where('program_id', $request->program_id)->get();
        return $this->returnData('data', $data, "Teacher Assignments ");
    }
    /**
     * @OA\Post(
     *     path="/api/student_stats",
     *     summary="Get statistics of students",
     *     tags={"Teacher"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="group_id", type="integer"),
     *             @OA\Property(property="from", type="string"),
     *             @OA\Property(property="to", type="string"),
     *             @OA\Property(property="type", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student statistics fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function StudentStats(Request $request)
    {


        $progress = StudentProgress::with(['user', 'tests'])->join('group_students', 'student_progress.student_id', 'group_students.student_id')->where('group_students.group_id', $request->group_id)->orderBy('score', 'desc');
        // Filter by month of created_at date if provided
        if ($request->filled('from') && $request->filled('to')) {
            $month = $request->month;
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
            // $to = $request->to;
            $progress = $progress->whereBetween('student_progress.created_at', array(Carbon::parse($from), Carbon::parse($to)->addDays(1)));
            // ->whereBetween('date',array($datefrom,$dateto))
            // ->whereMonth('student_progress.created_at', Carbon::parse($month)->month)
        }


        // Filter by test_types if provided
        if ($request->filled('type')) {
            $type = $request->type;
            $progress->join('tests', 'student_progress.test_id', '=', 'tests.id')
                ->where('tests.type', $type);
        }
        $data['progress'] = $progress->select('student_progress.*')->get();


        // $data['progress'] = StudentProgress::->get();

        if ($data['progress']) {
            foreach ($data['progress'] as $course) {
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

        $data['total_score'] = 100;
        $test_types = TestTypes::all();
        $data['test_types'] = TestResource::make($test_types);
        return $this->returnData('data', $data, "Teacher Assignments ");

    }



    public function completionReport(Request $request)
    {
        // Initialize query builder
        $query = StudentTest::with('tests')->where('student_id', $request->student_id);
if($query->get()->isEmpty())
return $this->returnError('404', 'No student progress found for the given date range.');

if(!StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first()){
return $this->returnError('404', 'No student progress found for the given date range.');
}
$latest_game = StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first()->game_id;
    $latest = Game::find($latest_game)->lesson_id;
    $latest_lesson = Lesson::find($latest)->name;
    $latest_unit = Unit::find(Lesson::find($latest)->unit_id)->name;
    $data['student_latest'] = $latest_unit." ".$latest_lesson;
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
if ($request->filled(['from_date', 'to_date']) && $request->from_date != null && $request->to_date != null) {
    $fromDate = Carbon::parse($request->from_date)->startOfDay();
    $toDate = Carbon::parse($request->to_date)->endOfDay();

    $query->where('student_id', $request->student_id)
          ->where(function ($query) use ($fromDate, $toDate) {
              $query->whereBetween('start_date', [$fromDate, $toDate])
                    ->orWhereBetween('due_date', [$fromDate, $toDate]);
          });
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




        $data['counts'] = [
            'completed' => $finishedCount,
            'overdue' => $overdueCount,
            'pending' => $pendingCount,
        ];
        $data['assignments_percentages'] = [
            'completed' => round ($finishedPercentage),
            'overdue' => round($overduePercentage),
            'pending' => round($pendingPercentage),
        ];
        $data['tests'] = StudentAssignmentResource::make($tests);
        $data['test_types'] = TestResource::make($test_types);
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



        // Return response
        return $this->returnData('data', $data, "All groups for the student");
    }

public function masteryReport(Request $request)
{
    // Retrieve student progress for the given student and program
    $query = StudentProgress::where('student_id', $request->student_id)
        ->where('program_id', $request->program_id);

if($query->get()->isEmpty())
return $this->returnData('data', [], 'No student progress found for the given date range.');
    // Apply filters if provided
    if ($request->has('unit_id')) {
        $query->where('unit_id', $request->unit_id);
    }
    if ($request->has('lesson_id')) {
        $query->where('lesson_id', $request->lesson_id);
    }
    if ($request->has('game_id')) {
        $query->whereHas('test', function ($q) use ($request) {
            $q->where('game_id', $request->game_id);
        });
    }
    if ($request->has('skill_id')) {
        $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
            $q->where('skill_id', $request->skill_id);
        });
    }

    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            
        $query->whereBetween('created_at', [$fromDate, $toDate]);
    }
    $student_progress = $query->get();

    // Initialize arrays to hold data for grouping
    $unitsMastery = [];
    $lessonsMastery = [];
    $gamesMastery = [];
    $skillsMastery = [];
    $gameTypesMastery = [];

$latest_game = StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first()->game_id;
    $latest = Game::find($latest_game)->lesson_id;
    $latest_lesson = Lesson::find($latest)->name;
    $latest_unit = Unit::find(Lesson::find($latest)->unit_id)->name;
    
    // Process each progress record
    foreach ($student_progress as $progress) {
        // Retrieve the test and its related game, game type, and skills
        $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

        // Check if the test and its relationships are properly loaded
        if (!$test || !$test->game || !$test->game->gameTypes) {
            continue; // Skip to the next progress record if any of these are null
        }

        // Get the game type (since each game has one game type)
        $gameType = $test->game->gameTypes;

        // Group by unit
        if (!isset($unitsMastery[$progress->unit_id])) {
            $unitsMastery[$progress->unit_id] = [
                'unit_id' => $progress->unit_id,
                'name' => Unit::find($progress->unit_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
                'lessons' => [],
                'latest_prog' => $latest_unit." ".$latest_lesson
            ];
        }

        // Group by lesson
        if (!isset($lessonsMastery[$progress->lesson_id])) {
            $lessonsMastery[$progress->lesson_id] = [
                'lesson_id' => $progress->lesson_id,
                'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name." | ".Lesson::find($progress->lesson_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
                'games' => [],
                'latest_prog' => $latest_unit." ".$latest_lesson
            ];
        }

        // Group by game type
        if (!isset($gameTypesMastery[$gameType->id])) {
            $gameTypesMastery[$gameType->id] = [
                'game_type_id' => $gameType->id,
                'name' => GameType::find($gameType->id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'count' => 0,
                'total_score' => 0,
                'games' => [],
                'latest_prog' => $latest_unit." ".$latest_lesson
            ];
        }

        // Group by game within the game type
        if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
            $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                'game_id' => $test->game_id,
                
                'name' => Game::find($test->game_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'count' => 0,
                'total_score' => 0,
                'latest_prog' => $latest_unit." ".$latest_lesson
            ];
        }

        // Group by skill
        if ($gameType && $gameType->skills->unique()) {
            foreach ($gameType->skills->unique('skill') as $gameSkill) {
                $skill = $gameSkill->skill;

                if (!isset($skillsMastery[$skill->id])) {
                    $skillsMastery[$skill->id] = [
                        'skill_id' => $skill->id,
                        'name' => $skill->skill,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                $skillsMastery[$skill->id]['total_attempts']++;
                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $skillsMastery[$skill->id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $skillsMastery[$skill->id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $skillsMastery[$skill->id]['introduced']++;
                    } else {
                        $skillsMastery[$skill->id]['failed']++;
                    }
                } else {
                    $skillsMastery[$skill->id]['failed']++;
                }
                $skillsMastery[$skill->id]['total_score'] += $progress->score;
            }
        }

        // Update totals for units, lessons, and game types
        $unitsMastery[$progress->unit_id]['total_attempts']++;
        $lessonsMastery[$progress->lesson_id]['total_attempts']++;
        $gameTypesMastery[$gameType->id]['total_attempts']++;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

        if ($progress->is_done) {
            if ($progress->score >= 80) {
                $unitsMastery[$progress->unit_id]['mastered']++;
                $lessonsMastery[$progress->lesson_id]['mastered']++;
                $gameTypesMastery[$gameType->id]['mastered']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
            } elseif ($progress->score >= 60) {
                $unitsMastery[$progress->unit_id]['practiced']++;
                $lessonsMastery[$progress->lesson_id]['practiced']++;
                $gameTypesMastery[$gameType->id]['practiced']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
            } elseif ($progress->score >= 30) {
                $unitsMastery[$progress->unit_id]['introduced']++;
                $lessonsMastery[$progress->lesson_id]['introduced']++;
                $gameTypesMastery[$gameType->id]['introduced']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
            } else {
                $unitsMastery[$progress->unit_id]['failed']++;
                $lessonsMastery[$progress->lesson_id]['failed']++;
                $gameTypesMastery[$gameType->id]['failed']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
            }
        } else {
            $unitsMastery[$progress->unit_id]['failed']++;
            $lessonsMastery[$progress->lesson_id]['failed']++;
            $gameTypesMastery[$gameType->id]['failed']++;
            $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
        }

        $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
        $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

        // Group lessons under units
        if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
            $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                'lesson_id' => $progress->lesson_id,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
                'latest_prog' => $latest_unit." ".$latest_lesson
            ];
        }

        // Aggregate lesson data under the unit
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
    }

    // Ensure all lessons are included in units
    foreach ($unitsMastery as &$unit) {
        foreach ($lessonsMastery as $lessonId => $lessonData) {
            if (!isset($unit['lessons'][$lessonId])) {
                $unit['lessons'][$lessonId] = [
                    'lesson_id' => $lessonId,
                    'failed' => 0,
                    'introduced' => 0,
                    'practiced' => 0,
                    'mastered' => 0,
                    'total_attempts' => 0,
                    'total_score' => 0,
                    'mastery_percentage' => 0,
                    'latest_prog' => $latest_unit." ".$latest_lesson
                ];
            }
        }
    }

    // Calculate mastery percentages for units, lessons, games, and game types
    foreach ($unitsMastery as &$unitData) {
        $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? ($unitData['total_score'] / $unitData['total_attempts']) : 0;

        foreach ($unitData['lessons'] as &$lessonData) {
            $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
        }

        $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
    }

    foreach ($lessonsMastery as &$lessonData) {
        $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
    }

    foreach ($gameTypesMastery as &$gameTypeData) {
        foreach ($gameTypeData['games'] as &$gameData) {
            $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? ($gameData['total_score'] / $gameData['total_attempts']) : 0;
        }
        $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

        $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? ($gameTypeData['total_score'] / $gameTypeData['total_attempts']) : 0;
    }

    // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
    foreach ($skillsMastery as &$skillData) {
        if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
            $skillData['current_level'] = 'mastered';
            $skillData['mastery_percentage'] = $skillData['total_score']/$skillData['total_attempts'] > 100 ? 100 : $skillData['total_score']/$skillData['total_attempts'];
        } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
            $skillData['current_level'] = 'practiced';
            $skillData['mastery_percentage'] = $skillData['total_score']/$skillData['total_attempts'] > 100 ? 100 : $skillData['total_score']/$skillData['total_attempts'];
        } elseif ($skillData['introduced'] > $skillData['failed']) {
            $skillData['current_level'] = 'introduced';
            $skillData['mastery_percentage'] = $skillData['total_score']/$skillData['total_attempts'] > 100 ? 100 : $skillData['total_score']/$skillData['total_attempts'];
        } else {
            $skillData['current_level'] = 'failed';
            $skillData['mastery_percentage'] = $skillData['total_score']/$skillData['total_attempts'] > 100 ? 100 : $skillData['total_score']/$skillData['total_attempts'];
        }
    }

    // Prepare the response data
    $response = [];
    
    if ($request->has('filter')) {
        switch ($request->filter) {
            case 'Skill':
                $response = array_values($skillsMastery);
                break;
            case 'Unit':
                $response = array_values($unitsMastery);
                break;
            case 'Lesson':
                $response = array_values($lessonsMastery);
                break;
            case 'Game':
                $response = array_values($gameTypesMastery);
                break;
            default:
                $response = [
                    'skills' => array_values($skillsMastery),
                    'units' => array_values($unitsMastery),
                    'lessons' => array_values($lessonsMastery),
                    'games' => array_values($gameTypesMastery),
                ];
                break;
        }
    } else {
        $response = [
            'skills' => array_values($skillsMastery),
            'units' => array_values($unitsMastery),
            'lessons' => array_values($lessonsMastery),
            'games' => array_values($gameTypesMastery),
        ];
    }
      
     return $this->returnData('data', $response, 'deh api bta3ml 7agat');
}










    // public function numOfTrialsReport(Request $request){

    // }


    public function numOfTrialsReport(Request $request)
{
    // Validate the request to ensure program_id and student_id are required
    $request->validate([
        'program_id' => 'required|integer',
        'student_id' => 'required|integer',
    ]);
    if(!StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first())
return $this->returnError('404', 'No student progress found .');
    $latest_game = StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first()->game_id;
    $latest = Game::find($latest_game)->lesson_id;
    $latest_lesson = Lesson::find($latest)->name;
    $latest_unit = Unit::find(Lesson::find($latest)->unit_id)->name;
    $data['student_latest'] = $latest_unit." ".$latest_lesson;
    // dd($latest_lesson,$latest_unit);
    // Get the authenticated student's ID
    $studentId = $request->student_id;
    
    // Initialize query builder with student ID and program ID
    $progressQuery = StudentProgress::where('student_id', $studentId)
        ->where('program_id', $request->program_id);

if($progressQuery->get()->isEmpty())
return $this->returnError('404', 'No student progress found .');
    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $from_date = Carbon::parse($request->from_date)->startOfDay();
            $to_date = Carbon::parse($request->to_date)->endOfDay();
        $progressQuery->whereBetween('created_at', [$from_date, $to_date]);
    }

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

    // Filter by stars if provided
    if ($request->filled('stars')) {
        $stars = (array) $request->stars;
        if($request->stars == 2)
        $progressQuery->whereIn('mistake_count', range(2,1000));
        else
        $progressQuery->whereIn('mistake_count', $stars);
    }

    // Get the progress data
    $progress = $progressQuery->orderBy('created_at', 'ASC')
        ->select('student_progress.*')
        ->get();

    // Initialize arrays to hold the data
    $monthlyScores = [];
    $starCounts = [];

    foreach ($progress as $course) {
        $createdDate = Carbon::parse($course->created_at);
        $monthYear = $createdDate->format('Y-m');

        // Calculate the number of trials
        $numTrials = $course->mistake_count + 1;

        // Calculate the score for each test
        $testScore = [
            'name' => $course->test_name,
            'test_id' => $course->test_id,
            'score' => $course->score,
            'star' => $course->stars,  // Include star in the testScore for filtering
            'num_trials' => $numTrials
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

    $division = StudentProgress::where('student_id', $studentId)->count();
    if ($division == 0) {
        $division = 1;
    }

    if (!$request->filled('from_date') && !$request->filled('to_date')) {
        $data['reports_percentages'] = [
            'first_trial' => round((StudentProgress::where('mistake_count', 0)->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
            'second_trial' => round((StudentProgress::where('mistake_count', 1)->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
            'third_trial' => round((StudentProgress::whereIn('mistake_count', [2,3,4,5,6,7,8,9,10,11])->where('student_id', $studentId)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
        ];
    } else {
        $threestars = StudentProgress::where('mistake_count', 0)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();
        $twostars = StudentProgress::where('mistake_count', 1)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();
        $onestar = StudentProgress::whereIn('mistake_count', [2,3,4,5,6,7,8,9,10,11])->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();

        $division = StudentProgress::where('student_id', $studentId)
            ->whereBetween('student_progress.created_at', [$from_date, $to_date])->count();

        if ($division == 0) {
            $division = 1;
        }

        $data['reports_percentages'] = [
            'first_trial' => round(($threestars / $division) * 100, 2),
            'second_trial' => round(($twostars / $division) * 100, 2),
            'third_trial' => round(($onestar / $division) * 100, 2),
        ];
    }

    $test_types = TestTypes::all();
    $data['test_types'] = TestResource::make($test_types);

    return $this->returnData('data', $data, 'Student Progress');
}

   public function skillReport(Request $request)
{
    $studentId = $request->student_id;
    $programId = $request->program_id;
     $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
    // Retrieve student progress within the date range
    $studentProgress = StudentProgress::where('student_id', $studentId)
        ->where('program_id', $programId)
        ->where('is_done', 1)
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->get();

if($studentProgress->isEmpty())
return $this->returnError('404', 'No student progress found .');
$latest_game = StudentDegree::where('student_id',$request->student_id)->orderBy('id','desc')->first()->game_id;
    $latest = Game::find($latest_game)->lesson_id;
    $latest_lesson = Lesson::find($latest)->name;
    $latest_unit = Unit::find(Lesson::find($latest)->unit_id)->name;
    $data['student_latest'] = $latest_unit." ".$latest_lesson;
    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
        $studentProgress->whereBetween('created_at', [$fromDate, $toDate]);
    }
    if ($studentProgress->isEmpty()) {
        return $this->returnError('404', 'No student progress found .');
    }

    $skillsData = [];
    foreach ($studentProgress as $progress) {
        // Eager load the relationships
        $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

        if ($test && $test->game) {
            $game = $test->game;

            if ($game->gameTypes) {
                // foreach ($game->gameTypes as $gameType) {
                    if ($game->gameTypes->skills) {
                        foreach ($game->gameTypes->skills->unique('skill') as $gameSkill) {
                            if (!$gameSkill->skill) continue;

                            $skill = $gameSkill->skill;
                            $skillName = $skill->skill; // Assuming the column name is 'skill'
                            $date = $progress->created_at->format('Y-m-d');

                            $currentLevel = 'Introduced';
                            if ($progress->score >= 80) {
                                $currentLevel = 'Mastered';
                            } elseif ($progress->score >= 60) {
                                $currentLevel = 'Practiced';
                            }

                            // Initialize skill data if not already present
                            if (!isset($skillsData[$skillName])) {
                                $skillsData[$skillName] = [
                                    'skill_name' => $skillName,
                                    'total_score' => 0,
                                    'count' => 0, // Initialize count to 0
                                    'average_score' => 0, // Initialize count to 0
                                    'current_level' => $currentLevel,
                                    'date' => $date,
                                ];
                            }

                            // Increment count for each skill attempt
                            $skillsData[$skillName]['count']++;

                            // Sum the scores for each skill and update current level if needed
                            $skillsData[$skillName]['total_score'] += $progress->score;

                            // Calculate the average score
                            $averageScore = $skillsData[$skillName]['total_score'] / $skillsData[$skillName]['count'];
$skillsData[$skillName]['average_score'] = $averageScore;
                            if ($averageScore >= 80) {
                                $skillsData[$skillName]['current_level'] = 'Mastered';
                            } elseif ($averageScore >= 60) {
                                $skillsData[$skillName]['current_level'] = 'Practiced';
                            } else {
                                $skillsData[$skillName]['current_level'] = 'Introduced';
                            }
                        }
                    }
                }
            // }
        }
    }

    if (empty($skillsData)) {
        return $this->returnData('data', [], 'No skills data found for the given student progress.');
    }

    // Convert the associative array to an indexed array
    // $finalData = array_values($skillsData);
    $studentName = User::find($request->student_id)->name; // Assuming you have a name field in the Student model
        foreach ($skillsData as $skillData) {
            $finalData[] = array_merge(['student_name' => $studentName], $skillData);
        }
    // Generate the Excel file
    $fileName = 'Skill_Report_' . now()->format('Ymd_His') . '.xlsx';
    Excel::store(new SkillsExport($finalData), $fileName, 'public');

    // Return the downloadable link
    $filePath = 'https://ambernoak.co.uk/Fillament/public' . Storage::url($fileName);

    return $this->returnData('data', ['download_link' => $filePath], 'Skill Report');
}
















    public function classCompletionReport(Request $request)
    {
        $groupId = $request->group_id;

        // Retrieve all students in the group
        $students = GroupStudent::where('group_id', $groupId)->pluck('student_id');

        if ($students->isEmpty()) {
            return $this->returnError('404', 'No student progress found .');
        }

        // Initialize the query builder for student progress
        $progressQuery = StudentTest::with('tests')
            ->whereIn('student_id', $students);

if($progressQuery->get()->isEmpty())
return $this->returnError('404', 'No student progress found .');
        if ($request->filled('future') && $request->future != NULL) {
            if ($request->future == 1) {
                // No additional conditions needed
            } elseif ($request->future == 0) {
                $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }
        } else {
            $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
        }

        // Filter by from and to date if provided
        if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            // $from_date = Carbon::parse($request->from_date)->startOfDay();
            // $to_date = Carbon::parse($request->to_date)->endOfDay();
            $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);
        }

        // Filter by program ID if provided
        if ($request->filled('program_id') && $request->program_id != NULL) {
            $progressQuery->where('program_id', $request->program_id);
        }

        // Execute the query
        $allTests = $progressQuery->orderBy('due_date', 'DESC')->get();
        $totalAllTests = $allTests->count();
        $finishedCount = $allTests->where('status', 1)->count();
        $overdueCount = $allTests->where('due_date', '<', \Carbon\Carbon::now()->format('Y-m-d'))
            ->where('status', '!=', 1)
            ->count();
        $pendingCount = $totalAllTests - $finishedCount - $overdueCount;

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
                    $progressQuery->where('status', '1');
                    break;
                case 'Overdue':
                    $progressQuery->where('due_date', '<', $now->format('Y-m-d'))->where('status', '!=', 1);
                    break;
                case 'Pending':
                    $progressQuery->where('status', '0')->where('due_date', '>=', $now->format('Y-m-d'));
                    break;
                default:
                    // Invalid status provided
                    break;
            }
        }

        // Filter by assignment types if provided
        if ($request->filled('types') && $request->types != NULL) {
            $assignmentTypes = $request->types;
            $progressQuery->whereHas('tests', function ($q) use ($assignmentTypes) {
                $q->join('test_types', 'tests.type', '=', 'test_types.id')
                    ->whereIn('test_types.id', $assignmentTypes);
            });
        }

        // Execute the query
        $tests = $progressQuery->orderBy('due_date', 'DESC')->get();

        // Calculate status counts
        $totalTests = $tests->count();

        // Prepare response data
        $test_types = TestTypes::all();

        $data['counts'] = [
            'completed' => $finishedCount,
            'overdue' => $overdueCount,
            'pending' => $pendingCount,
        ];
        $data['assignments_percentages'] = [
            'completed' => ceil($finishedPercentage),
            'overdue' => floor($overduePercentage),
            'pending' => ceil($pendingPercentage),
        ];
        $data['tests'] = StudentAssignmentResource::make($tests);
        $data['test_types'] = TestResource::make($test_types);

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

        // Return response
        return $this->returnData('data', $data, "All groups for the class");
    }




public function classMasteryReport(Request $request)
{
    // Retrieve all students in the specified group
    $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

    if ($students->isEmpty()) {
        return $this->returnError('404', 'No student progress found .');
    }

    // Initialize query builder for student progress
    $query = StudentProgress::whereIn('student_id', $students)
        ->where('program_id', $request->program_id);

if($query->get()->isEmpty())
return $this->returnError('404', 'No student progress found .');
    // Apply filters if provided
    if ($request->has('unit_id')) {
        $query->where('unit_id', $request->unit_id);
    }
    if ($request->has('lesson_id')) {
        $query->where('lesson_id', $request->lesson_id);
    }
    if ($request->has('game_id')) {
        $query->whereHas('test', function ($q) use ($request) {
            $q->where('game_id', $request->game_id);
        });
    }
    if ($request->has('skill_id')) {
        $query->whereHas('test.game.gameTypes.skills', function ($q) use ($request) {
            $q->where('skill_id', $request->skill_id);
        });
    }

    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
        $query->whereBetween('created_at', [$fromDate, $toDate]);
    }

    $student_progress = $query->get();

    // Initialize arrays to hold data for grouping
    $unitsMastery = [];
    $lessonsMastery = [];
    $gamesMastery = [];
    $skillsMastery = [];


if ($student_progress->isEmpty()) {
            return $this->returnError('404', 'No student progress found .');
        }
    // Process each progress record
    foreach ($student_progress as $progress) {
        // Retrieve the test and its related game, game type, and skills
        $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

        // Check if the test and its relationships are properly loaded
        if (!$test || !$test->game || !$test->game->gameTypes) {
            continue; // Skip to the next progress record if any of these are null
        }

        // Get the game type (since each game has one game type)
        $gameType = $test->game->gameTypes;

        // Group by unit
        if (!isset($unitsMastery[$progress->unit_id])) {
            $unitsMastery[$progress->unit_id] = [
                'unit_id' => $progress->unit_id,
                'name' => Unit::find($progress->unit_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
                'lessons' => [],
            ];
        }

        // Group by lesson
        if (!isset($lessonsMastery[$progress->lesson_id])) {
            $lessonsMastery[$progress->lesson_id] = [
                'lesson_id' => $progress->lesson_id,
                'name' => Unit::find(Lesson::find($progress->lesson_id)->unit_id)->name." | ".Lesson::find($progress->lesson_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
                'games' => [],
            ];
        }

        // Group by game type
        if (!isset($gameTypesMastery[$gameType->id])) {
            $gameTypesMastery[$gameType->id] = [
                'game_type_id' => $gameType->id,
                'name' => GameType::find($gameType->id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'count' => 0,
                'total_score' => 0,
                'games' => [],
            ];
        }

        // Group by game within the game type
        if (!isset($gameTypesMastery[$gameType->id]['games'][$test->game_id])) {
            $gameTypesMastery[$gameType->id]['games'][$test->game_id] = [
                'game_id' => $test->game_id,
                
                'name' => Game::find($test->game_id)->name,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'count' => 0,
                'total_score' => 0,
            ];
        }

        // Group by skill
        if ($gameType && $gameType->skills) {
            foreach ($gameType->skills->unique('skill') as $gameSkill) {
                $skill = $gameSkill->skill;

                if (!isset($skillsMastery[$skill->id])) {
                    $skillsMastery[$skill->id] = [
                        'skill_id' => $skill->id,
                        'name' => $skill->skill,
                        'failed' => 0,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                        'total_score' => 0,
                        'mastery_percentage' => 0,
                    ];
                }

                $skillsMastery[$skill->id]['total_attempts']++;
                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $skillsMastery[$skill->id]['mastered']++;
                    } elseif ($progress->score >= 60) {
                        $skillsMastery[$skill->id]['practiced']++;
                    } elseif ($progress->score >= 30) {
                        $skillsMastery[$skill->id]['introduced']++;
                    } else {
                        $skillsMastery[$skill->id]['failed']++;
                    }
                } else {
                    $skillsMastery[$skill->id]['failed']++;
                }
                $skillsMastery[$skill->id]['total_score'] += $progress->score;
            }
        }

        // Update totals for units, lessons, and game types
        $unitsMastery[$progress->unit_id]['total_attempts']++;
        $lessonsMastery[$progress->lesson_id]['total_attempts']++;
        $gameTypesMastery[$gameType->id]['total_attempts']++;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_attempts']++;

        if ($progress->is_done) {
            if ($progress->score >= 80) {
                $unitsMastery[$progress->unit_id]['mastered']++;
                $lessonsMastery[$progress->lesson_id]['mastered']++;
                $gameTypesMastery[$gameType->id]['mastered']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['mastered']++;
            } elseif ($progress->score >= 60) {
                $unitsMastery[$progress->unit_id]['practiced']++;
                $lessonsMastery[$progress->lesson_id]['practiced']++;
                $gameTypesMastery[$gameType->id]['practiced']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['practiced']++;
            } elseif ($progress->score >= 30) {
                $unitsMastery[$progress->unit_id]['introduced']++;
                $lessonsMastery[$progress->lesson_id]['introduced']++;
                $gameTypesMastery[$gameType->id]['introduced']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['introduced']++;
            } else {
                $unitsMastery[$progress->unit_id]['failed']++;
                $lessonsMastery[$progress->lesson_id]['failed']++;
                $gameTypesMastery[$gameType->id]['failed']++;
                $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
            }
        } else {
            $unitsMastery[$progress->unit_id]['failed']++;
            $lessonsMastery[$progress->lesson_id]['failed']++;
            $gameTypesMastery[$gameType->id]['failed']++;
            $gameTypesMastery[$gameType->id]['games'][$test->game_id]['failed']++;
        }

        $unitsMastery[$progress->unit_id]['total_score'] += $progress->score;
        $lessonsMastery[$progress->lesson_id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['total_score'] += $progress->score;
        $gameTypesMastery[$gameType->id]['games'][$test->game_id]['count']++;

        // Group lessons under units
        if (!isset($unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id])) {
            $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id] = [
                'lesson_id' => $progress->lesson_id,
                'failed' => 0,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
                'total_score' => 0,
                'mastery_percentage' => 0,
            ];
        }

        // Aggregate lesson data under the unit
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['failed'] += $lessonsMastery[$progress->lesson_id]['failed'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['introduced'] += $lessonsMastery[$progress->lesson_id]['introduced'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['practiced'] += $lessonsMastery[$progress->lesson_id]['practiced'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['mastered'] += $lessonsMastery[$progress->lesson_id]['mastered'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_attempts'] += $lessonsMastery[$progress->lesson_id]['total_attempts'];
        $unitsMastery[$progress->unit_id]['lessons'][$progress->lesson_id]['total_score'] += $lessonsMastery[$progress->lesson_id]['total_score'];
    }
// Ensure all lessons are included in units
foreach ($unitsMastery as &$unit) {
        foreach ($lessonsMastery as $lessonId => $lessonData) {
            if (!isset($unit['lessons'][$lessonId])) {
                $unit['lessons'][$lessonId] = [
                    'lesson_id' => $lessonId,
                    'failed' => 0,
                    'introduced' => 0,
                    'practiced' => 0,
                    'mastered' => 0,
                    'total_attempts' => 0,
                    'total_score' => 0,
                    'mastery_percentage' => 0,
                ];
            }
        }
    }

    // Calculate mastery percentages for units, lessons, games, and game types
    foreach ($unitsMastery as &$unitData) {
        $unitData['mastery_percentage'] = $unitData['total_attempts'] > 0 ? ($unitData['total_score'] / $unitData['total_attempts']) : 0;

        foreach ($unitData['lessons'] as &$lessonData) {
            $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
        }

        $unitData['lessons'] = array_values($unitData['lessons']); // Convert lessons to array
    }

    foreach ($lessonsMastery as &$lessonData) {
        $lessonData['mastery_percentage'] = $lessonData['total_attempts'] > 0 ? ($lessonData['total_score'] / $lessonData['total_attempts']) : 0;
    }

    foreach ($gameTypesMastery as &$gameTypeData) {
        foreach ($gameTypeData['games'] as &$gameData) {
            $gameData['mastery_percentage'] = $gameData['total_attempts'] > 0 ? ($gameData['total_score'] / $gameData['total_attempts']) : 0;
        }
        $gameTypeData['games'] = array_values($gameTypeData['games']); // Convert games to array

        $gameTypeData['mastery_percentage'] = $gameTypeData['total_attempts'] > 0 ? ($gameTypeData['total_score'] / $gameTypeData['total_attempts']) : 0;
    }

    // Calculate skill mastery level based on mastered, practiced, introduced, and failed counts
    foreach ($skillsMastery as &$skillData) {
        if ($skillData['mastered'] > $skillData['practiced'] && $skillData['mastered'] > $skillData['introduced'] && $skillData['mastered'] > $skillData['failed']) {
            $skillData['current_level'] = 'mastered';
            $skillData['mastery_percentage'] = 100;
        } elseif ($skillData['practiced'] > $skillData['introduced'] && $skillData['practiced'] > $skillData['failed']) {
            $skillData['current_level'] = 'practiced';
            $skillData['mastery_percentage'] = 70;
        } elseif ($skillData['introduced'] > $skillData['failed']) {
            $skillData['current_level'] = 'introduced';
            $skillData['mastery_percentage'] = 30;
        } else {
            $skillData['current_level'] = 'failed';
            $skillData['mastery_percentage'] = 15;
        }
    }

    // Prepare the response data
    $response = [];
    if ($request->has('filter')) {
        switch ($request->filter) {
            case 'Skill':
                $response = array_values($skillsMastery);
                break;
            case 'Unit':
                $response = array_values($unitsMastery);
                break;
            case 'Lesson':
                $response = array_values($lessonsMastery);
                break;
            case 'Game':
                $response = array_values($gameTypesMastery);
                break;
            default:
                $response = [
                    'skills' => array_values($skillsMastery),
                    'units' => array_values($unitsMastery),
                    'lessons' => array_values($lessonsMastery),
                    'games' => array_values($gameTypesMastery),
                ];
                break;
        }
    } else {
        $response = [
            'skills' => array_values($skillsMastery),
            'units' => array_values($unitsMastery),
            'lessons' => array_values($lessonsMastery),
            'games' => array_values($gameTypesMastery),
        ];
    }

     return $this->returnData('data', $response, 'deh api bta3ml 7agat');
}








    public function classNumOfTrialsReport(Request $request)
    {
        // Get the student IDs for the given group ID
        $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

        // Initialize query builder with student IDs and program ID
        $progressQuery = StudentProgress::whereIn('student_id', $students)
            ->where('program_id', $request->program_id);
            
            if($progressQuery->get()->isEmpty())
return $this->returnError('404', 'No student progress found .');
if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $from_date = Carbon::parse($request->from_date)->startOfDay();
            $to_date = Carbon::parse($request->to_date)->endOfDay();
        $progressQuery->whereBetween('created_at', [$from_date, $to_date]);
    }

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

    // Filter by stars if provided
    if ($request->filled('stars')) {
        $stars = (array) $request->stars;
        if($request->stars == 2)
        $progressQuery->whereIn('mistake_count', range(2,1000));
        else
        $progressQuery->whereIn('mistake_count', $stars);
    }

    // Get the progress data
    $progress = $progressQuery->orderBy('created_at', 'ASC')
        ->select('student_progress.*')
        ->get();

    // Initialize arrays to hold the data
    $monthlyScores = [];
    $starCounts = [];

    foreach ($progress as $course) {
        $createdDate = Carbon::parse($course->created_at);
        $monthYear = $createdDate->format('Y-m');

        // Calculate the number of trials
        $numTrials = $course->mistake_count + 1;

        // Calculate the score for each test
        $testScore = [
            'name' => $course->test_name,
            'test_id' => $course->test_id,
            'score' => $course->score,
            'star' => $course->stars,  // Include star in the testScore for filtering
            'num_trials' => $numTrials
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
        $data['counts'] = StudentProgress::whereIn('student_id', $students)
            ->where('program_id', $request->program_id)
            ->count();
    }

    $division = StudentProgress::whereIn('student_id', $students)->count();
    if ($division == 0) {
        $division = 1;
    }

    if (!$request->filled('from_date') && !$request->filled('to_date')) {
        $data['reports_percentages'] = [
            'first_trial' => round((StudentProgress::where('mistake_count', 0)->whereIn('student_id', $students)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
            'second_trial' => round((StudentProgress::where('mistake_count', 1)->whereIn('student_id', $students)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
            'third_trial' => round((StudentProgress::whereIn('mistake_count', [2,3,4,5,6,7,8,9,10,11])->whereIn('student_id', $students)
                ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
        ];
    } else {
        $threestars = StudentProgress::where('mistake_count', 0)->whereIn('student_id', $students)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();
        $twostars = StudentProgress::where('mistake_count', 1)->whereIn('student_id', $students)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();
        $onestar = StudentProgress::whereIn('mistake_count', [2,3,4,5,6,7,8,9,10,11])->whereIn('student_id', $students)->whereBetween('student_progress.created_at', [$from_date, $to_date])
            ->where('program_id', $request->program_id)->count();

        $division = StudentProgress::whereIn('student_id', $students)
            ->whereBetween('student_progress.created_at', [$from_date, $to_date])->count();

        if ($division == 0) {
            $division = 1;
        }

        $data['reports_percentages'] = [
            'first_trial' => round(($threestars / $division) * 100, 1),
            'second_trial' => round(($twostars / $division) * 100, 1),
            'third_trial' => round(($onestar / $division) * 100, 1),
        ];
    }

    $test_types = TestTypes::all();
    $data['test_types'] = TestResource::make($test_types);

    return $this->returnData('data', $data, 'Student Progress');
}


public function classSkillReport(Request $request)
{
    $groupId = $request->group_id;
    $programId = $request->program_id;
    $fromDate = $request->from_date;
    $toDate = $request->to_date;

    // Get the student IDs for the given group ID
    $students = GroupStudent::where('group_id', $groupId)->pluck('student_id');

    // Initialize an array to store the skills data for each student
    $allStudentsSkillsData = [];

    // Loop through each student to retrieve their progress
    foreach ($students as $studentId) {
        $studentProgressQuery = StudentProgress::where('student_id', $studentId)
            ->where('program_id', $programId);

        if ($request->filled(['from_date', 'to_date']) && $request->from_date != null && $request->to_date != null) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $studentProgressQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $studentProgress = $studentProgressQuery->get();
        if ($studentProgress->isEmpty()) {
            continue;
        }

        $skillsData = [];

        foreach ($studentProgress as $progress) {
            // Eager load the relationships
            $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

            if ($test && $test->game) {
                $game = $test->game;

                if ($game->gameTypes) {
                    if ($game->gameTypes->skills) {
                        foreach ($game->gameTypes->skills->unique('skill') as $gameSkill) {
                            if (!$gameSkill->skill) continue;

                            $skill = $gameSkill->skill;
                            $skillName = $skill->skill; // Assuming the column name is 'skill'
                            $date = $progress->created_at->format('Y-m-d');

                            $currentLevel = 'Introduced';
                            if ($progress->score >= 80) {
                                $currentLevel = 'Mastered';
                            } elseif ($progress->score >= 60) {
                                $currentLevel = 'Practiced';
                            }

                            // Initialize skill data if not already present
                            if (!isset($skillsData[$skillName])) {
                                $skillsData[$skillName] = [
                                    'skill_name' => $skillName,
                                    'total_score' => 0,
                                    'count' => 0, // Initialize count to 0
                                    'average_score' => 0, // Initialize count to 0
                                    'current_level' => $currentLevel,
                                    'date' => $date,
                                ];
                            }

                            // Increment count for each skill attempt
                            $skillsData[$skillName]['count']++;

                            // Sum the scores for each skill and update current level if needed
                            $skillsData[$skillName]['total_score'] += $progress->score;

                            // Calculate the average score
                            $averageScore = $skillsData[$skillName]['total_score'] / $skillsData[$skillName]['count'];
                            $skillsData[$skillName]['average_score'] = $averageScore;
                            if ($averageScore >= 80) {
                                $skillsData[$skillName]['current_level'] = 'Mastered';
                            } elseif ($averageScore >= 60) {
                                $skillsData[$skillName]['current_level'] = 'Practiced';
                            } else {
                                $skillsData[$skillName]['current_level'] = 'Introduced';
                            }
                        }
                    }
                }
            }
        }

        if (!empty($skillsData)) {
            $allStudentsSkillsData[$studentId] = $skillsData;
        }
    }

    if (empty($allStudentsSkillsData)) {
        return $this->returnData('data', [], 'No skills data found for the given student progress.');
    }

    // Prepare data for Excel export
    $finalData = [];
    foreach ($allStudentsSkillsData as $studentId => $skillsData) {
        $studentName = User::find($studentId)->name; // Assuming you have a name field in the Student model
        foreach ($skillsData as $skillData) {
            $finalData[] = array_merge(['student_name' => $studentName], $skillData);
        }
    }

    // Generate the Excel file
    $fileName = 'Skill_Report_' . now()->format('Ymd_His') . '.xlsx';
    Excel::store(new SkillsExport($finalData), $fileName, 'public');

    // Return the downloadable link
    $filePath = 'https://ambernoak.co.uk/Fillament/public' . Storage::url($fileName);

    return $this->returnData('data', ['download_link' => $filePath], 'Skill Report');
}










}
