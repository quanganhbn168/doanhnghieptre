<?php

namespace App\Filament\Association\Resources\TradePosts\Pages;

use App\Filament\Association\Actions\TradeReviewActions;
use App\Filament\Association\Resources\TradePosts\TradePostResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTradePost extends ViewRecord
{
    protected static string $resource = TradePostResource::class;

    protected function getHeaderActions(): array
    {
        return TradeReviewActions::make();
    }
}
