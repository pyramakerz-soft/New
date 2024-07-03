<?php

namespace App\Filament\Resources\PresentationResource\Pages;

use App\Filament\Resources\PresentationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreatePresentation extends CreateRecord
{
    protected static string $resource = PresentationResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array 
    {
        $data['type'] = Storage::disk('public')->mimeType($data['video']);
 
        return $data;
    } 
}
