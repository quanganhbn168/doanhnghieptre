<?php

namespace App\Filament\Resources\TradePosts;

use App\Filament\Association\Actions\TradeReviewActions;
use App\Filament\Resources\TradePosts\Pages\CreateTradePost;
use App\Filament\Resources\TradePosts\Pages\EditTradePost;
use App\Filament\Resources\TradePosts\Pages\ListTradePosts;
use App\Models\TradePost;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TradePostResource extends Resource
{
    protected static ?string $model = TradePost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Cơ hội giao thương';

    protected static ?string $modelLabel = 'cơ hội giao thương';

    protected static ?string $pluralModelLabel = 'cơ hội giao thương';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin cơ hội')
                ->schema([
                    Select::make('member_id')->label('Hội viên đăng tin')->relationship('member', 'full_name')->searchable()->preload()->required(),
                    Select::make('business_id')->label('Doanh nghiệp')->relationship('business', 'name')->searchable()->preload(),
                    Select::make('type')->label('Loại cơ hội')->options(TradePost::TYPE_OPTIONS)->required(),
                    TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('summary')->label('Tóm tắt')->rows(3)->columnSpanFull(),
                    RichEditor::make('content')->label('Nội dung chi tiết')->columnSpanFull(),
                    Select::make('industries')->label('Lĩnh vực')->relationship('industries', 'name')->multiple()->searchable()->preload()->columnSpanFull(),
                    TextInput::make('budget_label')->label('Quy mô / ngân sách tham chiếu')->maxLength(255),
                    TextInput::make('location_label')->label('Khu vực hợp tác')->maxLength(255),
                ])
                ->columns(2),
            Section::make('Liên hệ và hiệu lực')
                ->schema([
                    TextInput::make('contact_name')->label('Người liên hệ')->maxLength(255),
                    TextInput::make('contact_phone')->label('Số điện thoại')->tel()->maxLength(30),
                    TextInput::make('contact_email')->label('Email')->email()->maxLength(255),
                    DateTimePicker::make('expires_at')->label('Hiệu lực đến')->seconds(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Cơ hội')->searchable()->sortable()->limit(55),
                TextColumn::make('member.full_name')->label('Hội viên')->placeholder('—')->searchable(),
                TextColumn::make('business.name')->label('Doanh nghiệp')->placeholder('—')->toggleable(),
                TextColumn::make('type')->label('Loại')->formatStateUsing(TradePost::typeLabel(...))->badge(),
                TextColumn::make('status')->label('Trạng thái')->formatStateUsing(fn ($state) => TradePost::STATUS_OPTIONS[$state] ?? $state)->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('expires_at')->label('Hết hạn')->dateTime('d/m/Y')->placeholder('Không giới hạn')->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('type')->label('Loại cơ hội')->options(TradePost::TYPE_OPTIONS),
                SelectFilter::make('status')->label('Trạng thái')->options([
                    'draft' => 'Bản nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Yêu cầu bổ sung', 'closed' => 'Đã đóng',
                ]),
            ])
            ->recordActions([
                ...TradeReviewActions::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTradePosts::route('/'),
            'create' => CreateTradePost::route('/create'),
            'edit' => EditTradePost::route('/{record}/edit'),
        ];
    }
}
