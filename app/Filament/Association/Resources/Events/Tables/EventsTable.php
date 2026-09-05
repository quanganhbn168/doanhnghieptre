<?php

namespace App\Filament\Association\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Sự kiện')->searchable()->wrap(),
            TextColumn::make('starts_at')->label('Bắt đầu')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('venue_name')->label('Địa điểm')->wrap(),
            TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn ($state) => Event::STATUS_OPTIONS[$state] ?? $state)->badge()->color(fn ($state) => match ($state) {
                'published' => 'success', 'cancelled' => 'danger', default => 'gray'
            }),
            TextColumn::make('registrations_count')->label('Lượt đăng ký')->counts(['registrations' => fn ($query) => $query->where('status', 'registered')]),
        ])->defaultSort('starts_at', 'desc')->filters([SelectFilter::make('status')->label('Trạng thái')->options(Event::STATUS_OPTIONS)])
            ->recordActions([EditAction::make()->label('Quản lý'), Action::make('preview')->label('Xem trang')->url(fn (Event $record) => route('events.show', $record->slug))->openUrlInNewTab()->visible(fn (Event $record) => Event::query()->publiclyVisible()->whereKey($record)->exists())]);
    }
}
