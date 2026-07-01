<?php

namespace App\Filament\Resources\ProfessionalDevelopmentResource\Pages;

use App\Filament\Resources\ProfessionalDevelopmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProfessionalDevelopment extends EditRecord
{
    protected static string $resource = ProfessionalDevelopmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
