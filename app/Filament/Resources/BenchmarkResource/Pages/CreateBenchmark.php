<?php

namespace App\Filament\Resources\BenchmarkResource\Pages;

use App\Filament\Resources\BenchmarkResource;
use App\Models\BenchmarkAssignTo;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBenchmark extends CreateRecord
{
    protected static string $resource = BenchmarkResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        //insert the student
        $tmp = array();
        $tmp = $data;
        unset($data['unit_id']);
        $record = static::getModel()::create($data);

        foreach ($tmp['unit_id'] as $index => $unit) {

            $assign = new BenchmarkAssignTo();
            $assign->number = $index + 1;
            $assign->benchmark_id = $record->id;
            $assign->unit_id = $unit;
            $assign->save();
        }

        return $record;
    }
}
