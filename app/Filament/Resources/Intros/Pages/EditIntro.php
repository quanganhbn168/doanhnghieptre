<?php

namespace App\Filament\Resources\Intros\Pages;

use App\Filament\Resources\Intros\IntroResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIntro extends EditRecord
{
    protected static string $resource = IntroResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
