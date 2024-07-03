<?php

namespace App\Filament\Resources\PptResource\Pages;

use App\Filament\Resources\PptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPpts extends ListRecords
{
    protected static string $resource = PptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
