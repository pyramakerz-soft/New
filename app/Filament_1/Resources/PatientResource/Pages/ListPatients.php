<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Patient;
use Closure;
use Filament\Actions;
use Filament\Panel\Concerns\HasSidebar;
use Filament\Resources\Pages\ListRecords;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Illuminate\Support\Facades\Route;

class ListPatients extends ListRecords
{
    
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {

        return [
            Actions\CreateAction::make(),

        ];
    }
}
