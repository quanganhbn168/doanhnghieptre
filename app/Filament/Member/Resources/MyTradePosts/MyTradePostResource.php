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

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-handshake';

    protected static ?string $navigationLabel = 'Cơ hội giao thương';

    protected static ?string $modelLabel = 'cơ hội giao thương';

    protected static ?string $pluralModelLabel = 'cơ hội giao thương của tôi';

    protected static string|UnitEnum|null $navigationGroup = 'Kết nối & giao thương';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $memberId = auth('web')->user()?->member?->id;

        return parent::getEloquentQuery()
            ->when($memberId, fn (Builder $query) => $query->where('member_id', $memberId), fn (Builder $query) => $query->whereRaw('1 = 0'));
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
