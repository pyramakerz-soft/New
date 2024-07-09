<?php

namespace App\Filament\Resources\WarmupResource\Pages;

use App\Filament\Resources\WarmupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarmups extends ListRecords
{
    protected static string $resource = WarmupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
