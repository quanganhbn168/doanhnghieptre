<?php

namespace App\Filament\Resources\ProfessionalGroups\Pages;

use App\Filament\Resources\ProfessionalGroups\ProfessionalGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfessionalGroup extends CreateRecord
{
    protected static string $resource = ProfessionalGroupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'is_member_group' => true, 'parent_id' => null];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
