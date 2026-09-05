<?php

namespace App\Filament\Resources\ProfessionalGroups\Pages;

use App\Filament\Resources\ProfessionalGroups\ProfessionalGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfessionalGroups extends ListRecords
{
    protected static string $resource = ProfessionalGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Thêm khối ngành nghề')];
    }
}
