<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $query->where('role', 2);
        return $query;
    }

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
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    // protected function getTableQuery(): Builder
    // {
    //     $query = parent::getTableQuery();

    //     $query->where('role', 2);

    //     if ($stage_id = $this->getTableFilterState('stage_id')) {
    //         dd($stage_id);
    //         // $query->with('details')->where('details.stage_id', $stage_id);
    //         $query->whereHas('details',function(Builder $query) use ($stage_id){
    //             // dd($stage_id['value']);
    //             $stage = $stage_id['value'];
    //             dd($query->where('stage_id', '=',$stage)->toSql());
    //             return $query->where('stage_id',$stage_id['value']);
    //         });
    //         // $query->whereHas('details', function (Builder $query) use ($stage_id) {
    //         //     // dd($stage);

    //         //             $query->where('id', $stage_id);
    //         //         });
    //     }

    //     if ($school = $this->getTableFilterState('school')) {
    //         $query->with('details')->where('school_id', $school);
    //     }

    //     return $query;
    // }
}
// $query = parent::getTableQuery();

// $query->where('is_student', 1);

// if ($stage = $this->getTableFilterState('stage')) {
//     $query->whereHas('details.stage', function (Builder $query) use ($stage) {
//         $query->where('id', $stage);
//     });
// }

// if ($school = $this->getTableFilterState('school')) {
//     $query->whereHas('details.school', function (Builder $query) use ($school) {
//         $query->where('id', $school);
//     });
// }

// return $query;