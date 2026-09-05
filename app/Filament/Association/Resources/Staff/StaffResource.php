<?php

namespace App\Filament\Association\Resources\Staff;

use App\Filament\Association\Resources\Staff\Pages\CreateStaff;
use App\Filament\Association\Resources\Staff\Pages\EditStaff;
use App\Filament\Association\Resources\Staff\Pages\ListStaff;
use App\Filament\Association\Resources\Staff\Schemas\StaffForm;
use App\Filament\Association\Resources\Staff\Tables\StaffTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StaffResource extends Resource
{
    public const ROLES = ['association_manager' => 'Cán bộ Hội', 'chapter_manager' => 'Cán bộ Chi hội'];

    protected static ?string $model = User::class;

    protected static ?string $slug = 'can-bo';

    protected static ?string $modelLabel = 'cán bộ';

    protected static ?string $pluralModelLabel = 'cán bộ Hội & Chi hội';

    protected static ?string $navigationLabel = 'Cán bộ & phân quyền';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Tổ chức Hội';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'managedChapters'])
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', array_keys(self::ROLES)))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereNotIn('name', array_keys(self::ROLES)));
    }

    public static function canViewAny(): bool
    {
        return (bool) auth('admin')->user()?->canManageAssociation();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && $record->id !== auth('admin')->id() && static::getEloquentQuery()->whereKey($record->id)->exists();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListStaff::route('/'), 'create' => CreateStaff::route('/create'), 'edit' => EditStaff::route('/{record}/edit')];
    }
}
