<?php

namespace App\Filament\Member\Pages;

use App\Filament\Member\Widgets\MemberBusinessOverview;
use BackedEnum;
use Filament\Pages\Dashboard;
use Filament\Widgets\Widget;

class MemberOverview extends Dashboard
{
    protected static string $routePath = '/';

    protected static ?string $navigationLabel = 'Tổng quan';

    protected static ?string $title = 'Cổng hội viên';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            MemberBusinessOverview::class,
        ];
    }
}
