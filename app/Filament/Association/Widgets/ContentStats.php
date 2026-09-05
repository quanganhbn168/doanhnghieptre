<?php

namespace App\Filament\Association\Widgets;

use App\Filament\Association\Resources\MemberPosts\MemberPostResource;
use App\Filament\Association\Resources\TradePosts\TradePostResource;
use App\Models\Post;
use App\Models\TradePost;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return (bool) auth('admin')->user()?->canModerateContent();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Bài hội viên chờ duyệt', Post::query()->whereNotNull('business_id')->where('review_status', 'pending')->count())
                ->color('warning')->url(MemberPostResource::getUrl('index')),
            Stat::make('Tin giao thương chờ duyệt', TradePost::query()->where('status', 'pending')->count())
                ->color('warning')->url(TradePostResource::getUrl('index')),
            Stat::make('Bài hội viên đang hiển thị', Post::query()->whereNotNull('business_id')->visibleOnSite()->count())->color('success'),
        ];
    }
}
