<?php

namespace App\Filament\Association\Resources\Staff\Schemas;

use App\Filament\Association\Resources\Staff\StaffResource;
use App\Models\BusinessChapter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin cán bộ')->schema([
                TextInput::make('name')->label('Họ và tên')->required()->maxLength(255),
                TextInput::make('email')->label('Email đăng nhập')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('phone')->label('Điện thoại')->tel()->maxLength(30),
                TextInput::make('password')->label('Mật khẩu')->password()->revealable()->minLength(8)->maxLength(255)
                    ->required(fn (string $operation) => $operation === 'create')->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Khi chỉnh sửa, để trống nếu giữ mật khẩu hiện tại.'),
                Toggle::make('is_active')->label('Cho phép đăng nhập')->default(true),
            ])->columns(2)->columnSpanFull(),
            Section::make('Quyền và phạm vi xử lý')->schema([
                Select::make('staff_roles')->label('Vai trò')->options(StaffResource::ROLES)->multiple()->required()->minItems(1)->in(array_keys(StaffResource::ROLES))->live(),
                Select::make('managedChapters')->label('Chi hội được phân công')->relationship('managedChapters', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                    ->multiple()->searchable()->preload()->in(fn () => BusinessChapter::query()->where('is_active', true)->pluck('id')->all())
                    ->required(fn (Get $get) => in_array('chapter_manager', $get('staff_roles') ?? [], true)),
            ])->description('Văn phòng kiểm tra hồ sơ; Chi hội trưởng thẩm định trong Chi hội được phân công; Trưởng ban Hội viên chuẩn y; Ban Truyền thông duyệt nội dung.')->columns(2)->columnSpanFull(),
        ]);
    }
}
