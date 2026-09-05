<?php

namespace App\Filament\Association\Resources\Chapters\Pages;

use App\Filament\Association\Resources\Chapters\ChapterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;
}
