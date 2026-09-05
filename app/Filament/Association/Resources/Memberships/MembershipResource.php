<?php

namespace App\Filament\Association\Resources\Memberships;

use App\Filament\Association\Resources\Memberships\Pages\ListMemberships;
use App\Filament\Association\Resources\Memberships\Pages\ViewMembership;
use App\Filament\Association\Resources\Memberships\Schemas\MembershipInfolist;
use App\Filament\Association\Resources\Memberships\Tables\MembershipsTable;
use App\Models\Business;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MembershipResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $slug = 'hoi-vien';

    protected static ?string $navigationLabel = 'Hồ sơ & hội viên';

    protected static ?string $modelLabel = 'hồ sơ hội viên';

    protected static ?string $pluralModelLabel = 'Hồ sơ hội viên doanh nghiệp';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Hội viên doanh nghiệp';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $user = auth('admin')->user();

        return parent::getEloquentQuery()->with(['chapter', 'industries', 'submittedBy', 'members', 'media'])
            ->when($user, fn (Builder $query) => $query->visibleToReviewer($user), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public static function canViewAny(): bool
    {
        return (bool) (auth('admin')->user()?->canRatifyMembership() || auth('admin')->user()?->canReviewAssociation() || auth('admin')->user()?->canReceiveChapter());
    }

    public static function canView($record): bool
    {
        return static::canViewAny() && static::getEloquentQuery()->whereKey($record->id)->exists();
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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MembershipInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListMemberships::route('/'), 'view' => ViewMembership::route('/{record}')];
    }
}
