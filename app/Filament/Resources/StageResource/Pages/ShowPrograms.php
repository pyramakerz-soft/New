<?php

namespace App\Filament\Resources\StageResource\Pages;

use App\Filament\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ShowPrograms extends ListRecords
{
    protected static string $view = 'filament.pages.programs.show'; 

    protected static string $resource = StageResource::class;
    
    
}
