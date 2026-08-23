<?php

namespace App\Filament\Resources\Intros\Pages;

use App\Filament\Resources\Intros\IntroResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIntros extends ListRecords
{
    protected static string $resource = IntroResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm nội dung intro')];
    }
}
