<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use App\Models\Business;
use App\Services\BusinessProfileReviewService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditMyBusiness extends EditRecord
{
    protected static string $resource = MyBusinessResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, ...($this->record->pending_profile ?? []), 'industry_ids' => $this->record->pending_profile['industry_ids'] ?? $this->record->industries->sortByDesc('pivot.is_primary')->modelKeys()];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($record, $data): void {
            $locked = Business::query()->lockForUpdate()->findOrFail($record->id);
            abort_unless(MyBusinessResource::canEdit($locked), 403);
            app(BusinessProfileReviewService::class)->submit($locked, auth('web')->user(), $data);
        });

        return $record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return MyBusinessResource::getUrl('index');
    }
}
