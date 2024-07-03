<?php

namespace App\Filament\Resources\LessonPlanResource\Pages;

use App\Filament\Resources\LessonPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonPlans extends ListRecords
{
    protected static string $resource = LessonPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
