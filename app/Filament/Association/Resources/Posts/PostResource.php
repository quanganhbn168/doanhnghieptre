<?php

namespace App\Filament\Association\Resources\Posts;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use UnitEnum;

class PostResource extends \App\Filament\Resources\Posts\PostResource
{
    use ManagesAssociationContent;

    protected static ?string $slug = 'tin-tuc';

    protected static ?string $navigationLabel = 'Tin tức';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung Hội';

    public static function getPages(): array
    {
        return ['index' => Pages\ListPosts::route('/'), 'create' => Pages\CreatePost::route('/create'), 'edit' => Pages\EditPost::route('/{record}/edit')];
    }
}
