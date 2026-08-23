<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $recordTitleAttribute = 'full_name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_code')->label('Mã hội viên')->searchable(),
                TextColumn::make('full_name')->label('Hội viên')->searchable()->description(fn ($record): ?string => $record->email),
                TextColumn::make('pivot.role')->label('Vai trò')->formatStateUsing(fn (?string $state): string => [
                    'representative' => 'Đại diện',
                    'owner' => 'Chủ sở hữu',
                    'manager' => 'Quản lý',
                    'staff' => 'Nhân sự',
                ][$state] ?? ($state ?: '—')),
                TextColumn::make('pivot.job_title')->label('Chức danh')->placeholder('—'),
                IconColumn::make('pivot.is_primary')->label('Đại diện chính')->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Liên kết hội viên')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['full_name', 'member_code', 'email'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect()->label('Hội viên')->required(),
                        Select::make('role')->label('Vai trò')->options([
                            'representative' => 'Đại diện',
                            'owner' => 'Chủ sở hữu',
                            'manager' => 'Quản lý',
                            'staff' => 'Nhân sự',
                        ])->default('representative')->required(),
                        TextInput::make('job_title')->label('Chức danh tại doanh nghiệp')->maxLength(160),
                        Select::make('status')->label('Trạng thái liên kết')->options([
                            'active' => 'Đang hiệu lực',
                            'inactive' => 'Tạm dừng',
                        ])->default('active')->required(),
                        DatePicker::make('started_at')->label('Từ ngày'),
                        Select::make('is_primary')->label('Đại diện chính')->options([1 => 'Có', 0 => 'Không'])->default(0)->required(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make()->label('Gỡ liên kết'),
            ]);
    }
}
