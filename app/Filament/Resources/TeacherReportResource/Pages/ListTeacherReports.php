<?php

namespace App\Filament\Resources\TeacherReportResource\Pages;

use App\Filament\Resources\TeacherReportResource;
use App\Filament\Resources\TeacherReportResource\Widgets\CompReportStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTeacherReports extends ListRecords
{
  
    protected static string $resource = TeacherReportResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $query->where('role', 1);
        return $query;
    }

}
