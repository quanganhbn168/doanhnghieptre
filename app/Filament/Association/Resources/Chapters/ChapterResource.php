<?php

namespace App\Filament\Association\Resources\Chapters;

use App\Filament\Association\Resources\Chapters\Pages\CreateChapter;
use App\Filament\Association\Resources\Chapters\Pages\EditChapter;
use App\Filament\Association\Resources\Chapters\Pages\ListChapters;
use App\Filament\Association\Resources\Chapters\Schemas\ChapterForm;
use App\Filament\Association\Resources\Chapters\Tables\ChaptersTable;
use App\Models\BusinessChapter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ChapterResource extends Resource
{
    protected static ?string $model = BusinessChapter::class;

    protected static ?string $slug = 'chi-hoi';

    protected static ?string $modelLabel = 'chi hội';

    protected static ?string $pluralModelLabel = 'chi hội';

    protected static ?string $navigationLabel = 'Chi hội trực thuộc';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = 'Tổ chức Hội';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['businesses' => fn (Builder $query) => $query->where('status', 'approved')])
            ->when(! auth('admin')->user()?->canManageAssociation(), fn (Builder $query) => $query->whereIn('id', auth('admin')->user()?->managedChapters()->select('business_chapters.id') ?? []));
    }

    public static function canViewAny(): bool
    {
        return (bool) (auth('admin')->user()?->canManageAssociation() || auth('admin')->user()?->canReceiveChapter());
    }

    public static function canCreate(): bool
    {
        return (bool) auth('admin')->user()?->canManageAssociation();
    }

    public static function canEdit($record): bool
    {
        return static::canCreate();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ChapterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChaptersTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListChapters::route('/'), 'create' => CreateChapter::route('/create'), 'edit' => EditChapter::route('/{record}/edit')];
    }
}
