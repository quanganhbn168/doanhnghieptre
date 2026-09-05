<?php

namespace App\Filament\Member\Resources\MyBusinesses;

use App\Filament\Member\Resources\MyBusinesses\Pages\CreateMyBusiness;
use App\Filament\Member\Resources\MyBusinesses\Pages\EditMyBusiness;
use App\Filament\Member\Resources\MyBusinesses\Pages\ListMyBusinesses;
use App\Filament\Member\Resources\MyBusinesses\Schemas\MyBusinessForm;
use App\Filament\Member\Resources\MyBusinesses\Tables\MyBusinessesTable;
use App\Models\Business;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MyBusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Hồ sơ doanh nghiệp';

    protected static ?string $modelLabel = 'doanh nghiệp';

    protected static ?string $pluralModelLabel = 'doanh nghiệp của tôi';

    protected static string|UnitEnum|null $navigationGroup = 'Doanh nghiệp của tôi';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $user = auth('web')->user();

        return parent::getEloquentQuery()
            ->when($user, fn (Builder $query) => $query->representedBy($user), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return in_array($record->status, ['approved', 'draft', 'rejected'], true) && static::getEloquentQuery()->whereKey($record->id)->exists();
    }

    public static function form(Schema $schema): Schema
    {
        return MyBusinessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyBusinessesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyBusinesses::route('/'),
            'create' => CreateMyBusiness::route('/create'),
            'edit' => EditMyBusiness::route('/{record}/edit'),
        ];
    }
}
