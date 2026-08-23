<?php

namespace App\Filament\Member\Resources\MyTradePosts\Schemas;

use App\Models\Business;
use App\Models\TradePost;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyTradePostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung cơ hội giao thương')
                ->description('Bài đăng sẽ hiển thị công khai sau khi được Hội duyệt.')
                ->schema([
                    Select::make('business_id')
                        ->label('Doanh nghiệp đăng tin')
                        ->options(fn (): array => Business::query()
                            ->whereHas('members', fn ($members) => $members->whereKey(auth('web')->user()?->member?->id))
                            ->where('status', 'approved')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->required()
                        ->searchable(),
                    Select::make('type')->label('Loại cơ hội')->options(TradePost::TYPE_OPTIONS)->required(),
                    TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('summary')->label('Tóm tắt')->required()->rows(3)->columnSpanFull(),
                    RichEditor::make('content')->label('Nội dung chi tiết')->columnSpanFull(),
                    Select::make('industries')->label('Lĩnh vực liên quan')->relationship('industries', 'name')->multiple()->searchable()->preload()->columnSpanFull(),
                    TextInput::make('budget_label')->label('Quy mô / ngân sách tham chiếu')->maxLength(255),
                    TextInput::make('location_label')->label('Khu vực hợp tác')->maxLength(255),
                ])
                ->columns(2),
            Section::make('Thông tin liên hệ')
                ->schema([
                    TextInput::make('contact_name')->label('Người liên hệ')->required()->maxLength(255),
                    TextInput::make('contact_phone')->label('Số điện thoại')->required()->tel()->maxLength(30),
                    TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                    DateTimePicker::make('expires_at')->label('Hiệu lực đến')->seconds(false),
                ])
                ->columns(2),
        ]);
    }
}
