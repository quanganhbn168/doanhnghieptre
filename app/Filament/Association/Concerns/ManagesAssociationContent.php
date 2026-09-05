<?php

namespace App\Filament\Association\Concerns;

trait ManagesAssociationContent
{
    public static function canViewAny(): bool
    {
        return (bool) auth('admin')->user()?->canModerateContent();
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function canReorder(): bool
    {
        return static::canViewAny();
    }
}
