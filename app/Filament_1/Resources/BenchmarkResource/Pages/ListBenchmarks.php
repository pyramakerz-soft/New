<?php

namespace App\Filament\Resources\BenchmarkResource\Pages;

use App\Filament\Resources\BenchmarkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBenchmarks extends ListRecords
{
    protected static string $resource = BenchmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
