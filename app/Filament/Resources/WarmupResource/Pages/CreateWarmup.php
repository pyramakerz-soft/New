<?php

namespace App\Filament\Resources\WarmupResource\Pages;

use App\Filament\Resources\WarmupResource;
use App\Models\WarmupVideo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWarmup extends CreateRecord
{
    protected static string $resource = WarmupResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        $warmups = $data;
        // unset($data['warmups']);
        unset($data['video']);

        $record = static::getModel()::create($data);
        $wamupVideo = new WarmupVideo();
        $wamupVideo->warmup_id = $record->id;
        $wamupVideo->video = $warmups['video'];
        $wamupVideo->save();
        return $record;
    }
}
