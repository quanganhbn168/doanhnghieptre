<?php

namespace App\Filament\Resources\SliderItems;

use App\Filament\Resources\SliderItems\Pages\CreateSliderItem;
use App\Filament\Resources\SliderItems\Pages\EditSliderItem;
use App\Filament\Resources\SliderItems\Pages\ListSliderItems;
use App\Filament\Resources\SliderItems\Schemas\SliderItemForm;
use App\Filament\Resources\SliderItems\Tables\SliderItemsTable;
use App\Models\SliderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SliderItemResource extends Resource
{
    protected static ?string $model = SliderItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Nội dung banner';

    protected static ?string $modelLabel = 'slide';

    protected static ?string $pluralModelLabel = 'slide';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung trang chủ';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return SliderItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SliderItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSliderItems::route('/'),
            'create' => CreateSliderItem::route('/create'),
            'edit' => EditSliderItem::route('/{record}/edit'),
        ];
    }
}
