<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class AssociationOverview extends Dashboard
{
    protected static string $routePath = '/';

    protected static ?string $slug = 'tong-quan-hoi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Tổng quan Hội';

    protected static ?string $title = 'Tổng quan vận hành Hội';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Hội';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.association-overview';

    /** @var array<int, array{label: string, value: int, description: string, icon: string}> */
    public array $metrics = [];

    public function mount(): void
    {
        $this->metrics = [
            [
                'label' => 'Hội viên',
                'value' => $this->count('members', fn ($query) => $query->where('status', 'approved')),
                'description' => 'Hồ sơ hội viên đã được duyệt',
                'icon' => 'heroicon-o-users',
            ],
            [
                'label' => 'Doanh nghiệp',
                'value' => $this->count('businesses', fn ($query) => $query->where('status', 'approved')),
                'description' => 'Doanh nghiệp đang công bố',
                'icon' => 'heroicon-o-building-office-2',
            ],
            [
                'label' => 'Doanh nghiệp chờ duyệt',
                'value' => $this->count('businesses', fn ($query) => $query->where('status', 'pending')),
                'description' => 'Hồ sơ doanh nghiệp vừa gửi từ cổng tài khoản',
                'icon' => 'heroicon-o-clipboard-document-check',
            ],
            [
                'label' => 'Sự kiện sắp tới',
                'value' => $this->count('events', fn ($query) => $query->where('status', 'published')->where('starts_at', '>=', now())),
                'description' => 'Sự kiện đã công bố, chưa diễn ra',
                'icon' => 'heroicon-o-calendar-days',
            ],
            [
                'label' => 'Cơ hội giao thương',
                'value' => $this->count('trade_posts', fn ($query) => $query->where('status', 'approved')->where(fn ($expired) => $expired->whereNull('expires_at')->orWhere('expires_at', '>=', now()))),
                'description' => 'Cơ hội còn hiệu lực trên nền tảng',
                'icon' => 'heroicon-o-arrows-right-left',
            ],
            [
                'label' => 'Đăng ký sự kiện',
                'value' => $this->count('event_registrations'),
                'description' => 'Tổng lượt đăng ký tham dự',
                'icon' => 'heroicon-o-ticket',
            ],
        ];
    }

    private function count(string $table, ?callable $constraint = null): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);

        if ($constraint) {
            $constraint($query);
        }

        return $query->count();
    }
}
