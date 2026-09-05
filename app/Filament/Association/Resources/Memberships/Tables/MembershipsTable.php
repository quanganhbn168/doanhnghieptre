<?php

namespace App\Filament\Association\Resources\Memberships\Tables;

use App\Models\Business;
use App\Models\BusinessChapter;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MembershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Doanh nghiệp')->searchable()->sortable()->description(fn (Business $record) => $record->tax_code)->wrap()->width('30%'),
            TextColumn::make('membership_code')->label('Mã hội viên')->searchable()->placeholder('Chưa cấp'),
            TextColumn::make('representative_name')->label('Người đại diện')->state(fn (Business $record) => $record->representativeDisplayName())->searchable()->placeholder('—'),
            TextColumn::make('chapter.name')->label('Chi hội')->placeholder('Chưa phân công'),
            TextColumn::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)
                ->color(fn (string $state) => match ($state) {
                    'approved' => 'success', 'chapter_pending', 'board_pending' => 'info', 'pending', 'changes_requested' => 'warning', 'rejected' => 'danger', default => 'gray'
                }),
            TextColumn::make('created_at')->label('Ngày nộp')->date('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('status')->label('Trạng thái')->options(Business::STATUS_LABELS),
            SelectFilter::make('business_chapter_id')->label('Chi hội')->options(fn () => BusinessChapter::query()
                ->when(! auth('admin')->user()->canReviewAssociation() && ! auth('admin')->user()->canRatifyMembership(), fn ($query) => $query->whereIn('id', auth('admin')->user()->managedChapters()->select('business_chapters.id')))
                ->orderBy('name')->pluck('name', 'id')),
        ])->recordActions([ViewAction::make()->label('Xem hồ sơ')]);
    }
}
