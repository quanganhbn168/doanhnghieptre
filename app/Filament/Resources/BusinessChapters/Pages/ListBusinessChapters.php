<?php

namespace App\Filament\Resources\BusinessChapters\Pages;

use App\Filament\Resources\BusinessChapters\BusinessChapterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessChapters extends ListRecords
{
    protected static string $resource = BusinessChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm chi hội')];
    }
}
