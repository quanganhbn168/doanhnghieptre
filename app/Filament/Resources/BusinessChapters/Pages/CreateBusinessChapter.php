<?php

namespace App\Filament\Resources\BusinessChapters\Pages;

use App\Filament\Resources\BusinessChapters\BusinessChapterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessChapter extends CreateRecord
{
    protected static string $resource = BusinessChapterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
