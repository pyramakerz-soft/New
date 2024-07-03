<?php

namespace App\Filament\Resources\BeginningResource\Pages;

use App\Filament\Resources\BeginningResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBeginning extends ViewRecord
{
    protected static string $resource = BeginningResource::class;
    protected static string $view = 'filament.pages.view-video';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
