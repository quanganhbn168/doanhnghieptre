<?php

namespace App\Filament\Resources\Businesses\Tables;

use App\Models\Business;
use App\Services\BusinessApprovalService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;

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
                TextColumn::make('status')->label('Trạng thái')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                IconColumn::make('is_featured')->label('Nổi bật')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options([
                    'draft' => 'Bản nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối',
                ]),
                SelectFilter::make('business_size')->label('Quy mô')->options([
                    'small' => 'Nhỏ', 'medium' => 'Vừa', 'large' => 'Lớn',
                ]),
                SelectFilter::make('business_chapter_id')->label('Chi hội')->relationship('chapter', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('submit')
                    ->label('Gửi duyệt')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->visible(fn (Business $record): bool => in_array($record->status, ['draft', 'rejected'], true))
                    ->requiresConfirmation()
                    ->action(fn (Business $record): mixed => $record->transitionTo('pending', auth()->id(), 'Hồ sơ được gửi duyệt từ quản trị.')),
                Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Business $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (Business $record): mixed => app(BusinessApprovalService::class)->approve($record, auth()->id())),
                Action::make('reject')
                    ->label('Yêu cầu bổ sung')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->visible(fn (Business $record): bool => $record->status === 'pending')
                    ->schema([
                        Textarea::make('reason')->label('Nội dung cần bổ sung')->required()->rows(4),
                    ])
                    ->action(fn (Business $record, array $data): mixed => $record->transitionTo('rejected', auth()->id(), $data['reason'])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
