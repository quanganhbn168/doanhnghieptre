<?php

namespace App\Filament\Resources\TradePosts\Pages;

use App\Filament\Resources\TradePosts\TradePostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTradePost extends EditRecord
{
    protected static string $resource = TradePostResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
