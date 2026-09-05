<?php

namespace App\Filament\Member\Resources\MyTradePosts;

use App\Filament\Member\Resources\MyTradePosts\Pages\CreateMyTradePost;
use App\Filament\Member\Resources\MyTradePosts\Pages\EditMyTradePost;
use App\Filament\Member\Resources\MyTradePosts\Pages\ListMyTradePosts;
use App\Filament\Member\Resources\MyTradePosts\Schemas\MyTradePostForm;
use App\Filament\Member\Resources\MyTradePosts\Tables\MyTradePostsTable;
use App\Models\TradePost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MyTradePostResource extends Resource
{
    protected static ?string $model = TradePost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Chợ doanh nghiệp';

    protected static ?string $modelLabel = 'tin giao thương';

    protected static ?string $pluralModelLabel = 'tin giao thương của doanh nghiệp';

    protected static string|UnitEnum|null $navigationGroup = 'Kết nối & giao thương';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $user = auth('web')->user();

        return parent::getEloquentQuery()->when($user, fn (Builder $query) => $query->ownedBy($user), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canViewAny(): bool
    {
        return (bool) auth('web')->user()?->hasApprovedBusiness();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && static::getEloquentQuery()->whereKey($record)->exists();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MyTradePostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MyTradePostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyTradePosts::route('/'),
            'create' => CreateMyTradePost::route('/create'),
            'edit' => EditMyTradePost::route('/{record}/edit'),
        ];
    }
}
