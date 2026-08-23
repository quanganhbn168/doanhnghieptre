<?php

namespace App\Filament\Resources\Intros;

use App\Filament\Resources\Intros\Pages\CreateIntro;
use App\Filament\Resources\Intros\Pages\EditIntro;
use App\Filament\Resources\Intros\Pages\ListIntros;
use App\Models\Intro;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
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

class IntroResource extends Resource
{
    protected static ?string $model = Intro::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationLabel = 'Trang giới thiệu';

    protected static ?string $modelLabel = 'nội dung intro';

    protected static ?string $pluralModelLabel = 'nội dung intro';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung website';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung intro')
                ->description('Các phần này hiển thị tại trang Giới thiệu của Hội.')
                ->schema([
                    TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true)->helperText('Ví dụ: gioi-thieu-hoi hoặc co-cau-to-chuc-hoi.')->columnSpanFull(),
                    TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->default(fn (): int => Intro::nextSortOrder())->required()->columnSpanFull(),
                    Textarea::make('summary')->label('Mô tả ngắn')->rows(3)->columnSpanFull(),
                    RichEditor::make('content')->label('Nội dung')->columnSpanFull(),
                    Toggle::make('is_active')->label('Hiển thị trên website')->default(true)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Tiêu đề')->searchable()->sortable()->wrap(),
                TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
                ToggleColumn::make('is_active')->label('Hiển thị'),
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
            'index' => ListIntros::route('/'),
            'create' => CreateIntro::route('/create'),
            'edit' => EditIntro::route('/{record}/edit'),
        ];
    }
}
