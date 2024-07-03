<?php

namespace App\Filament\Resources\TeacherEBookResource\Pages;

use App\Filament\Resources\TeacherEBookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherEBooks extends ListRecords
{
    protected static string $resource = TeacherEBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
