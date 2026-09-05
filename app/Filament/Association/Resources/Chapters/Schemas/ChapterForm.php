<?php

namespace App\Filament\Association\Resources\Chapters\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ChapterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin chi hội')->schema([
                TextInput::make('name')->label('Tên chi hội')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug($state ?? '')) : null),
                TextInput::make('slug')->label('Đường dẫn')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->maxValue(65535)->default(0)->required(),
                Toggle::make('is_active')->label('Đang hoạt động')->default(true),
                Textarea::make('description')->label('Giới thiệu chi hội')->maxLength(5000)->rows(4)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
}
