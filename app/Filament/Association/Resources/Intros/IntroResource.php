<?php

namespace App\Filament\Association\Resources\Intros;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use UnitEnum;

class IntroResource extends \App\Filament\Resources\Intros\IntroResource
{
    use ManagesAssociationContent;

    protected static ?string $slug = 'thong-tin-hoi';

    protected static ?string $navigationLabel = 'Thông tin Hội';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung Hội';

    public static function getPages(): array
    {
        return ['index' => Pages\ListIntros::route('/'), 'create' => Pages\CreateIntro::route('/create'), 'edit' => Pages\EditIntro::route('/{record}/edit')];
    }
}
