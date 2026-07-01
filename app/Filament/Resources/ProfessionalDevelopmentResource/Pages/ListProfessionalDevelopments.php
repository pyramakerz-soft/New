<?php

namespace App\Filament\Resources\ProfessionalDevelopmentResource\Pages;

use App\Filament\Resources\ProfessionalDevelopmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfessionalDevelopments extends ListRecords
{
    protected static string $resource = ProfessionalDevelopmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
