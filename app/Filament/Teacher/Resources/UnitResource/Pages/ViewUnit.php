<?php

namespace App\Filament\Teacher\Resources\UnitResource\Pages;

use App\Filament\Teacher\Resources\UnitResource;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;
    use HasPageSidebar;
}
