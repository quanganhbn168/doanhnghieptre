<?php

namespace App\Filament\Member\Widgets;

use App\Models\Business;
use App\Models\TradePost;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemberBusinessOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Tổng quan doanh nghiệp của tôi';

    protected function getStats(): array
    {
        $user = auth('web')->user();
        $query = Business::query()->representedBy($user);
        $tradePosts = TradePost::query()->ownedBy($user);

        return [
            Stat::make('Doanh nghiệp', (clone $query)->count())->description('Doanh nghiệp do anh/chị đại diện'),
            Stat::make('Đang công bố', (clone $query)->where('status', 'approved')->count())->description('Hiển thị trên danh bạ')->color('success'),
            Stat::make('Bản cập nhật chờ kiểm tra', (clone $query)->whereNotNull('pending_profile')->count())->description('Hồ sơ mới hoặc vừa cập nhật')->color('warning'),
            Stat::make('Cơ hội giao thương', (clone $tradePosts)->publiclyVisible()->count())->description('Đang hiển thị công khai')->color('primary'),
        ];
    }
}
