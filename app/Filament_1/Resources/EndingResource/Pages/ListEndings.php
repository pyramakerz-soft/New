<?php

namespace App\Filament\Resources\EndingResource\Pages;

use App\Filament\Resources\EndingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEndings extends ListRecords
{
    protected static string $resource = EndingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
