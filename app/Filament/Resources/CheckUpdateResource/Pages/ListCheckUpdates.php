<?php

namespace App\Filament\Resources\CheckUpdateResource\Pages;

use App\Filament\Resources\CheckUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckUpdates extends ListRecords
{
    protected static string $resource = CheckUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
