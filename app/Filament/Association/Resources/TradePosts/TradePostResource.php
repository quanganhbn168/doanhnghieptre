<?php

namespace App\Filament\Association\Resources\TradePosts;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use App\Models\TradePost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TradePostResource extends Resource
{
    use ManagesAssociationContent;

    protected static ?string $model = TradePost::class;

    protected static ?string $slug = 'cho-doanh-nghiep';

    protected static ?string $navigationLabel = 'Duyệt tin giao thương';

    protected static ?string $modelLabel = 'tin giao thương';

    protected static ?string $pluralModelLabel = 'tin giao thương';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Chợ doanh nghiệp';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return Schemas\TradePostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\TradePostsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::canViewAny() ? (string) TradePost::query()->where('status', 'pending')->count() : null;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTradePosts::route('/'), 'view' => Pages\ViewTradePost::route('/{record}')];
    }
}
