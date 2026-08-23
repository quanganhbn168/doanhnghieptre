<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyBusinesses extends ListRecords
{
    protected static string $resource = MyBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm doanh nghiệp')];
    }
}
