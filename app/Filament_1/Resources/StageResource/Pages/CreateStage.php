<?php

namespace App\Filament\Resources\StageResource\Pages;

use App\Filament\Resources\StageResource;
use App\Models\Program;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStage extends CreateRecord
{
    protected ?string $heading = 'Create Stage Program';
    protected static string $resource = StageResource::class;


}
