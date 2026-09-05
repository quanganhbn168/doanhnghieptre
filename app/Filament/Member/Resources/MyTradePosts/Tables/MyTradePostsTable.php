<?php

namespace App\Filament\Member\Resources\MyTradePosts\Tables;

use App\Models\TradePost;
use App\Services\TradePostService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MyTradePostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Cơ hội')->searchable()->sortable()->limit(60),
                TextColumn::make('business.name')->label('Doanh nghiệp')->placeholder('—')->toggleable(),
                TextColumn::make('type')->label('Loại')->formatStateUsing(TradePost::typeLabel(...))->badge(),
                TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (string $state) => TradePost::STATUS_OPTIONS[$state] ?? $state)->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('review_note')->label('Hội yêu cầu bổ sung')->wrap()->placeholder('—')->limit(120)->tooltip(fn (TradePost $record) => $record->review_note),
                TextColumn::make('expires_at')->label('Hết hạn')->dateTime('d/m/Y')->placeholder('Không giới hạn'),
                TextColumn::make('updated_at')->label('Cập nhật')->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')->filters([SelectFilter::make('status')->label('Trạng thái')->options(TradePost::STATUS_OPTIONS)])
            ->recordActions([
                EditAction::make()->label('Sửa / gửi duyệt'),
                Action::make('close')->label('Đóng tin')->color('gray')->requiresConfirmation()->visible(fn (TradePost $record) => $record->status !== 'closed')->action(fn (TradePost $record) => app(TradePostService::class)->close($record, auth('web')->user())),
            ]);
    }
}
