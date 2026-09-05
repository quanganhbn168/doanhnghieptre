<?php

namespace App\Filament\Member\Resources\MyTradePosts\Pages;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use App\Services\TradePostService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMyTradePost extends CreateRecord
{
    protected static string $resource = MyTradePostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TradePostService::class)->submit(auth('web')->user(), $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Đã gửi tin cho Hội duyệt';
    }
}
