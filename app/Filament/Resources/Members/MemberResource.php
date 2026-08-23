<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Hội viên';

    protected static ?string $modelLabel = 'hội viên';

    protected static ?string $pluralModelLabel = 'hội viên';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hồ sơ hội viên')
                ->schema([
                    Select::make('user_id')->label('Tài khoản đăng nhập')->relationship('user', 'email')->searchable()->preload(),
                    TextInput::make('member_code')->label('Mã hội viên')->required()->maxLength(80)->unique(ignoreRecord: true),
                    TextInput::make('full_name')->label('Họ và tên')->required()->maxLength(255),
                    TextInput::make('email')->label('Email')->email()->maxLength(255),
                    TextInput::make('phone')->label('Số điện thoại')->tel()->maxLength(30),
                    Select::make('gender')->label('Giới tính')->options(['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác']),
                    DatePicker::make('date_of_birth')->label('Ngày sinh'),
                    TextInput::make('province')->label('Tỉnh / thành phố')->maxLength(100),
                    TextInput::make('district')->label('Quận / huyện')->maxLength(100),
                    Textarea::make('address')->label('Địa chỉ')->rows(2)->columnSpanFull(),
                    Textarea::make('introduction')->label('Giới thiệu')->rows(4)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Xét duyệt')
                ->schema([
                    Select::make('status')->label('Trạng thái')->options([
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'suspended' => 'Tạm dừng',
                        'rejected' => 'Từ chối',
                    ])->default('pending')->required(),
                    DatePicker::make('joined_at')->label('Ngày tham gia'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_code')->label('Mã hội viên')->searchable()->sortable(),
                TextColumn::make('full_name')->label('Hội viên')->searchable()->sortable()->description(fn (Member $record): ?string => $record->email),
                TextColumn::make('phone')->label('Điện thoại')->placeholder('—')->toggleable(),
                TextColumn::make('user.email')->label('Tài khoản')->placeholder('Chưa liên kết')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Trạng thái')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options([
                    'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'suspended' => 'Tạm dừng', 'rejected' => 'Từ chối',
                ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
