<?php

namespace App\Filament\Teacher\Resources\ProgramResource\Pages;

use App\Filament\Teacher\Resources\ProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
