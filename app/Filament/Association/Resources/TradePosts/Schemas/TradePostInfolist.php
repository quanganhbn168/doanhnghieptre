<?php

namespace App\Filament\Association\Resources\TradePosts\Schemas;

use App\Models\TradePost;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TradePostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung tin')->columnSpanFull()->columns(2)->schema([
                TextEntry::make('business.name')->label('Doanh nghiệp')->placeholder('Chưa gắn doanh nghiệp'),
                TextEntry::make('type')->label('Nhu cầu')->formatStateUsing(TradePost::typeLabel(...)),
                TextEntry::make('title')->label('Tiêu đề')->columnSpanFull(),
                TextEntry::make('summary')->label('Tóm tắt')->columnSpanFull(),
                TextEntry::make('content')->label('Chi tiết')->formatStateUsing(fn ($state) => strip_tags($state ?? ''))->columnSpanFull(),
                TextEntry::make('industries.name')->label('Lĩnh vực')->badge(),
                TextEntry::make('budget_label')->label('Ngân sách / giá tham khảo')->placeholder('—'),
                TextEntry::make('location_label')->label('Khu vực')->placeholder('—'),
            ]),
            Section::make('Liên hệ và xét duyệt')->columnSpanFull()->columns(2)->schema([
                TextEntry::make('contact_name')->label('Người liên hệ'),
                TextEntry::make('contact_phone')->label('Điện thoại'),
                TextEntry::make('contact_email')->label('Email')->placeholder('—'),
                TextEntry::make('expires_at')->label('Hết hạn')->dateTime('d/m/Y H:i')->placeholder('Không giới hạn'),
                TextEntry::make('status')->label('Trạng thái')->formatStateUsing(fn ($state) => TradePost::STATUS_OPTIONS[$state] ?? $state)->badge(),
                TextEntry::make('approved_at')->label('Ngày duyệt')->dateTime('d/m/Y H:i')->placeholder('Chưa duyệt'),
                TextEntry::make('review_note')->label('Yêu cầu bổ sung')->placeholder('Không có')->columnSpanFull(),
            ]),
        ]);
    }
}
