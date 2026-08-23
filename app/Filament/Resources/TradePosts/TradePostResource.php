<?php

namespace App\Filament\Resources\TradePosts;

use App\Filament\Resources\TradePosts\Pages\CreateTradePost;
use App\Filament\Resources\TradePosts\Pages\EditTradePost;
use App\Filament\Resources\TradePosts\Pages\ListTradePosts;
use App\Models\TradePost;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
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

class TradePostResource extends Resource
{
    protected static ?string $model = TradePost::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-handshake';

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
                    Select::make('type')->label('Loại cơ hội')->options([
                        'request' => 'Tìm đối tác / nhu cầu hợp tác', 'offer' => 'Giới thiệu năng lực / sản phẩm', 'collaborate' => 'Mời hợp tác',
                    ])->required(),
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
                TextColumn::make('type')->label('Loại')->formatStateUsing(fn (?string $state): string => [
                    'request' => 'Tìm đối tác', 'offer' => 'Giới thiệu năng lực', 'collaborate' => 'Mời hợp tác',
                ][$state] ?? '—'),
                TextColumn::make('status')->label('Trạng thái')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('expires_at')->label('Hết hạn')->dateTime('d/m/Y')->placeholder('Không giới hạn')->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Trạng thái')->options([
                    'draft' => 'Bản nháp', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Yêu cầu bổ sung', 'closed' => 'Đã đóng',
                ]),
            ])
            ->recordActions([
                Action::make('submit')
                    ->label('Gửi duyệt')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->visible(fn (TradePost $record): bool => in_array($record->status, ['draft', 'rejected'], true))
                    ->requiresConfirmation()
                    ->action(fn (TradePost $record): bool => $record->update([
                        'status' => 'pending', 'review_note' => null, 'reviewed_by' => auth()->id(),
                    ])),
                Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (TradePost $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (TradePost $record): bool => $record->update([
                        'status' => 'approved', 'review_note' => null, 'approved_at' => now(), 'reviewed_by' => auth()->id(),
                    ])),
                Action::make('reject')
                    ->label('Yêu cầu bổ sung')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->visible(fn (TradePost $record): bool => $record->status === 'pending')
                    ->schema([
                        Textarea::make('review_note')->label('Nội dung cần bổ sung')->required()->rows(4),
                    ])
                    ->action(fn (TradePost $record, array $data): bool => $record->update([
                        'status' => 'rejected', 'review_note' => $data['review_note'], 'approved_at' => null, 'reviewed_by' => auth()->id(),
                    ])),
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
