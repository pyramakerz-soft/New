<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
