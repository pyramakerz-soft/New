<?php

namespace App\Filament\Resources\BeginningResource\Pages;

use App\Filament\Resources\BeginningResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeginnings extends ListRecords
{
    protected static string $resource = BeginningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
