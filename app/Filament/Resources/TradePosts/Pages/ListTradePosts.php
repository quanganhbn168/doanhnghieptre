<?php

namespace App\Filament\Resources\TradePosts\Pages;

use App\Filament\Resources\TradePosts\TradePostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTradePosts extends ListRecords
{
    protected static string $resource = TradePostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm cơ hội giao thương')];
    }
}
