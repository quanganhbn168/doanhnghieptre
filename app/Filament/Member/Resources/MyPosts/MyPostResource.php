<?php

namespace App\Filament\Member\Resources\MyPosts;

use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MyPostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $slug = 'bai-viet';

    protected static ?string $navigationLabel = 'Bài viết doanh nghiệp';

    protected static ?string $modelLabel = 'bài viết hội viên';

    protected static ?string $pluralModelLabel = 'bài viết hội viên';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|UnitEnum|null $navigationGroup = 'Kết nối & giao thương';

    public static function getEloquentQuery(): Builder
    {
        $user = auth('web')->user();

        return parent::getEloquentQuery()->with('business')->when($user, fn (Builder $query) => $query->ownedBy($user), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canViewAny(): bool
    {
        return (bool) auth('web')->user()?->hasApprovedBusiness();
    }

    public static function canView($record): bool
    {
        return static::canViewAny() && static::getEloquentQuery()->whereKey($record->id)->exists();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canView($record);
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
        return Schemas\MyPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\MyPostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMyPosts::route('/'), 'create' => Pages\CreateMyPost::route('/create'), 'edit' => Pages\EditMyPost::route('/{record}/edit')];
    }
}
