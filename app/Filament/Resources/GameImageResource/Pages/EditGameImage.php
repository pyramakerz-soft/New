<?php

namespace App\Filament\Resources\GameImageResource\Pages;

use App\Filament\Resources\GameImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGameImage extends EditRecord
{
    protected static string $resource = GameImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
