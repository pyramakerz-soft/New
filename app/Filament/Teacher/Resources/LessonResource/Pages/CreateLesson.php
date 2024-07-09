<?php

namespace App\Filament\Teacher\Resources\LessonResource\Pages;

use App\Filament\Teacher\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;
}
