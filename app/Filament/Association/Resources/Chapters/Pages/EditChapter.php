<?php

namespace App\Filament\Association\Resources\Chapters\Pages;

use App\Filament\Association\Resources\Chapters\ChapterResource;
use Filament\Resources\Pages\EditRecord;

class EditChapter extends EditRecord
{
    protected static string $resource = ChapterResource::class;
}
