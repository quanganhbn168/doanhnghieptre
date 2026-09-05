<?php

namespace App\Filament\Resources\Businesses\Tables;

use App\Filament\Association\Actions\MembershipReviewActions;
use App\Models\Business;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->getStateUsing(fn (Business $record): ?string => $record->getFirstMediaUrl('logo') ?: null)
                    ->square(),
                TextColumn::make('name')->label('Doanh nghiệp')->searchable()->sortable()->description(fn (Business $record): ?string => $record->legal_name),
                TextColumn::make('submittedBy.name')->label('Người nộp')->placeholder('Quản trị viên tạo')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')->label('Nhóm')->placeholder('—')->toggleable(),
                TextColumn::make('chapter.name')->label('Chi hội')->placeholder('—')->toggleable(),
                TextColumn::make('business_size')->label('Quy mô')->formatStateUsing(fn (?string $state): string => [
                    'small' => 'Nhỏ', 'medium' => 'Vừa', 'large' => 'Lớn',
                ][$state] ?? '—')->toggleable(),
                TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                IconColumn::make('signed_membership_application')
                    ->label('Đơn đã ký')
                    ->getStateUsing(fn (Business $record): bool => $record->hasMedia('signed_membership_application'))
                    ->boolean(),
                IconColumn::make('is_featured')->label('Nổi bật')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options([
                    ...Business::STATUS_LABELS,
                ]),
                SelectFilter::make('business_size')->label('Quy mô')->options([
                    'small' => 'Nhỏ', 'medium' => 'Vừa', 'large' => 'Lớn',
                ]),
                SelectFilter::make('business_chapter_id')->label('Chi hội')->relationship('chapter', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                ...MembershipReviewActions::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
