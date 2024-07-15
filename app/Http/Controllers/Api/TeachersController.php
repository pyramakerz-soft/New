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
use App\Models\Test;

use App\Models\Game;
use App\Models\GameType;
use App\Models\TestTypes;
use App\Models\GameSkills;
use App\Models\Skill;
use App\Models\User;

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
        return $this->returnData('data', $data, "All groups for the student");
    }

    public function masteryReport(Request $request)
    {
        // Retrieve student progress for the given student and program
        $query = StudentProgress::where('student_id', $request->student_id)
            ->where('program_id', $request->program_id);

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
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        $student_progress = $query->get();

        // Initialize arrays to hold data for grouping
        $unitsMastery = [];
        $lessonsMastery = [];
        $gamesMastery = [];
        $skillsMastery = [];

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
                    'introduced' => 0,
                    'practiced' => 0,
                    'mastered' => 0,
                    'total_attempts' => 0,
                ];
            }

            // Group by lesson
            if (!isset($lessonsMastery[$progress->lesson_id])) {
                $lessonsMastery[$progress->lesson_id] = [
                    'lesson_id' => $progress->lesson_id,
                    'introduced' => 0,
                    'practiced' => 0,
                    'mastered' => 0,
                    'total_attempts' => 0,
                ];
            }

            // Group by game
            if (!isset($gamesMastery[$test->game_id])) {
                $gamesMastery[$test->game_id] = [
                    'game_id' => $test->game_id,
                    'introduced' => 0,
                    'practiced' => 0,
                    'mastered' => 0,
                    'total_attempts' => 0,
                ];
            }

            // Check if the game type has related skills
            if ($gameType && $gameType->skills) {
                foreach ($gameType->skills as $gameSkill) {
                    $skill = $gameSkill->skill;

                    // Group by skill
                    if (!isset($skillsMastery[$skill->id])) {
                        $skillsMastery[$skill->id] = [
                            'skill_id' => $skill->id,
                            'name' => $skill->skill,
                            'introduced' => 0,
                            'practiced' => 0,
                            'mastered' => 0,
                            'total_attempts' => 0,
                        ];
                    }

                    // Update the skill datamasteryPercentage
                    $skillsMastery[$skill->id]['total_attempts']++;
                    if ($progress->is_done) {
                        if ($progress->score >= 80) {
                            $skillsMastery[$skill->id]['mastered']++;
                            $unitsMastery[$progress->unit_id]['mastered']++;
                            $lessonsMastery[$progress->lesson_id]['mastered']++;
                            $gamesMastery[$test->game_id]['mastered']++;
                        } elseif ($progress->score >= 30) {
                            $skillsMastery[$skill->id]['practiced']++;
                            $unitsMastery[$progress->unit_id]['practiced']++;
                            $lessonsMastery[$progress->lesson_id]['practiced']++;
                            $gamesMastery[$test->game_id]['practiced']++;
                        } else {
                            $skillsMastery[$skill->id]['introduced']++;
                            $unitsMastery[$progress->unit_id]['introduced']++;
                            $lessonsMastery[$progress->lesson_id]['introduced']++;
                            $gamesMastery[$test->game_id]['introduced']++;
                        }
                    }
                }
            }

            // Update totals for units, lessons, and games
            $unitsMastery[$progress->unit_id]['total_attempts']++;
            $lessonsMastery[$progress->lesson_id]['total_attempts']++;
            $gamesMastery[$test->game_id]['total_attempts']++;
        }

        // Determine the current level for each skill
        foreach ($skillsMastery as &$skillData) {
            $introduced = $skillData['introduced'];
            $practiced = $skillData['practiced'];
            $mastered = $skillData['mastered'];
            $total = $skillData['total_attempts'];

            if ($mastered > $practiced && $mastered > $introduced) {
                $skillData['current_level'] = 'Mastered';
            } elseif ($practiced > $introduced) {
                $skillData['current_level'] = 'Practiced';
            } else {
                $skillData['current_level'] = 'Introduced';
            }

            // Calculate mastery percentage
            $skillData['mastery_percentage'] = $total > 0 ? min(($mastered / $total) * 100, 100) : 0;
        }

        // Determine the current level for each unit, lesson, and game
        $determineLevel = function (&$data) {
            foreach ($data as &$item) {
                $introduced = $item['introduced'];
                $practiced = $item['practiced'];
                $mastered = $item['mastered'];
                $total = $item['total_attempts'];

                if ($mastered > $practiced && $mastered > $introduced) {
                    $item['current_level'] = 'Mastered';
                } elseif ($practiced > $introduced) {
                    $item['current_level'] = 'Practiced';
                } else {
                    $item['current_level'] = 'Introduced';
                }

                // Calculate mastery percentage
                $item['mastery_percentage'] = $total > 0 ? min(($mastered / $total) * 100, 100) : 0;
            }
        };

        $determineLevel($unitsMastery);
        $determineLevel($lessonsMastery);
        $determineLevel($gamesMastery);

        // Filter the data based on the requested filter type
        $responseData = [];
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'unit':
                    $responseData = array_values($unitsMastery);
                    break;
                case 'lesson':
                    $responseData = array_values($lessonsMastery);
                    break;
                case 'game':
                    $responseData = array_values($gamesMastery);
                    break;
                case 'skill':
                    $responseData = array_values($skillsMastery);
                    break;
                default:
                    $responseData = array_values($skillsMastery); // Default to skills if no valid filter provided
                    break;
            }
        } else {
            $responseData = array_values($skillsMastery); // Default to skills if no filter provided
        }

        // Return the mastery report data
        return $this->returnData('data', $responseData, 'Student Progress');
    }


    // public function numOfTrialsReport(Request $request){

    // }


    public function numOfTrialsReport(Request $request)
    {
        // Validate the request to ensure program_id is required
        $request->validate([
            'program_id' => 'required|integer',
            'student_id' => 'required|integer',
        ]);

        // Get the authenticated student's ID
        $studentId = $request->student_id;

        // Initialize query builder with student ID and program ID
        $progressQuery = StudentProgress::where('student_id', $studentId)
            ->where('program_id', $request->program_id);

        if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            $from_date = date('d-m-Y', strtotime($request->from_date));
            $to_date = date('d-m-Y', strtotime($request->to_date));
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

        // Filter by date range if provided
        // if ($request->filled('from_date') && $request->filled('to_date')) {
        //     $from_date = Carbon::parse($request->from_date)->startOfDay();
        //     $to_date = Carbon::parse($request->to_date)->endOfDay();
        //     $progressQuery->whereBetween('student_progress.created_at', [$from_date, $to_date]);
        // }

        // Filter by stars if provided
        if ($request->filled('stars')) {
            $stars = (array) $request->stars;
            $progressQuery->whereIn('stars', $stars);
        }

        // Get the progress data
        $progress = $progressQuery->orderBy('created_at', 'ASC')
            ->select('student_progress.*')
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
        $division = StudentProgress::where('student_id', $studentId)
            ->count();
        if ($division == 0)
            $division = 1;
        if (!$request->filled('from_date') && !$request->filled('to_date'))
            $data['reports_percentages'] = [
                'first_trial' => ((StudentProgress::where('stars', 3)->where('student_id', $studentId)
                    ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
                'second_trial' => ((StudentProgress::where('stars', 2)->where('student_id', $studentId)
                    ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,
                'third_trial' => ((StudentProgress::where('stars', 1)->where('student_id', $studentId)
                    ->where('program_id', $request->program_id)->count() / $division) * 100) ?? 0,

            ];
        else {

            $threestars = StudentProgress::where('stars', 3)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                ->where('program_id', $request->program_id)->count();
            $twostars = StudentProgress::where('stars', 2)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                ->where('program_id', $request->program_id)->count();
            $onestar = StudentProgress::where('stars', 1)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
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
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Convert dates to Carbon instances for better manipulation
        // $fromDate = Carbon::parse($fromDate)->startOfDay();
        // $toDate = Carbon::parse($toDate)->endOfDay();

        // Retrieve student progress within the date range
        $studentProgress = StudentProgress::where('student_id', $studentId)
            ->where('program_id', $programId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();
        if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $studentProgress->whereBetween('created_at', [$fromDate, $toDate]);
        }
        if ($studentProgress->isEmpty()) {
            return $this->returnData('data', [], 'No student progress found for the given date range.');
        }

        $skillsData = [];

        foreach ($studentProgress as $progress) {
            // Eager load the relationships
            $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

            if ($test && $test->game) {
                $game = $test->game;

                // foreach ($game->gameTypes as $gameType) {
                // dd($game->gameTypes);
                // if (GameType::with(['skills'])->where('id',$gameType->id)->count() > 0) continue;
                if ($game->gameTypes && $game->gameTypes->skills) {

                    // Check if this GameType has skills
                    if (!$game->gameTypes->skills->isEmpty()) {
                        foreach ($game->gameTypes->skills as $gameSkill) {
                            if (!$gameSkill->skill)
                                continue;

                            $skill = $gameSkill->skill;
                            $skillName = $skill->skill; // Assuming the column name is 'skill'
                            $date = $progress->created_at->format('Y-m-d');

                            $currentLevel = 'Introduced';
                            if ($progress->score >= 80) {
                                $currentLevel = 'Mastered';
                            } elseif ($progress->score >= 60) {
                                $currentLevel = 'Practiced';
                            }

                            // Use skill name as the key to aggregate scores
                            if (!isset($skillsData[$skillName])) {
                                $skillsData[$skillName] = [
                                    'skill_name' => $skillName,
                                    'total_score' => 0,
                                    'current_level' => $currentLevel,
                                    'date' => $date,
                                ];
                            }

                            // Sum the scores for each skill and update current level if needed*
                            $skillsData[$skillName]['total_score'] += $progress->score;
                            if ($currentLevel === 'Mastered') {
                                $skillsData[$skillName]['current_level'] = 'Mastered';
                            } elseif ($currentLevel === 'Practiced' && $skillsData[$skillName]['current_level'] !== 'Mastered') {
                                $skillsData[$skillName]['current_level'] = 'Practiced';
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
        $finalData = array_values($skillsData);

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
            return $this->returnData('data', [], 'No students found for the given group.');
        }

        // Initialize the query builder for student progress
        $progressQuery = StudentTest::with('tests')
            ->whereIn('student_id', $students);

        if ($request->filled('future') && $request->future != NULL) {
            if ($request->future == 1) {
                // No additional conditions needed
            } elseif ($request->future == 0) {
                $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
            }
        } else {
            $progressQuery->where('start_date', '<=', date('Y-m-d', strtotime(now())));
        }

// <<<<<<< HEAD
//         // Filter by from and to date if provided
//         if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
//             $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
//             $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
//             $progressQuery->whereBetween('due_date', [$fromDate, $toDate]);
// =======
    // Filter by from and to date if provided
    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
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
>>>>>>> 0c464b6 (Class Mastery Report)
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







public function classMasteryReport(Request $request) {
    // Retrieve all students in the specified group
    $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

    if ($students->isEmpty()) {
        return $this->returnData('data', [], 'No students found in the specified group.');
    }

    // Initialize query builder for student progress
    $query = StudentProgress::whereIn('student_id', $students)
                            ->where('program_id', $request->program_id);

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

    // Process each progress record
    foreach($student_progress as $progress) {
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
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
            ];
        }

        // Group by lesson
        if (!isset($lessonsMastery[$progress->lesson_id])) {
            $lessonsMastery[$progress->lesson_id] = [
                'lesson_id' => $progress->lesson_id,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
            ];
        }

        // Group by game
        if (!isset($gamesMastery[$test->game_id])) {
            $gamesMastery[$test->game_id] = [
                'game_id' => $test->game_id,
                'introduced' => 0,
                'practiced' => 0,
                'mastered' => 0,
                'total_attempts' => 0,
            ];
        }

        // Check if the game type has related skills
        if ($gameType && $gameType->skills) {
            foreach($gameType->skills as $gameSkill) {
                $skill = $gameSkill->skill;

                // Group by skill
                if (!isset($skillsMastery[$skill->id])) {
                    $skillsMastery[$skill->id] = [
                        'skill_id' => $skill->id,
                        'name' => $skill->skill,
                        'introduced' => 0,
                        'practiced' => 0,
                        'mastered' => 0,
                        'total_attempts' => 0,
                    ];
                }

                // Update the skill data
                $skillsMastery[$skill->id]['total_attempts']++;
                if ($progress->is_done) {
                    if ($progress->score >= 80) {
                        $skillsMastery[$skill->id]['mastered']++;
                        $unitsMastery[$progress->unit_id]['mastered']++;
                        $lessonsMastery[$progress->lesson_id]['mastered']++;
                        $gamesMastery[$test->game_id]['mastered']++;
                    } elseif ($progress->score >= 30) {
                        $skillsMastery[$skill->id]['practiced']++;
                        $unitsMastery[$progress->unit_id]['practiced']++;
                        $lessonsMastery[$progress->lesson_id]['practiced']++;
                        $gamesMastery[$test->game_id]['practiced']++;
                    } else {
                        $skillsMastery[$skill->id]['introduced']++;
                        $unitsMastery[$progress->unit_id]['introduced']++;
                        $lessonsMastery[$progress->lesson_id]['introduced']++;
                        $gamesMastery[$test->game_id]['introduced']++;
                    }
                }
            }
        }

        // Update totals for units, lessons, and games
        $unitsMastery[$progress->unit_id]['total_attempts']++;
        $lessonsMastery[$progress->lesson_id]['total_attempts']++;
        $gamesMastery[$test->game_id]['total_attempts']++;
    }

    // Determine the current level for each skill
    foreach($skillsMastery as &$skillData) {
        $introduced = $skillData['introduced'];
        $practiced = $skillData['practiced'];
        $mastered = $skillData['mastered'];
        $total = $skillData['total_attempts'];

        if ($mastered > $practiced && $mastered > $introduced) {
            $skillData['current_level'] = 'Mastered';
        } elseif ($practiced > $introduced) {
            $skillData['current_level'] = 'Practiced';
        } else {
            $skillData['current_level'] = 'Introduced';
        }

        // Calculate mastery percentage
        $skillData['mastery_percentage'] = $total > 0 ? min(($mastered / $total) * 100, 100) : 0;
    }

    // Determine the current level for each unit, lesson, and game
    $determineLevel = function(&$data) {
        foreach($data as &$item) {
            $introduced = $item['introduced'];
            $practiced = $item['practiced'];
            $mastered = $item['mastered'];
            $total = $item['total_attempts'];

            if ($mastered > $practiced && $mastered > $introduced) {
                $item['current_level'] = 'Mastered';
            } elseif ($practiced > $introduced) {
                $item['current_level'] = 'Practiced';
            } else {
                $item['current_level'] = 'Introduced';
            }

            // Calculate mastery percentage
            $item['mastery_percentage'] = $total > 0 ? min(($mastered / $total) * 100, 100) : 0;
        }
    };

    $determineLevel($unitsMastery);
    $determineLevel($lessonsMastery);
    $determineLevel($gamesMastery);

    // Filter the data based on the requested filter type
    $responseData = [];
    if ($request->has('filter')) {
        switch ($request->filter) {
            case 'unit':
                $responseData = array_values($unitsMastery);
                break;
            case 'lesson':
                $responseData = array_values($lessonsMastery);
                break;
            case 'game':
                $responseData = array_values($gamesMastery);
                break;
            case 'skill':
                $responseData = array_values($skillsMastery);
                break;
            default:
                $responseData = array_values($skillsMastery); // Default to skills if no valid filter provided
                break;
        }
    } else {
        $responseData = array_values($skillsMastery); // Default to skills if no filter provided
    }

    // Return the mastery report data
    return $this->returnData('data', $responseData, 'Group Progress');
}







   public function classNumOfTrialsReport(Request $request)
{
    // Get the student IDs for the given group ID
    $students = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

    // Initialize query builder with student IDs and program ID
    $progressQuery = StudentProgress::whereIn('student_id', $students)
                            ->where('program_id', $request->program_id);

    if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date = date('Y-m-d', strtotime($request->to_date));
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
        $progressQuery->whereIn('stars', $stars);
    }

    // Get the progress data
    $progress = $progressQuery->orderBy('created_at', 'ASC')
                              ->select('student_progress.*')
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
            'star' => $course->stars,
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
        $data['tprogress'] = array_filter($monthlyScores, function($monthlyScore) use ($stars) {
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
        $data['counts'] = StudentProgress::whereIn('student_id', $students)
                            ->where('program_id', $request->program_id)
                            ->whereIn('stars', $request->stars)
                            ->count();
    } else {
        $data['counts'] = StudentProgress::whereIn('student_id', $students)
                            ->where('program_id', $request->program_id)
                            ->count();
    }

    if (!$request->filled(['from_date', 'to_date'])) {
        $totalAttempts = StudentProgress::whereIn('student_id', $students)
                                ->where('program_id', $request->program_id)
                                ->count();
        $data['reports_percentages'] = [
            'first_trial' => (StudentProgress::whereIn('student_id', $students)
                                ->where('stars', 3)
                                ->where('program_id', $request->program_id)
                                ->count() / max($totalAttempts, 1)) * 100,
            'second_trial' => (StudentProgress::whereIn('student_id', $students)
                                ->where('stars', 2)
                                ->where('program_id', $request->program_id)
                                ->count() / max($totalAttempts, 1)) * 100,
            'third_trial' => (StudentProgress::whereIn('student_id', $students)
                                ->where('stars', 1)
                                ->where('program_id', $request->program_id)
                                ->count() / max($totalAttempts, 1)) * 100,
        ];
    } else {
        $threestars = StudentProgress::whereIn('student_id', $students)
                        ->where('stars', 3)
                        ->whereBetween('student_progress.created_at', [$from_date, $to_date])
                        ->where('program_id', $request->program_id)
                        ->count();
        $twostars = StudentProgress::whereIn('student_id', $students)
                        ->where('stars', 2)
                        ->whereBetween('student_progress.created_at', [$from_date, $to_date])
                        ->where('program_id', $request->program_id)
                        ->count();
        $onestar = StudentProgress::whereIn('student_id', $students)
                        ->where('stars', 1)
                        ->whereBetween('student_progress.created_at', [$from_date, $to_date])
                        ->where('program_id', $request->program_id)
                        ->count();

        $totalAttempts = StudentProgress::whereIn('student_id', $students)
                        ->whereBetween('student_progress.created_at', [$from_date, $to_date])
                        ->count();

        $data['reports_percentages'] = [
            'first_trial' => ($threestars / max($totalAttempts, 1)) * 100,
            'second_trial' => ($twostars / max($totalAttempts, 1)) * 100,
            'third_trial' => ($onestar / max($totalAttempts, 1)) * 100,
        ];
    }

    $test_types = TestTypes::all();
    $data['test_types'] = TestResource::make($test_types);

    return $this->returnData('data', $data, 'Group Progress');
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
    $skillsData = [];

    // Loop through each student to retrieve their progress
    foreach ($students as $studentId) {
        $studentProgressQuery = StudentProgress::where('student_id', $studentId)
            ->where('program_id', $programId);

        if ($request->filled(['from_date', 'to_date']) && $request->from_date != NULL && $request->to_date != NULL) {
            $fromDate = date('Y-m-d', strtotime($request->from_date));
            $toDate = date('Y-m-d', strtotime($request->to_date));
            $studentProgressQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $studentProgress = $studentProgressQuery->get();
        if ($studentProgress->isEmpty()) {
            continue;
        }

        foreach ($studentProgress as $progress) {
            $test = Test::with(['game.gameTypes.skills.skill'])->find($progress->test_id);

            if ($test && $test->game) {
                $game = $test->game;

                if ($game->gameTypes && $game->gameTypes->skills) {
                    if (!$game->gameTypes->skills->isEmpty()) {
                        foreach ($game->gameTypes->skills as $gameSkill) {
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

                            // Use student ID and skill name as the key to aggregate scores
                            if (!isset($skillsData[$studentId])) {
                                $student = User::find($studentId);
                                $skillsData[$studentId] = [
                                    'student_name' => $student->name,
                                    'skills' => []
                                ];
                            }

                            if (!isset($skillsData[$studentId]['skills'][$skillName])) {
                                $skillsData[$studentId]['skills'][$skillName] = [
                                    'skill_name' => $skillName,
                                    'total_score' => 0,
                                    'current_level' => $currentLevel,
                                    'date' => $date,
                                ];
                            }

                            // Sum the scores for each skill and update current level if needed
                            $skillsData[$studentId]['skills'][$skillName]['total_score'] += $progress->score;
                            if ($currentLevel === 'Mastered') {
                                $skillsData[$studentId]['skills'][$skillName]['current_level'] = 'Mastered';
                            } elseif ($currentLevel === 'Practiced' && $skillsData[$studentId]['skills'][$skillName]['current_level'] !== 'Mastered') {
                                $skillsData[$studentId]['skills'][$skillName]['current_level'] = 'Practiced';
                            }
                        }
                    }
                }
            }
        }
    }

    if (empty($skillsData)) {
        return $this->returnData('data', [], 'No skills data found for the given group.');
    }

    // Convert the associative array to an indexed array for final data
    $finalData = [];
    foreach ($skillsData as $studentId => $data) {
        foreach ($data['skills'] as $skillData) {
            $finalData[] = [
                'student_name' => $data['student_name'],
                'skill_name' => $skillData['skill_name'],
                'total_score' => $skillData['total_score'],
                'current_level' => $skillData['current_level'],
                'date' => $skillData['date'],
            ];
        }
    }

    // Generate the Excel file
    $fileName = 'Group_Skill_Report_' . now()->format('Ymd_His') . '.xlsx';
    Excel::store(new SkillsExport($finalData), $fileName, 'public');

    // Return the downloadable link
    $filePath = 'https://ambernoak.co.uk/Fillament/public'.Storage::url($fileName);

    return $this->returnData('data', ['download_link' => $filePath], 'Group Skill Report');
}








}
