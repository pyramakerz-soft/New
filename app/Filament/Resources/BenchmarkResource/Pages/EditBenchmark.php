<?php

namespace App\Filament\Resources\BenchmarkResource\Pages;

use App\Filament\Resources\BenchmarkResource;
use App\Models\BenchmarkAssignTo;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBenchmark extends EditRecord
{
    protected static string $resource = BenchmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['unit_id']);

        $record->update($data);

        return $record;
    }
}
