<?php

namespace App\Filament\Resources\ProfessionalGroups;

use App\Filament\Resources\ProfessionalGroups\Pages\CreateProfessionalGroup;
use App\Filament\Resources\ProfessionalGroups\Pages\EditProfessionalGroup;
use App\Filament\Resources\ProfessionalGroups\Pages\ListProfessionalGroups;
use App\Models\Industry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProfessionalGroupResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Nhóm nghề nghiệp';

    protected static ?string $modelLabel = 'nhóm nghề nghiệp';

    protected static ?string $pluralModelLabel = 'nhóm nghề nghiệp';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->memberGroups();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nhóm nghề nghiệp')
                ->description('Hội đang có 17 nhóm nghề nghiệp và 01 nhóm Khác.')
                ->schema([
                    TextInput::make('name')->label('Tên nhóm')->required()->maxLength(255),
                    TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->default(fn (): int => ((int) Industry::query()->memberGroups()->max('sort_order')) + 10)->required(),
                    Toggle::make('is_active')->label('Đang sử dụng')->default(true),
                    Textarea::make('description')->label('Mô tả')->rows(3)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nhóm nghề nghiệp')->searchable()->sortable(),
                TextColumn::make('description')->label('Mô tả')->limit(80)->toggleable(),
                TextColumn::make('sort_order')->label('Thứ tự')->sortable(),
                ToggleColumn::make('is_active')->label('Sử dụng'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfessionalGroups::route('/'),
            'create' => CreateProfessionalGroup::route('/create'),
            'edit' => EditProfessionalGroup::route('/{record}/edit'),
        ];
    }
}
