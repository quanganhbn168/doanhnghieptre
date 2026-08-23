<?php

namespace App\Filament\Member\Resources\MyTradePosts\Pages;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use Filament\Resources\Pages\EditRecord;

class EditMyTradePost extends EditRecord
{
    protected static string $resource = MyTradePostResource::class;

    protected function afterSave(): void
    {
        if ($this->record->status !== 'pending') {
            $this->record->update([
                'status' => 'pending',
                'approved_at' => null,
                'reviewed_by' => null,
                'review_note' => null,
            ]);
        }
    }
}
