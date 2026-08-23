<?php

namespace App\Filament\Resources\BusinessChapters\Pages;

use App\Filament\Resources\BusinessChapters\BusinessChapterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessChapter extends EditRecord
{
    protected static string $resource = BusinessChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
