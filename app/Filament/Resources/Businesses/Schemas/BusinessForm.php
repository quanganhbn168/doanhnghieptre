<?php

namespace App\Filament\Resources\Businesses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nhận diện doanh nghiệp')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo doanh nghiệp')
                        ->image()
                        ->storeFiles(false)
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ]),
            Section::make('Thông tin doanh nghiệp')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    TextInput::make('name')->label('Tên giao dịch')->required()->maxLength(255),
                    TextInput::make('legal_name')->label('Tên pháp lý')->maxLength(255),
                    TextInput::make('representative_job_title')->label('Chức danh người đại diện')->maxLength(160),
                    TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('tax_code')->label('Mã số thuế')->maxLength(32)->unique(ignoreRecord: true),
                    Select::make('business_category_id')->label('Nhóm doanh nghiệp')->relationship('category', 'name')->searchable()->preload(),
                    Select::make('business_chapter_id')->label('Thuộc chi hội')->relationship('chapter', 'name')->searchable()->preload(),
                    Select::make('business_size')->label('Quy mô')->options([
                        'small' => 'Quy mô nhỏ',
                        'medium' => 'Quy mô vừa',
                        'large' => 'Quy mô lớn',
                    ]),
                    Select::make('industries')->label('Lĩnh vực hoạt động')->relationship('industries', 'name')->multiple()->searchable()->preload()->columnSpanFull(),
                    TextInput::make('phone')->label('Điện thoại')->tel()->maxLength(30),
                    TextInput::make('email')->label('Email')->email()->maxLength(255),
                    TextInput::make('website')->label('Website')->url()->maxLength(2048)->columnSpanFull(),
                    Textarea::make('address')->label('Địa chỉ')->rows(2)->columnSpanFull(),
                    TextInput::make('province')->label('Tỉnh / thành phố')->maxLength(100),
                    TextInput::make('district')->label('Quận / huyện')->maxLength(100),
                    Textarea::make('summary')->label('Mô tả ngắn')->rows(3)->columnSpanFull(),
                    RichEditor::make('description')->label('Giới thiệu chi tiết')->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Hiển thị danh bạ')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Toggle::make('is_featured')->label('Doanh nghiệp nổi bật')->default(false),
                ]),
        ]);
    }
}
