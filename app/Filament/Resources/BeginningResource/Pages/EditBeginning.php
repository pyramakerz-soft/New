<?php

namespace App\Filament\Resources\BeginningResource\Pages;

use App\Filament\Resources\BeginningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBeginning extends EditRecord
{
    protected static string $resource = BeginningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
