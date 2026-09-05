<?php

namespace App\Filament\Resources\ProfessionalGroups;

use App\Filament\Resources\ProfessionalGroups\Pages\CreateProfessionalGroup;
use App\Filament\Resources\ProfessionalGroups\Pages\EditProfessionalGroup;
use App\Filament\Resources\ProfessionalGroups\Pages\ListProfessionalGroups;
use App\Filament\Resources\ProfessionalGroups\Schemas\ProfessionalGroupForm;
use App\Filament\Resources\ProfessionalGroups\Tables\ProfessionalGroupsTable;
use App\Models\Industry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProfessionalGroupResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Khối ngành nghề';

    protected static ?string $modelLabel = 'khối ngành nghề';

    protected static ?string $pluralModelLabel = 'khối ngành nghề';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->memberGroups()->with('image')->withCount(['businesses as business_count' => fn ($query) => $query->where('status', 'approved')]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProfessionalGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionalGroupsTable::configure($table);
    }

    public static function canDelete($record): bool
    {
        return parent::canDelete($record) && ! $record->hasAssignments();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfessionalGroups::route('/'),
            'create' => CreateProfessionalGroup::route('/create'),
            'edit' => EditProfessionalGroup::route('/{record}/edit'),
        ];
    }
}
