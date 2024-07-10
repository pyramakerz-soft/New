<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use App\Models\RevisionQuestionsBank;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTest extends CreateRecord
{
    public $field_names = [];
        protected static string $resource = TestResource::class;
        protected function handleRecordCreation(array $data): Model
    {
        //insert the student
        $record =  static::getModel()::create($data);
        // Create a new Guardian model instance
        $revision_question = new RevisionQuestionsBank();
        // $guardian->first_name = $data['guardian_fname'];
        // $guardian->last_name = $data['guardian_lname'];
        // $guardian->gender = $data['guardian_gender'];
        // $guardian->email = $data['guardian_email'];
        // $guardian->contact_no = $data['guardian_contact'];

        // Assuming 'student_id' is the foreign key linking to students
        // $guardian->student_id = $record->student_id; 

        // Save the Guardian model to insert the data
        // $guardian->save();


        return $record;
    }
}
