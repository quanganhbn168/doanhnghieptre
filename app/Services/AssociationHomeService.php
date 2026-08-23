<?php

namespace App\Services;

use App\Settings\HomepageSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AssociationHomeService
{
    /** @var array<int, array{icon: string, title: string, description: string}> */
    private const DEFAULT_MEMBER_BENEFITS = [
        [
            'icon' => 'fa-solid fa-people-group',
            'title' => 'Mở rộng mạng lưới',
            'description' => 'Kết nối với cộng đồng doanh nhân trẻ uy tín tại Bắc Ninh và toàn quốc.',
        ],
        [
            'icon' => 'fa-solid fa-tower-broadcast',
            'title' => 'Nâng tầm thương hiệu',
            'description' => 'Quảng bá doanh nghiệp trên nền tảng truyền thông của Hội.',
        ],
        [
            'icon' => 'fa-solid fa-graduation-cap',
            'title' => 'Đào tạo & phát triển',
            'description' => 'Tham gia các chương trình đào tạo kỹ năng, quản trị và chuyên đề.',
        ],
        [
            'icon' => 'fa-solid fa-calendar-days',
            'title' => 'Tham gia sự kiện',
            'description' => 'Ưu tiên tham dự hội thảo, tọa đàm, sự kiện thương mại và giao lưu kết nối.',
        ],
        [
            'icon' => 'fa-solid fa-chart-line',
            'title' => 'Cơ hội giao thương',
            'description' => 'Tiếp cận cơ hội kinh doanh, kết nối cung cầu và hợp tác hiệu quả.',
        ],
        [
            'icon' => 'fa-solid fa-hand-holding-heart',
            'title' => 'Hỗ trợ doanh nghiệp',
            'description' => 'Tư vấn pháp lý, tài chính, chuyển đổi số và các chính sách hỗ trợ.',
        ],
    ];

    public function home(): array
    {
        return [
            'activityFields' => $this->activityFields(6),
            'memberBenefitTitle' => $this->memberBenefitTitle(),
            'memberBenefits' => $this->memberBenefits(),
            'upcomingEvents' => $this->events(3, true),
            'tradePosts' => $this->tradePosts(12),
        ];
    }

    public function memberBenefitTitle(): string
    {
        try {
            $title = app(HomepageSettings::class)->homepage_member_benefit_title;
            $value = trim((string) data_get($title, 'vi'));

            return $value !== '' ? $value : 'Lợi ích dành cho hội viên';
        } catch (\Throwable) {
            return 'Lợi ích dành cho hội viên';
        }
    }

    /** @return Collection<int, array{icon: string, title: string, description: string}> */
    public function memberBenefits(): Collection
    {
        try {
            $benefits = app(HomepageSettings::class)->homepage_member_benefits;
        } catch (\Throwable) {
            $benefits = self::DEFAULT_MEMBER_BENEFITS;
        }

        return collect(is_array($benefits) ? $benefits : [])
            ->filter(fn (mixed $benefit): bool => is_array($benefit) && filled($benefit['title'] ?? null))
            ->map(fn (array $benefit): array => [
                'icon' => filled($benefit['icon'] ?? null) ? (string) $benefit['icon'] : 'fa-solid fa-circle-check',
                'title' => trim((string) $benefit['title']),
                'description' => trim((string) ($benefit['description'] ?? '')),
            ])
            ->values();
    }

    public function businesses(int $limit = 24, bool $featuredOnly = false): Collection
    {
        if (! Schema::hasTable('businesses')) {
            return collect();
        }

        $query = DB::table('businesses')
            ->leftJoin('business_categories', 'businesses.business_category_id', '=', 'business_categories.id')
            ->leftJoin('media as business_logos', function ($join): void {
                $join->on('business_logos.model_id', '=', 'businesses.id')
                    ->where('business_logos.model_type', '=', 'App\\Models\\Business')
                    ->where('business_logos.collection_name', '=', 'logo');
            })
            ->where('businesses.status', 'approved')
            ->select([
                'businesses.id',
                'businesses.name',
                'businesses.slug',
                'businesses.summary',
                'businesses.province',
                'businesses.is_featured',
                'business_categories.name as category_name',
                'business_logos.id as logo_media_id',
                'business_logos.file_name as logo_file_name',
                'business_logos.disk as logo_disk',
                DB::raw('null as cover_url'),
            ]);

        if ($featuredOnly) {
            $query->where('businesses.is_featured', true);
        }

        return $query
            ->orderByDesc('businesses.is_featured')
            ->orderByDesc('businesses.approved_at')
            ->orderBy('businesses.name')
            ->limit($limit)
            ->get()
            ->map(fn (object $business): object => $this->presentBusiness($business));
    }

    public function activityFields(int $limit = 12): Collection
    {
        if (! Schema::hasTable('industries') || ! Schema::hasTable('business_industries') || ! Schema::hasTable('businesses')) {
            return collect();
        }

        return DB::table('industries')
            ->join('business_industries', 'industries.id', '=', 'business_industries.industry_id')
            ->join('businesses', function ($join): void {
                $join->on('business_industries.business_id', '=', 'businesses.id')
                    ->where('businesses.status', '=', 'approved');
            })
            ->where('industries.is_active', true)
            ->select([
                'industries.name',
                'industries.slug',
                'industries.description',
                DB::raw('count(distinct businesses.id) as business_count'),
            ])
            ->groupBy('industries.id', 'industries.name', 'industries.slug', 'industries.description')
            ->orderByDesc('business_count')
            ->orderBy('industries.name')
            ->limit($limit)
            ->get()
            ->map(fn (object $industry): object => (object) [
                ...get_object_vars($industry),
                'icon' => $this->activityFieldIcon($industry->slug),
                'image' => $this->activityFieldImage($industry->slug),
            ]);
    }

    public function events(int $limit = 24, bool $upcomingOnly = false): Collection
    {
        if (! Schema::hasTable('events')) {
            return collect();
        }

        $query = DB::table('events')
            ->where('events.status', 'published')
            ->where('events.visibility', 'public')
            ->select([
                'events.id',
                'events.title',
                'events.slug',
                'events.summary',
                'events.venue_name',
                'events.venue_address',
                'events.starts_at',
                'events.ends_at',
                'events.registration_opens_at',
                'events.registration_closes_at',
                'events.capacity',
                DB::raw('null as cover_url'),
            ]);

        if ($upcomingOnly) {
            $query->where('events.starts_at', '>=', now());
        }

        return $query
            ->orderBy('events.starts_at', $upcomingOnly ? 'asc' : 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (object $event): object => $this->presentEvent($event));
    }

    public function tradePosts(int $limit = 24): Collection
    {
        if (! Schema::hasTable('trade_posts')) {
            return collect();
        }

        return DB::table('trade_posts')
            ->leftJoin('businesses', 'trade_posts.business_id', '=', 'businesses.id')
            ->where('trade_posts.status', 'approved')
            ->where(function ($query): void {
                $query->whereNull('trade_posts.expires_at')
                    ->orWhere('trade_posts.expires_at', '>=', now());
            })
            ->select([
                'trade_posts.id',
                'trade_posts.type',
                'trade_posts.title',
                'trade_posts.slug',
                'trade_posts.summary',
                'trade_posts.budget_label',
                'trade_posts.location_label',
                'trade_posts.approved_at',
                'businesses.name as business_name',
                DB::raw('null as cover_url'),
            ])
            ->orderByDesc('trade_posts.approved_at')
            ->orderByDesc('trade_posts.id')
            ->limit($limit)
            ->get();
    }

    private function presentEvent(object $event): object
    {
        $startsAt = Carbon::parse($event->starts_at);
        $event->date_day = $startsAt->format('d');
        $event->date_month = 'Tháng '.$startsAt->format('m');
        $event->date_year = $startsAt->format('Y');
        $event->time_label = $startsAt->format('H:i');
        $event->registration_is_open = (! $event->registration_opens_at || Carbon::parse($event->registration_opens_at)->isPast())
            && (! $event->registration_closes_at || Carbon::parse($event->registration_closes_at)->isFuture());

        return $event;
    }

    private function activityFieldIcon(string $slug): string
    {
        return match (true) {
            str_contains($slug, 'cong-nghe') || str_contains($slug, 'phan-mem') || str_contains($slug, 'dien-tu') || str_contains($slug, 'tu-dong-hoa') => 'fa-microchip',
            str_contains($slug, 'logistics') || str_contains($slug, 'van-tai') || str_contains($slug, 'xuat-nhap') => 'fa-truck-fast',
            str_contains($slug, 'xay-dung') || str_contains($slug, 'vat-lieu') => 'fa-compass-drafting',
            str_contains($slug, 'nong-nghiep') || str_contains($slug, 'thuc-pham') => 'fa-seedling',
            str_contains($slug, 'tai-chinh') || str_contains($slug, 'tu-van') => 'fa-chart-pie',
            str_contains($slug, 'giao-duc') || str_contains($slug, 'dao-tao') => 'fa-graduation-cap',
            default => 'fa-industry',
        };
    }

    private function presentBusiness(object $business): object
    {
        $business->logo_url = $this->mediaUrl(
            $business->logo_media_id ?? null,
            $business->logo_file_name ?? null,
            $business->logo_disk ?? null,
        );

        return $business;
    }

    private function mediaUrl(mixed $id, mixed $fileName, mixed $disk): ?string
    {
        if (! filled($id) || ! filled($fileName)) {
            return null;
        }

        return Storage::disk(filled($disk) ? (string) $disk : 'public_media')->url($id.'/'.$fileName);
    }

    private function activityFieldImage(string $slug): string
    {
        return match (true) {
            str_contains($slug, 'cong-nghe') || str_contains($slug, 'phan-mem') || str_contains($slug, 'dien-tu') || str_contains($slug, 'tu-dong-hoa') => 'assets/images/home-demo/camera.jpg',
            str_contains($slug, 'logistics') || str_contains($slug, 'van-tai') || str_contains($slug, 'xuat-nhap') => 'assets/images/home-demo/factory.jpg',
            str_contains($slug, 'xay-dung') || str_contains($slug, 'vat-lieu') => 'assets/images/home-demo/factory.jpg',
            str_contains($slug, 'nong-nghiep') || str_contains($slug, 'thuc-pham') => 'assets/images/home-demo/product.jpg',
            str_contains($slug, 'giao-duc') || str_contains($slug, 'dao-tao') => 'assets/images/home-demo/classroom.jpg',
            default => 'assets/images/home-demo/team.jpg',
        };
    }
}
