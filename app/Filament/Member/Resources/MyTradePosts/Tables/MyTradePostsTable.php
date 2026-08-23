<?php

namespace App\Filament\Member\Resources\MyTradePosts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MyTradePostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Cơ hội')->searchable()->sortable()->limit(60),
                TextColumn::make('business.name')->label('Doanh nghiệp')->placeholder('—')->toggleable(),
                TextColumn::make('type')->label('Loại')->formatStateUsing(fn (?string $state): string => [
                    'request' => 'Tìm đối tác', 'offer' => 'Giới thiệu năng lực', 'collaborate' => 'Mời hợp tác',
                ][$state] ?? '—'),
                TextColumn::make('status')->label('Trạng thái')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('updated_at')->label('Cập nhật')->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Cập nhật'),
            ]);
    }
}
