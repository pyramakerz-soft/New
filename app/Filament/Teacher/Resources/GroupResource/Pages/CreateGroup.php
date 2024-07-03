<?php

namespace App\Filament\Teacher\Resources\GroupResource\Pages;

use App\Filament\Teacher\Resources\GroupResource;
use App\Models\GroupCourse;
use App\Models\GroupStudent;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     //insert the student
    //     // $stage = $data;

  
    //     $temp = $data;
    //     unset($data['program_id']);
    //     $record = static::getModel()::create($data);
    //     $group = new GroupCourse();
    //     $group->group_id = $record->id;
    //     $group->program_id = $temp["program_id"];
    //     $group->save();

    //     return $record;
    // }
}
