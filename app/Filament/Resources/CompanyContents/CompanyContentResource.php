<?php

namespace App\Filament\Resources\CompanyContents;

use App\Filament\Resources\CompanyContents\Pages\CreateCompanyContent;
use App\Filament\Resources\CompanyContents\Pages\EditCompanyContent;
use App\Filament\Resources\CompanyContents\Pages\ListCompanyContents;
use App\Filament\Resources\CompanyContents\Schemas\CompanyContentForm;
use App\Filament\Resources\CompanyContents\Tables\CompanyContentsTable;
use App\Models\CompanyContent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CompanyContentResource extends Resource
{
    protected static ?string $model = CompanyContent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Giới thiệu về Hội';

    protected static ?string $modelLabel = 'nội dung giới thiệu';

    protected static ?string $pluralModelLabel = 'nội dung giới thiệu';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung website';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return CompanyContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyContentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyContents::route('/'),
            'create' => CreateCompanyContent::route('/create'),
            'edit' => EditCompanyContent::route('/{record}/edit'),
        ];
    }
}
