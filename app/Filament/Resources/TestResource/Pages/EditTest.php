<?php

namespace App\Filament\Resources\TestResource\Pages;

use App\Filament\Resources\TestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTest extends EditRecord
{
    protected static string $resource = TestResource::class;
    public $field_names = [];
    protected function getHeaderActions(): array
    {
        
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
