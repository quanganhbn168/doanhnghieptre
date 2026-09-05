<?php

namespace App\Filament\Association\Resources\MemberPosts\Tables;

use App\Models\Post;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Tiêu đề')->state(fn (Post $record) => $record->getTranslation('name', 'vi'))->searchable()->wrap(),
            TextColumn::make('business.name')->label('Doanh nghiệp')->wrap(),
            TextColumn::make('review_status')->label('Trạng thái')->badge()->formatStateUsing(fn ($state) => Post::REVIEW_STATUSES[$state] ?? $state)->color(fn ($state) => match ($state) {
                'approved' => 'success', 'pending' => 'warning', default => 'danger'
            }),
            TextColumn::make('review_note')->label('Phản hồi của Hội')->wrap()->placeholder('—'),
            TextColumn::make('updated_at')->label('Cập nhật')->dateTime('d/m/Y H:i')->sortable(),
        ])->defaultSort('updated_at', 'desc')->filters([SelectFilter::make('review_status')->label('Trạng thái')->options(Post::REVIEW_STATUSES)])->recordActions([ViewAction::make()->label('Xem và duyệt')]);
    }
}
