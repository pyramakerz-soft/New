<?php

namespace App\Filament\Resources\PptResource\Pages;

use App\Filament\Resources\PptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPpt extends EditRecord
{
    protected static string $resource = PptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
