<?php

namespace App\Filament\Member\Resources\MyTradePosts\Pages;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyTradePosts extends ListRecords
{
    protected static string $resource = MyTradePostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Đăng cơ hội giao thương')];
    }
}
