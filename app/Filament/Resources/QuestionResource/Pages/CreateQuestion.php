<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Choice;
use App\Models\Question;
use App\Models\RevisionQuestionsBank;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuestion extends CreateRecord
{
    public $field_names = [];
    public $test = [];
    protected static string $resource = QuestionResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        
        $record = static::getModel()::create($data);
            $question = Question::find($record->id);
            $number = Question::where('test_id',$question->test_id)->orderBy('id','desc')->count();
            $question->number = $number;
$index = $data['control']['answer'];
            $num = json_decode($data['choices'][$index],true);
            $question->save();
            $question = Question::find($record->id)->update(['control->answer' => $num]);

            $revision = new RevisionQuestionsBank();
            $revision->bank_id = $record->bank_id;
            $revision->question_id = $record->id;
            $revision->number = $number;
            $revision->save();
        return $record;
    }
        //insert the student
        // $choices = $data;
        // dd($data);
        // unset($data['choices']);
        // $record = static::getModel()::create($data);
        
        // Create a new Guardian model instance
        // $revision_question = new RevisionQuestionsBank();
        // $revision_question->bank_id = $data['bank_id'];
        // $revision_question->type = $data['type'];
        // $revision_question->number = $data['number'];
        // $revision_question->time = $data['time'];
        // $revision_question->difficulty = $data['difficulty'];
        // $revision_question->question = $data['question'];
        // if ($revision_question->type == 0) {
        //     $revision_question->first_part = $data['first_part'];
        //     $revision_question->second_part = $data['second_part'];
        //     $revision_question->answer = $data['answer'];
        // } elseif ($revision_question->type == 2) {
        //     $revision_question->true_flag = $data['true_flag'];
        //     $revision_question->first_part = null;
        //     $revision_question->second_part = null;
        //     $revision_question->answer = null;
        //     $choices = Choice::where('question_id',$record->id)->delete();

        // } 
        // else {
        //     foreach($choices['choices'] as $c){
                
        //         if($c['choice'] == $data['answer']){
        //             array_push($c,['is_correct' => 1]);
        //         }else
        //         array_push($c,['is_correct' => 0]);
        //         if($c[0]['is_correct'] == 1)
        //         $is_correct = 1;
        //     else
        //     $is_correct = 0;
        //         // dd($c[0]['is_correct']);
        //         $ch = new Choice();
        //         $ch->question_id = $record->id;
        //         $ch->choice = $c['choice'];
        //         $ch->is_correct = $is_correct;
        //         $ch->save();
        //     }
            
        //     $revision_question->true_flag = null;
             
        //     $revision_question->answer = null;
            
            

        // }

        // $guardian->first_name = $data['guardian_fname'];
        // $guardian->last_name = $data['guardian_lname'];
        // $guardian->gender = $data['guardian_gender'];
        // $guardian->email = $data['guardian_email'];
        // $guardian->contact_no = $data['guardian_contact'];

        // Assuming 'student_id' is the foreign key linking to students
        // $guardian->student_id = $record->student_id; 

        // Save the Guardian model to insert the data
        // $revision_question->save();


        // return $record;
    // }
}