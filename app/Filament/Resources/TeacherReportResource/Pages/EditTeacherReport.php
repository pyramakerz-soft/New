<?php

namespace App\Filament\Resources\TeacherReportResource\Pages;

use App\Filament\Resources\TeacherReportResource;
use App\Filament\Resources\TeacherReportResource\Widgets\CompReportStats;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherReport extends EditRecord
{
    protected static string $resource = TeacherReportResource::class;

    // public function getHeaderWidgets(): array
    // {
    //     return [
    //         CompReportStats::class,
    //     ];
    // }
     protected function getHeaderWidgets(): array
    {
        return [
            TeacherReportResource\Widgets\CompReportStats::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
