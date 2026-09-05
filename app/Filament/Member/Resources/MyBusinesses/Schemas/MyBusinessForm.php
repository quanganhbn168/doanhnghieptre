<?php

namespace App\Filament\Member\Resources\MyBusinesses\Schemas;

use App\Filament\Resources\ProfessionalGroups\Schemas\ProfessionalGroupSelect;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyBusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nhận diện doanh nghiệp')
                ->schema([
                    FileUpload::make('logo')->label('Logo doanh nghiệp')->image()->storeFiles(false)->maxSize(10240),
                ]),
            Section::make('Thông tin doanh nghiệp')
                ->description('Mọi thay đổi sẽ được Hội kiểm tra lại trước khi hiển thị công khai.')
                ->schema([
                    TextInput::make('name')->label('Tên giao dịch')->required()->maxLength(255),
                    TextInput::make('legal_name')->label('Tên pháp lý')->maxLength(255),
                    TextInput::make('tax_code')->label('Mã số thuế')->required()->maxLength(32)->unique(ignoreRecord: true),
                    Select::make('business_type')->label('Loại hình')->options([
                        'limited' => 'Công ty TNHH', 'joint_stock' => 'Công ty cổ phần', 'private' => 'Doanh nghiệp tư nhân',
                        'household' => 'Hộ kinh doanh', 'cooperative' => 'Hợp tác xã', 'other' => 'Loại hình khác',
                    ])->required(),
                    Select::make('business_category_id')->label('Nhóm doanh nghiệp')->relationship('category', 'name')->searchable()->preload(),
                    Select::make('business_size')->label('Quy mô')->options([
                        'small' => 'Quy mô nhỏ', 'medium' => 'Quy mô vừa', 'large' => 'Quy mô lớn',
                    ])->required(),
                    ProfessionalGroupSelect::make()->columnSpanFull(),
                    TextInput::make('representative_job_title')->label('Chức danh người đại diện')->maxLength(160),
                    TextInput::make('phone')->label('Điện thoại')->tel()->required()->maxLength(30),
                    TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                    TextInput::make('website')->label('Website')->maxLength(253)->dehydrateStateUsing(static fn (?string $state): ?string => $state ? rtrim((string) preg_replace('#^https?://#i', '', trim($state)), '/') : null)->columnSpanFull(),
                    Textarea::make('address')->label('Địa chỉ')->required()->rows(2)->columnSpanFull(),
                    TextInput::make('province')->label('Tỉnh / thành phố')->required()->maxLength(100),
                    TextInput::make('district')->label('Quận / huyện')->maxLength(100),
                    Textarea::make('summary')->label('Mô tả ngắn')->required()->rows(3)->columnSpanFull(),
                    RichEditor::make('description')->label('Giới thiệu chi tiết')->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
