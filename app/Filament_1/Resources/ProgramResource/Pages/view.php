<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class view extends ViewRecord
{
    protected static string $resource = ProgramResource::class;
    use HasPageSidebar;

}
