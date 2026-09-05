<?php

namespace App\Filament\Member\Resources\MyBusinesses\Pages;

use App\Filament\Member\Resources\MyBusinesses\MyBusinessResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListMyBusinesses extends ListRecords
{
    protected static string $resource = MyBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('apply')->label('Đăng ký doanh nghiệp hội viên')->url(route('membership.create'))];
    }
}
