<?php

namespace App\Filament\Resources\Intros\Pages;

use App\Filament\Resources\Intros\IntroResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntro extends CreateRecord
{
    protected static string $resource = IntroResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
