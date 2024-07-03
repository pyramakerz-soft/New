<?php

namespace App\Filament\Widgets;

use App\Models\Program;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProgramChart extends ChartWidget
{
    protected static ?string $heading = 'Programs';
    protected static string $color = 'success';
 protected static ?int $sort = 1;
    protected function getData(): array
    {
        $data = Trend::model(Program::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();
 
    return [
        'datasets' => [
            [
                'label' => 'Programs',
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
