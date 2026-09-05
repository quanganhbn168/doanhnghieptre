<?php

namespace App\Filament\Association\Resources\Chapters\Pages;

use App\Filament\Association\Resources\Chapters\ChapterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChapters extends ListRecords
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
