<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use App\Models\Business;
use App\Services\BusinessService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMyBusiness extends EditRecord
{
    protected static string $resource = MyBusinessResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($record, $data): void {
            $locked = Business::query()->lockForUpdate()->findOrFail($record->id);
            abort_unless(MyBusinessResource::canEdit($locked), 403);
            app(BusinessService::class)->update($locked, $data);
            $locked->transitionTo('pending', auth('web')->id(), 'Người đại diện cập nhật hồ sơ; gửi lại để Hội duyệt và Chi hội tiếp nhận.');
        });

        return $record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return route('account.dashboard');
    }
}
