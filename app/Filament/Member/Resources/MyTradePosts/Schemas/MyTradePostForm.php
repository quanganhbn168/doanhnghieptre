<?php

namespace App\Filament\Member\Resources\MyTradePosts\Schemas;

use App\Models\Business;
use App\Models\TradePost;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyTradePostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tin giao thương')->columnSpanFull()
                ->description('Bài đăng sẽ hiển thị công khai sau khi được Hội duyệt.')
                ->schema([
                    Select::make('business_id')
                        ->label('Doanh nghiệp đăng tin')
                        ->options(fn (): array => Business::query()
                            ->representedBy(auth('web')->user())
                            ->where('status', 'approved')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->required()
                        ->searchable(),
                    Select::make('type')->label('Bạn muốn đăng gì?')->options(TradePost::TYPE_OPTIONS)->required(),
                    TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('summary')->label('Tóm tắt nhu cầu / dịch vụ cung cấp')->required()->maxLength(2000)->rows(3)->columnSpanFull(),
                    Textarea::make('content')->label('Nội dung chi tiết')->rows(8)->maxLength(20000)->columnSpanFull(),
                    Select::make('industries')->label('Lĩnh vực liên quan')->relationship('industries', 'name')->multiple()->searchable()->preload()->columnSpanFull(),
                    TextInput::make('budget_label')->label('Quy mô / ngân sách tham chiếu')->maxLength(255),
                    TextInput::make('location_label')->label('Khu vực hợp tác')->maxLength(255),
                ])
                ->columns(2),
            Section::make('Thông tin liên hệ')->columnSpanFull()
                ->schema([
                    TextInput::make('contact_name')->label('Người liên hệ')->default(fn () => auth('web')->user()?->name)->required()->maxLength(255),
                    TextInput::make('contact_phone')->label('Số điện thoại')->default(fn () => auth('web')->user()?->phone)->required()->tel()->maxLength(30),
                    TextInput::make('contact_email')->label('Email')->default(fn () => auth('web')->user()?->email)->email()->maxLength(255),
                    DateTimePicker::make('expires_at')->label('Hiệu lực đến')->seconds(false)->after('now')->helperText('Để trống nếu chưa xác định. Có thể đóng tin bất cứ lúc nào.'),
                ])
                ->columns(2),
        ]);
    }
}
