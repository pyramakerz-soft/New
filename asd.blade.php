<?php
public function classMasteryReport(Request $request)
{
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
                'name' => Lesson::find($progress->lesson_id)->name,
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
            foreach ($gameType->skills as $gameSkill) {
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
    return $this->returnData('data', $responseData, 'Group Progress');
}