<?php

namespace App\Filament\Teacher\Resources\TestResource\Pages;

use App\Filament\Teacher\Resources\TestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTest extends ViewRecord
{
    protected static string $resource = TestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
