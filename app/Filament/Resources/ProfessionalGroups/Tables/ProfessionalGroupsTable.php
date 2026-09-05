<?php

namespace App\Filament\Resources\ProfessionalGroups\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProfessionalGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Khối ngành nghề')->searchable()->sortable()->wrap(),
            TextColumn::make('business_count')->label('Doanh nghiệp hội viên')->numeric()->sortable(),
            TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
            ToggleColumn::make('is_active')->label('Sử dụng'),
            ToggleColumn::make('show_on_home')->label('Trang chủ'),
        ])->defaultSort('sort_order')->reorderable('sort_order')->paginated([25, 50, 100])->defaultPaginationPageOption(25)
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
