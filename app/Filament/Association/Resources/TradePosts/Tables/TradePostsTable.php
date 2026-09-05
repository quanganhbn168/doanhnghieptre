<?php

namespace App\Filament\Association\Resources\TradePosts\Tables;

use App\Models\TradePost;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TradePostsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Tin đăng')->searchable()->wrap()->limit(80),
            TextColumn::make('business.name')->label('Doanh nghiệp')->searchable()->wrap(),
            TextColumn::make('type')->label('Nhu cầu')->formatStateUsing(TradePost::typeLabel(...))->wrap(),
            TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn ($state) => TradePost::STATUS_OPTIONS[$state] ?? $state)->badge()->color(fn ($state) => match ($state) {
                'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray'
            }),
            TextColumn::make('expires_at')->label('Hết hạn')->date('d/m/Y')->placeholder('Không giới hạn'),
            TextColumn::make('updated_at')->label('Cập nhật')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('status')->label('Trạng thái')->options(TradePost::STATUS_OPTIONS),
            SelectFilter::make('type')->label('Nhu cầu')->options(TradePost::TYPE_OPTIONS),
        ])->defaultSort('updated_at', 'desc')->recordActions([ViewAction::make()->label('Xem và duyệt')]);
    }
}
