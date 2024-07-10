<?php

namespace App\Filament\Resources\TeacherEBookResource\Pages;

use App\Filament\Resources\TeacherEBookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherEBook extends EditRecord
{
    protected static string $resource = TeacherEBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
