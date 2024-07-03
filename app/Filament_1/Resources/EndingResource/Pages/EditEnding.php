<?php

namespace App\Filament\Resources\EndingResource\Pages;

use App\Filament\Resources\EndingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnding extends EditRecord
{
    protected static string $resource = EndingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
