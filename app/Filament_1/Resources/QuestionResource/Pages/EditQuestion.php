<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Choice;
use App\Models\RevisionQuestionsBank;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;
    public $field_names = [];
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];

    }

    protected function handleRecordCreation(array $data): Model
    {
        //insert the student
       
        $record = static::getModel()::create($data);

       
        // Create a new Guardian model instance
        $revision_question = new RevisionQuestionsBank();
        $revision_question->bank_id = $data['bank_id'];
        $revision_question->type = $data['type'];
        $revision_question->number = $data['number'];
        $revision_question->time = $data['time'];
        $revision_question->difficulty = $data['difficulty'];
        $revision_question->question = $data['question'];
        if ($revision_question->type == 0) {
            $revision_question->first_part = $data['first_part'];
            $revision_question->second_part = $data['second_part'];
            $revision_question->answer = $data['answer'];
        } elseif ($revision_question->type == 2) {
            $revision_question->true_flag = $data['true_flag'];
            $revision_question->first_part = null;
            $revision_question->second_part = null;
            $revision_question->answer = null;
            $choices = Choice::where('question_id',$record->id)->delete();

        } else {
            foreach($data['choices'] as $c){
                
                $c = new Choice();
                $c->question_id = $record->id;
                $c->choice = $c;
                $c->is_correct = $c->is_correct ? true : false ;
                $c->save();
            }
            
            $revision_question->true_flag = null;
            $revision_question->first_part = null;
            $revision_question->second_part = null;
            $revision_question->answer = null;
            
            

        }
        
        // $guardian->first_name = $data['guardian_fname'];
        // $guardian->last_name = $data['guardian_lname'];
        // $guardian->gender = $data['guardian_gender'];
        // $guardian->email = $data['guardian_email'];
        // $guardian->contact_no = $data['guardian_contact'];

        // Assuming 'student_id' is the foreign key linking to students
        // $guardian->student_id = $record->student_id; 

        // Save the Guardian model to insert the data
        $revision_question->save();


        return $record;
    }
}
