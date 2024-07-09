<?php

namespace App\Filament\Teacher\Resources\GroupResource\Pages;

use App\Filament\Teacher\Resources\GroupResource;
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


    // protected function handleRecordCreation(array $data): Model
    // {
    //     //insert the student
    //     // $stage = $data;



    //     $record = static::getModel()::Update($data);
    //     $stage = new GroupStudent();
    //     $stage->stage_id = $data["stage_id"];
    //     $stage->save();

    //     return $record;
    // }
}
