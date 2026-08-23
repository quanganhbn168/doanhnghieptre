<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use App\Services\BusinessService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMyBusiness extends EditRecord
{
    protected static string $resource = MyBusinessResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(BusinessService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function afterSave(): void
    {
        if ($this->record->status !== 'pending') {
            $this->record->transitionTo('pending', auth('web')->id(), 'Hội viên đã cập nhật hồ sơ doanh nghiệp và gửi lại để Hội kiểm tra.');
        }
    }
}
