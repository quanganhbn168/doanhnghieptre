<?php

namespace App\Filament\Association\Resources\TradePosts\Pages;

use App\Filament\Association\Resources\TradePosts\TradePostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTradePosts extends ListRecords
{
    protected static string $resource = TradePostResource::class;

    public function getTabs(): array
    {
        return ['all' => Tab::make('Tất cả'), 'pending' => Tab::make('Chờ Hội duyệt')->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')), 'approved' => Tab::make('Đã duyệt')->modifyQueryUsing(fn ($query) => $query->where('status', 'approved'))];
    }
}
