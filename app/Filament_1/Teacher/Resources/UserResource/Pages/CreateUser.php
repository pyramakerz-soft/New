<?php

namespace App\Filament\Teacher\Resources\UserResource\Pages;

use App\Filament\Teacher\Resources\UserResource;
use App\Models\UserDetails;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        //insert the student
        $temp = $data;
        
        unset($data['stage_id']);
        unset($data['user_id']);
        $record = static::getModel()::create($data);
        $user = new UserDetails();
        $user->user_id = $record->id;
        $user->school_id = $temp["school_id"];
        $user->stage_id = $temp["stage_id"];
        $user->save();

        return $record;
    }
}
