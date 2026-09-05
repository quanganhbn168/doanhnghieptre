<?php

namespace App\Filament\Resources\Intros\Schemas;

use App\Models\Intro;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin Hội')
                ->columnSpanFull()
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
}
