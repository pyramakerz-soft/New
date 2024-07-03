<?php

namespace App\Filament\Teacher\Resources\UserResource\Pages;

use App\Filament\Teacher\Resources\UserResource;
use App\Models\UserDetails;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, $data): Model
    {
        //insert the student
        $temp = $data;
        
        unset($data['stage_id']);
        unset($data['user_id']);
        // $record = static::getModel()::update($data);
        $user =  UserDetails::find($record->id);
        $user->user_id = $record->id;
        $user->school_id = $temp["school_id"];
        $user->stage_id = $temp["stage_id"];
        $user->save();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
