<?php

namespace App\Filament\Resources\TradePosts\Pages;

use App\Filament\Resources\TradePosts\TradePostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTradePost extends EditRecord
{
    protected static string $resource = TradePostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [...$data, 'status' => 'pending', 'approved_at' => null, 'closed_at' => null, 'reviewed_by' => null, 'review_note' => null];
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
