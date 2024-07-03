<?php

namespace App\Filament\Resources\CheckpointResource\Pages;

use App\Filament\Resources\CheckpointResource;
use App\Models\CheckpointAssignedTo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCheckpoint extends CreateRecord
{
    protected static string $resource = CheckpointResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        //insert the student
        $tmp = array();
        $tmp = $data;
        unset($data['lesson_id']);
        unset($data['checkpoint_id']);
        unset($data['program_id']);
        unset($data['unit_id']);
        // dd($data);
        $record = static::getModel()::create($data);

        foreach($tmp['lesson_id'] as $index  => $lesson){
            
        $assign = new CheckpointAssignedTo();
        $assign->number = $index+1;
        // $assign->unit_id = $tmp['unit_id'];
        $assign->program_id = $tmp['program_id'];
        $assign->checkpoint_id = $record->id;
        $assign->lesson_id = $lesson;
        $assign->save();
}
        
     return $record;   
    }

    
}
