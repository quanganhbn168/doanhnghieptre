<?php

namespace App\Filament\Resources\AccountApprovals;

use App\Actions\AccountApprovals\ApproveAccount;
use App\Filament\Resources\AccountApprovals\Pages\ListAccountApprovals;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AccountApprovalResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Tài khoản chờ duyệt';

    protected static ?string $modelLabel = 'tài khoản đăng ký';

    protected static ?string $pluralModelLabel = 'tài khoản đăng ký';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth('admin')->user();

        return $user instanceof User && $user->hasRole(['super_admin', 'admin'], 'admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = User::query()->where('approval_status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Họ và tên')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('phone')->label('Số điện thoại')->placeholder('—')->toggleable(),
                TextColumn::make('approval_status')->label('Trạng thái')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                    default => 'Đã khóa',
                })->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->label('Đăng ký lúc')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('accountApprovedBy.name')->label('Người duyệt')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('approval_status')->label('Trạng thái')->options([
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                    'suspended' => 'Đã khóa',
                ])->default('pending'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Duyệt tài khoản')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Tài khoản sẽ được phép đăng nhập để nộp hồ sơ doanh nghiệp.')
                    ->action(fn (User $record): mixed => app(ApproveAccount::class)($record, auth('admin')->id())),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountApprovals::route('/'),
        ];
    }
}
