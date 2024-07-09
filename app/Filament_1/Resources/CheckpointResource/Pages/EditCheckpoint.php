<?php

namespace App\Filament\Resources\CheckpointResource\Pages;

use App\Filament\Resources\CheckpointResource;
use App\Models\CheckpointAssignedTo;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCheckpoint extends EditRecord
{
    protected static string $resource = CheckpointResource::class;

    protected function handleRecordUpdate(Model $record, $data): Model
    {
        $tmp = $data;
$tmp2 = $record;

        unset($data['lesson_id']);
        unset($data['checkpoint_id']);
        unset($data['program_id']);
        unset($data['unit_id']);
        // $record->fill($data);
        // $record->save();

        $tmp3 = CheckpointAssignedTo::where('checkpoint_id',$record->id)->first()->program_id;
        CheckpointAssignedTo::where('checkpoint_id',$record->id)->where('program_id',$tmp3)->delete();
        foreach ($tmp['lesson_id'] as $index => $lesson) {
            $assign = new CheckpointAssignedTo();
            $assign->checkpoint_id = $tmp2->id;
            $assign->lesson_id = $lesson;
            $assign->number = $index + 1;
            $assign->program_id = $tmp3;

            $assign->save();
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

}
