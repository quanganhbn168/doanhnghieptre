<?php

namespace App\Filament\Association\Resources\PostCategories;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use UnitEnum;

class PostCategoryResource extends \App\Filament\Resources\PostCategories\PostCategoryResource
{
    use ManagesAssociationContent;

    protected static ?string $slug = 'chuyen-muc';

    protected static ?string $navigationLabel = 'Chuyên mục tin';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung Hội';

    public static function getPages(): array
    {
        return ['index' => Pages\ListPostCategories::route('/'), 'create' => Pages\CreatePostCategory::route('/create'), 'edit' => Pages\EditPostCategory::route('/{record}/edit')];
    }
}
