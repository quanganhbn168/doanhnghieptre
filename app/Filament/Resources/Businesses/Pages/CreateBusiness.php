<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Services\BusinessService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(BusinessService::class)->create($data);
    }
}
