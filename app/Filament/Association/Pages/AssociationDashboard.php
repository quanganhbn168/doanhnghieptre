<?php

namespace App\Filament\Association\Pages;

use App\Filament\Association\Widgets\ContentStats;
use App\Filament\Association\Widgets\MembershipStats;
use Filament\Pages\Dashboard;

class AssociationDashboard extends Dashboard
{
    protected static ?string $title = 'Tổng quan Hội';

    protected static ?string $navigationLabel = 'Tổng quan';

    public function getWidgets(): array
    {
        return [MembershipStats::class, ContentStats::class];
    }
}
