<?php

namespace App\Filament\Teacher\Resources\ProgramResource\Pages;

use App\Filament\Teacher\Resources\ProgramResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResource extends ViewRecord
{
    protected static string $resource = ProgramResource::class;
    use HasPageSidebar;

}
