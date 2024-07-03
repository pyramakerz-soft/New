<?php

namespace App\Filament\Teacher\Resources\TestResource\Pages;

use App\Filament\Teacher\Resources\TestResource;
use App\Models\Test;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateTest extends CreateRecord
{
    protected static string $resource = TestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $temp = $data;
        // unset($data['user_id']);
        $record = static::getModel()::create($data);
        $test =  Test::find($record->id);
        $test->user_id = Auth::id();
        $test->type = $temp['type'];
        $test->save();

        // Insert the student record

        return $record;
    }
}
