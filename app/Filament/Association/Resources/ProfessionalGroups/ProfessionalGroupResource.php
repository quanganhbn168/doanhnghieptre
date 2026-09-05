<?php

namespace App\Filament\Association\Resources\ProfessionalGroups;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use UnitEnum;

class ProfessionalGroupResource extends \App\Filament\Resources\ProfessionalGroups\ProfessionalGroupResource
{
    use ManagesAssociationContent;

    protected static ?string $slug = 'khoi-nganh-nghe';

    protected static string|UnitEnum|null $navigationGroup = 'Tổ chức Hội';

    public static function canDelete($record): bool
    {
        return static::canViewAny() && ! $record->hasAssignments();
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProfessionalGroups::route('/'), 'create' => Pages\CreateProfessionalGroup::route('/create'), 'edit' => Pages\EditProfessionalGroup::route('/{record}/edit')];
    }
}
