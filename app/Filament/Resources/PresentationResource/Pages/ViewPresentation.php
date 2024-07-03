<?php

namespace App\Filament\Resources\PresentationResource\Pages;

use App\Filament\Resources\PresentationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPresentation extends ViewRecord
{
    protected static string $resource = PresentationResource::class;
    protected static string $view = 'filament.pages.view-video'; 

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
