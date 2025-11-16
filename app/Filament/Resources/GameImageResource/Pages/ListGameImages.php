<?php

namespace App\Filament\Resources\GameImageResource\Pages;

use App\Filament\Resources\GameImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGameImages extends ListRecords
{
    protected static string $resource = GameImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
