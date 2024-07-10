<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\GroupStudent;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     //insert the student
    //     $temp = $data;

    //     unset($data['group_id']);
    //     unset($data['student_id']);
    //     dd($record,$data);
    //     $groupStudent = GroupStudent::where('group_id',$record->id);
    //     $groupStudent->group_id = $record->id ?? $groupStudent->group_id;
    //     $groupStudent->student_id = $temp["student_id"];
    //     $groupStudent->save();

    //     return $record;
    // }
}
