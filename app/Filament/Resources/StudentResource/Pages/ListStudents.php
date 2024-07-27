<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;
    // protected function applyFilters(Builder $query): Builder
    // {
    //     return $query
    //         ->when($this->getTableFilters()->get('stage'), function ($query, $stage) {
    //             $query->where('stage_id', $stage->getState());
    //         })
    //         ->when($this->getTableFilters()->get('school'), function ($query, $school) {
    //             $query->where('school_id', $school->getState());
    //         })
    //         ->when($this->getTableFilters()->get('third_criterion'), function ($query, $thirdCriterion) {
    //             $query->where('third_criterion_column', $thirdCriterion->getState());
    //         });
    // }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
