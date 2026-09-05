<?php

namespace App\Filament\Association\Resources\Events\RelationManagers;

use App\Models\EventRegistration;
use App\Services\EventRegistrationService;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RegistrationsRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Người đăng ký tham dự';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) auth('admin')->user()?->canManageAssociation();
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('full_name')->label('Họ tên')->searchable(),
            TextColumn::make('phone')->label('Điện thoại')->searchable(),
            TextColumn::make('email')->label('Email')->searchable()->toggleable(),
            TextColumn::make('guest_count')->label('Đi cùng'),
            TextColumn::make('status')->label('Đăng ký')->formatStateUsing(fn ($state) => $state === 'registered' ? 'Đã đăng ký' : 'Đã hủy')->badge(),
            TextColumn::make('attendance_status')->label('Tham dự')->formatStateUsing(fn ($state) => $state === 'attended' ? 'Đã điểm danh' : 'Chưa điểm danh')->badge(),
            TextColumn::make('registered_at')->label('Ngày đăng ký')->dateTime('d/m/Y H:i')->sortable(),
        ])->filters([SelectFilter::make('status')->label('Đăng ký')->options(['registered' => 'Đã đăng ký', 'cancelled' => 'Đã hủy'])])->defaultSort('registered_at', 'desc')->recordActions([
            Action::make('check_in')->label('Điểm danh')->visible(fn (EventRegistration $record) => $record->status === 'registered' && $record->attendance_status !== 'attended')->requiresConfirmation()->action(fn (EventRegistration $record) => app(EventRegistrationService::class)->updateAttendance($record, auth('admin')->user(), 'check_in')),
            Action::make('cancel')->label('Hủy đăng ký')->color('danger')->visible(fn (EventRegistration $record) => $record->status === 'registered')->requiresConfirmation()->action(fn (EventRegistration $record) => app(EventRegistrationService::class)->updateAttendance($record, auth('admin')->user(), 'cancel')),
        ]);
    }
}
