<?php

namespace App\Filament\Association\Widgets;

use App\Filament\Association\Resources\Memberships\MembershipResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembershipStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return MembershipResource::canViewAny();
    }

    protected function getStats(): array
    {
        $query = MembershipResource::getEloquentQuery();

        return [
            Stat::make('Chờ Văn phòng kiểm tra', (clone $query)->where('status', 'pending')->count())->color('warning'),
            Stat::make('Chờ Chi hội thẩm định', (clone $query)->where('status', 'chapter_pending')->count())->color('info'),
            Stat::make('Chờ Trưởng ban chuẩn y', (clone $query)->where('status', 'board_pending')->count())->color('info'),
            Stat::make('Cần bổ sung', (clone $query)->where('status', 'changes_requested')->count())->color('danger'),
            Stat::make('Hội viên doanh nghiệp', (clone $query)->where('status', 'approved')->count())->color('success'),
        ];
    }
}
