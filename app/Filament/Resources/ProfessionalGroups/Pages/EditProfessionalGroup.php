<?php

namespace App\Filament\Resources\ProfessionalGroups\Pages;

use App\Filament\Resources\ProfessionalGroups\ProfessionalGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfessionalGroup extends EditRecord
{
    protected static string $resource = ProfessionalGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
