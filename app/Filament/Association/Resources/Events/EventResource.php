<?php

namespace App\Filament\Association\Resources\Events;

use App\Filament\Association\Concerns\ManagesAssociationContent;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class EventResource extends Resource
{
    use ManagesAssociationContent;

    protected static ?string $model = Event::class;

    protected static ?string $slug = 'su-kien';

    protected static ?string $navigationLabel = 'Sự kiện & đăng ký';

    protected static ?string $modelLabel = 'sự kiện';

    protected static ?string $pluralModelLabel = 'sự kiện';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung Hội';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return Schemas\EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\RegistrationsRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListEvents::route('/'), 'create' => Pages\CreateEvent::route('/create'), 'edit' => Pages\EditEvent::route('/{record}/edit')];
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
