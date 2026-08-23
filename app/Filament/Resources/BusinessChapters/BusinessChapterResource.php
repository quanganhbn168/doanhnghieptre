<?php

namespace App\Filament\Resources\BusinessChapters;

use App\Filament\Resources\BusinessChapters\Pages\CreateBusinessChapter;
use App\Filament\Resources\BusinessChapters\Pages\EditBusinessChapter;
use App\Filament\Resources\BusinessChapters\Pages\ListBusinessChapters;
use App\Models\BusinessChapter;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class BusinessChapterResource extends Resource
{
    protected static ?string $model = BusinessChapter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Chi hội trực thuộc';

    protected static ?string $modelLabel = 'chi hội';

    protected static ?string $pluralModelLabel = 'chi hội';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin chi hội')
                ->description('Dữ liệu khởi tạo gồm 09 chi hội trực thuộc Hội.')
                ->schema([
                    TextInput::make('name')->label('Tên chi hội')->required()->maxLength(255),
                    TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->default(fn (): int => ((int) BusinessChapter::query()->max('sort_order')) + 10)->required(),
                    Toggle::make('is_active')->label('Đang hoạt động')->default(true),
                    Textarea::make('description')->label('Mô tả')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Chi hội')->searchable()->sortable(),
                TextColumn::make('description')->label('Mô tả')->limit(80)->toggleable(),
                TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
                ToggleColumn::make('is_active')->label('Hoạt động'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessChapters::route('/'),
            'create' => CreateBusinessChapter::route('/create'),
            'edit' => EditBusinessChapter::route('/{record}/edit'),
        ];
    }
}
