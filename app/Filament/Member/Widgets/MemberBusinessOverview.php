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
        $memberId = auth('web')->user()?->member?->id;
        $query = Business::query()->whereHas('members', fn ($members) => $members->whereKey($memberId));
        $tradePosts = TradePost::query()->where('member_id', $memberId);

        return [
            Stat::make('Doanh nghiệp', (clone $query)->count())->description('Doanh nghiệp do anh/chị đại diện'),
            Stat::make('Đang công bố', (clone $query)->where('status', 'approved')->count())->description('Hiển thị trên danh bạ')->color('success'),
            Stat::make('Chờ Hội duyệt', (clone $query)->whereIn('status', ['pending', 'chapter_pending'])->count())->description('Hồ sơ mới hoặc vừa cập nhật')->color('warning'),
            Stat::make('Cơ hội giao thương', (clone $tradePosts)->where('status', 'approved')->count())->description('Đang hiển thị công khai')->color('primary'),
        ];
    }
}
