<?php
namespace App\Filament\Resources\TeacherReportResource\Widgets;

use App\Filament\Resources\TeacherReportResource;
use App\Models\Course;
use App\Models\Program;
use App\Models\TeacherProgram;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Request;

class CompReportStats extends BaseWidget
{
    protected static string $resource = TeacherReportResource::class;

    protected function getStats(): array
    {
        $teacherId = Request::route('record');
        // dd($teacherId);
        return [
            Stat::make('Count Programs', TeacherProgram::where('teacher_id', $teacherId)->count()),
            // Stat::make('Views', Course::where('student_id', $this->studentId)->count()),
            Stat::make('Average read time', User::where('role', 2)->count()),
        ];
    }
}

