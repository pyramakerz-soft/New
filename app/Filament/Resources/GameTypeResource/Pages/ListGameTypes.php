<?php

namespace App\Filament\Resources\GameTypeResource\Pages;

use App\Filament\Resources\GameTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameTypes extends ListRecords
{
    protected static string $resource = GameTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
