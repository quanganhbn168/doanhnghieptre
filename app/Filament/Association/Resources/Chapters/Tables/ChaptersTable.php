<?php

namespace App\Filament\Association\Resources\Chapters\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChaptersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Chi hội')->searchable()->sortable(),
            TextColumn::make('businesses_count')->label('Doanh nghiệp hội viên')->sortable(),
            TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
            IconColumn::make('is_active')->label('Hoạt động')->boolean(),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()]);
    }
}
