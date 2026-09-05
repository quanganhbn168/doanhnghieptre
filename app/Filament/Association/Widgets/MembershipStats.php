<?php

namespace App\Filament\Association\Widgets;

use App\Filament\Association\Resources\Memberships\MembershipResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembershipStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = MembershipResource::getEloquentQuery();

        return [
            Stat::make('Chờ Hội duyệt', (clone $query)->where('status', 'pending')->count())->color('warning'),
            Stat::make('Chờ Chi hội tiếp nhận', (clone $query)->where('status', 'chapter_pending')->count())->color('info'),
            Stat::make('Cần bổ sung', (clone $query)->where('status', 'rejected')->count())->color('danger'),
            Stat::make('Hội viên doanh nghiệp', (clone $query)->where('status', 'approved')->count())->color('success'),
        ];
    }
}
