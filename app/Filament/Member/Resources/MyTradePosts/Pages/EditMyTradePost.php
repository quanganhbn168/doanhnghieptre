<?php

namespace App\Filament\Member\Resources\MyTradePosts\Pages;

use App\Filament\Member\Resources\MyTradePosts\MyTradePostResource;
use App\Services\TradePostService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMyTradePost extends EditRecord
{
    protected static string $resource = MyTradePostResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TradePostService::class)->submit(auth('web')->user(), $data, $record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Đã gửi tin cho Hội duyệt';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['content'] = strip_tags($data['content'] ?? '');

        return $data;
    }
}
