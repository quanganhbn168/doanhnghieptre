<?php

namespace App\Filament\Association\Resources\MemberPosts;

use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MemberPostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $slug = 'bai-viet-hoi-vien';

    protected static ?string $navigationLabel = 'Duyệt bài hội viên';

    protected static ?string $modelLabel = 'bài viết hội viên';

    protected static ?string $pluralModelLabel = 'bài viết hội viên';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung Hội';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('business_id')->with(['business', 'category', 'media']);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth('admin')->user()?->canModerateContent();
    }

    public static function canView($record): bool
    {
        return static::canViewAny() && static::getEloquentQuery()->whereKey($record->id)->exists();
    }

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
        return Schemas\MemberPostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\MemberPostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMemberPosts::route('/'), 'view' => Pages\ViewMemberPost::route('/{record}')];
    }
}
