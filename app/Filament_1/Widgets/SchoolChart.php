<?php

namespace App\Filament\Widgets;

use App\Models\School;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SchoolChart extends ChartWidget
{
    protected static ?string $heading = 'Schools';
    protected static string $color = 'warning';
 protected static ?int $sort = 2;
    protected function getData(): array
    {
        $data = Trend::model(School::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();
 
    return [
        'datasets' => [
            [
                'label' => 'School',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $data->map(fn (TrendValue $value) => $value->date),
    ];
    }
    protected function getType(): string
    {
        return 'line';
    }
}
