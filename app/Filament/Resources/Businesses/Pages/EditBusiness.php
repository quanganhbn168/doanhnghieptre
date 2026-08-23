<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Services\BusinessService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(BusinessService::class)->update($record, $data);

        return $record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
