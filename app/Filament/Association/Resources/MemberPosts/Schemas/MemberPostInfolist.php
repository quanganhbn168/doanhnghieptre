<?php

namespace App\Filament\Association\Resources\MemberPosts\Schemas;

use App\Models\Post;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberPostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Bài viết doanh nghiệp')->schema([
                TextEntry::make('business.name')->label('Doanh nghiệp'),
                TextEntry::make('review_status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Post::REVIEW_STATUSES[$state] ?? $state),
                TextEntry::make('category.name')->label('Chuyên mục'),
                TextEntry::make('review_note')->label('Nội dung cần chỉnh sửa')->placeholder('Không có')->columnSpanFull(),
                TextEntry::make('title')->label('Tiêu đề')->state(fn (Post $record) => $record->getTranslation('name', 'vi'))->columnSpanFull(),
                TextEntry::make('excerpt')->label('Giới thiệu ngắn')->state(fn (Post $record) => $record->getTranslation('summary', 'vi'))->columnSpanFull(),
                ImageEntry::make('cover')->label('Ảnh đại diện')->state(fn (Post $record) => $record->getFirstMediaUrl('post_image'))->visible(fn (Post $record) => $record->hasMedia('post_image'))->imageHeight(240)->columnSpanFull(),
                TextEntry::make('body')->label('Nội dung')->state(fn (Post $record) => $record->getTranslation('content', 'vi'))->html()->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
}
