<?php

namespace App\Filament\Resources\WarmupResource\Pages;

use App\Filament\Resources\WarmupResource;
use App\Models\WarmupVideo;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWarmup extends EditRecord
{
    protected static string $resource = WarmupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make()/,
        ];
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // unset($data['warmups']);
        unset($data['video']);


        $record->update($data);

        return $record;
    }



}
