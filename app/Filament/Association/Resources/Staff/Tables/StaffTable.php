<?php

namespace App\Filament\Association\Resources\Staff\Tables;

use App\Filament\Association\Resources\Staff\StaffResource;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Cán bộ')->searchable()->sortable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('roles.name')->label('Vai trò')->badge()->formatStateUsing(fn (string $state) => StaffResource::ROLES[$state] ?? $state),
            TextColumn::make('managedChapters.name')->label('Chi hội phụ trách')->listWithLineBreaks()->placeholder('—'),
            IconColumn::make('is_active')->label('Đang hoạt động')->boolean(),
        ])->recordActions([EditAction::make()]);
    }
}
