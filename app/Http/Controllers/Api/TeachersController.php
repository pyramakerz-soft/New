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
use App\Models\TestTypes;

use App\Http\Resources\TeacherAssignmentFilterResource;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use App\Traits\backendTraits;
use Carbon\Carbon;
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

       $studentsDidAss = StudentTest::where('teacher_id',auth()->user()->id)->where('student_tests.status',0)->where('due_date','>=',date('Y-m-d',strtotime(now())))->orderBy('due_date','ASC')->get();

            $data = TeacherAssignmentResource::make($studentsDidAss);
    return $this->returnData('data',$data,"Teacher Assignments ");
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
            public function testQuestions(Request $request){
                $data['games'] = TestQuestion::with(['game.gameLetters','game.gameImages'])->where('test_id',$request->assign_id)->where('game_id','!=',null)->get();
                 return $this->returnData('data',$data,"Assignment Questions ");
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
            public function editGame($game_id, $assign_id, Request $request) {
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
                                              if($newRelatedTestQuestion)
                                            $newRelatedTestQuestion=  $newRelatedTestQuestion->replicate();
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

       $studentsDidAss = StudentTest::where('teacher_id',auth()->user()->id)->where('student_tests.status',0)->where('student_tests.due_date','>=',now());
       if($request->filled('program_id')){
           $studentsDidAss->where('student_tests.program_id',$request->program_id);
       }
if ($request->filled('type') && $request->filled('diff')) {
    $type = $request->type;
     $diff = $request->diff;
    $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
          ->where('tests.type', $type)->where('tests.difficulty_level', $diff);
}
elseif($request->filled('diff')){
    $diff = $request->diff;
   $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
          ->where('tests.difficulty_level', $diff);
}
elseif ($request->filled('type')) {
    $type = $request->type;
    $studentsDidAss->join('tests', 'student_tests.test_id', '=', 'tests.id')
          ->where('tests.type', $type);
}

$studentsDidAss=$studentsDidAss->select('student_tests.*')->get();
            $data['assignments'] = TeacherAssignmentFilterResource::make($studentsDidAss);
            $test_types = TestTypes::all();
    $data['test_types'] = TestResource::make($test_types);
    return $this->returnData('data',$data,"Teacher Assignments ");
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
            public function addAssignmentToGroup(Request $request){
                foreach($request->group_id as $group_id){
                $students_in_group = GroupStudent::where('group_id',$group_id)->get();
                if($students_in_group->count() > 0 ){
                foreach($students_in_group as $student){
                $assignment = new StudentTest();

                $assignment->lesson_id = Test::find($request->test_id)->lesson_id; 
                $assignment->program_id = Test::find($request->test_id)->program_id; 
                $assignment->teacher_id = auth()->user()->id; 
                $assignment->group_id = $group_id;
                $assignment->student_id = $student->student_id;
                $assignment->test_id = $request->test_id;
                $assignment->status = 0;
                $assignment->start_date = date('Y-m-d',strtotime($request->start_date));
                $assignment->due_date = date('Y-m-d',strtotime($request->to_date));
                $assignment->save();
                }
                }else
                return $this->returnError('404','There are no students in this group');
                }
                
                 return $this->returnData('data',$assignment,"Assignment Assigned ");
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
            public function teacherClasses(Request $request){
                $data['classes'] = Group::where('teacher_id',auth()->user()->id)->where('stage_id',$request->stage_id)->where('program_id',$request->program_id)->get();
                return $this->returnData('data',$data,"Teacher Assignments ");
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
            public function StudentStats(Request $request){
                
                
                        $progress = StudentProgress::with(['user','tests'])->join('group_students','student_progress.student_id','group_students.student_id')->where('group_students.group_id',$request->group_id)->orderBy('score','desc');
        // Filter by month of created_at date if provided
if ($request->filled('from') && $request->filled('to')) {
    $month = $request->month;
    $from = date('Y-m-d',strtotime($request->from));
    $to = date('Y-m-d',strtotime($request->to));
    // $to = $request->to;
    $progress = $progress->whereBetween('student_progress.created_at',array(Carbon::parse($from),Carbon::parse($to)->addDays(1)));
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
                
                if($data['progress']){
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
            return $this->returnData('data',$data,"Teacher Assignments ");
                
            }

            public function completionReport(Request $request)
            {
                // Validate the request to ensure program_id is required
                $request->validate([
                    'student_id' => 'required|integer',
                ]);
            
                // Get the authenticated student's ID
                $studentId = $request->student_id;
            
                // Initialize query builder with student ID and program ID
                $progressQuery = StudentProgress::where('student_id', $studentId);
                                            // ->where('program_id', $request->program_id);
            
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
                    $data['counts'] = StudentProgress::where('stars', $request->stars)->count();
                } else {
                    $data['counts'] = StudentProgress::where('student_id', $studentId)
                                                     ->where('program_id', $request->program_id)
                                                     ->count();
                }
                $division = StudentProgress::where('student_id', $studentId)
                ->count();
                if($division == 0 )
                $division = 1;
                if(!$request->filled('from_date') && !$request->filled('to_date'))
                $data['reports_percentages'] = [
                    'three_star' =>( (StudentProgress::where('stars',3)->where('student_id', $studentId)
                                            ->where('program_id', $request->program_id)->count()/$division)*100) ?? 0,
                    'two_star' => ((StudentProgress::where('stars',2)->where('student_id', $studentId)
                                            ->where('program_id', $request->program_id)->count()/$division)*100) ?? 0,
                     'one_star' => ((StudentProgress::where('stars',1)->where('student_id', $studentId)
                                            ->where('program_id', $request->program_id)->count()/$division)*100) ?? 0,
            
                ];
                else{
                    
                    $threestars = StudentProgress::where('stars',3)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                                            ->where('program_id', $request->program_id)->count();
                    $twostars=StudentProgress::where('stars',2)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                    ->where('program_id', $request->program_id)->count();
                    $onestar = StudentProgress::where('stars',1)->where('student_id', $studentId)->whereBetween('student_progress.created_at', [$from_date, $to_date])
                                            ->where('program_id', $request->program_id)->count();
                                            
                    $division =StudentProgress::where('student_id', $studentId)
                    ->whereBetween('student_progress.created_at', [$from_date, $to_date])->count(); 
                    
                    
                    if($division == 0){
                        $division = 1;
                    }
                $data['reports_percentages'] = [
                    'three_star' => (($threestars / $division)*100),
                    'two_star' => (($twostars / $division)*100),
                     'one_star' => (($onestar / $division)*100),
            
                ];
            }
                $test_types = TestTypes::all();
                $data['test_types'] = TestResource::make($test_types);
            
                return $this->returnData('data', $data, 'Student Progress');
            }
            public function masteryReport(Request $request){
                
            }
            public function numOfTrialsReport(Request $request){
                
            }
            public function skillReport(Request $request){
                
            }
}
