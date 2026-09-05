<?php

namespace App\Filament\Member\Resources\MyBusinesses\Tables;

use App\Models\Business;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MyBusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->label('Logo')->getStateUsing(fn (Business $record): ?string => $record->getFirstMediaUrl('logo') ?: null)->square(),
                TextColumn::make('name')->label('Doanh nghiệp')->searchable()->sortable()->description(fn (Business $record): ?string => $record->legal_name),
                TextColumn::make('business_size')->label('Quy mô')->formatStateUsing(fn (?string $state): string => [
                    'small' => 'Nhỏ', 'medium' => 'Vừa', 'large' => 'Lớn',
                ][$state] ?? '—'),
                TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('updated_at')->label('Cập nhật')->since()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Cập nhật hồ sơ'),
            ]);
    }
}
