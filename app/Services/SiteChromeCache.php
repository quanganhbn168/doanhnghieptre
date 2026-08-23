<?php

namespace App\Services;

use App\Models\Intro;
use App\Models\Menu;
use App\Models\ServiceCategory;
use App\Models\SiteAsset;
use App\Settings\WebsiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteChromeCache
{
    public const KEY = 'site:chrome:v7';

    public function get(): array
    {
        try {
            return Cache::remember(self::KEY, now()->addDay(), fn (): array => $this->build());
        } catch (Throwable) {
            return $this->build();
        }
    }

    public function forget(): void
    {
        try {
            Cache::forget(self::KEY);
        } catch (Throwable) {
            // Cache không được phép làm hỏng website.
        }
    }

    private function build(): array
    {
        $siteAssets = Schema::hasTable('site_assets') ? SiteAsset::current()->loadMissing('media') : null;
        $headerMenu = null;
        $megaMenu = null;
        $footerMenus = collect();
        $serviceCategories = collect();
        $aboutIntros = collect();

        if (Schema::hasTable('intros')) {
            $aboutIntros = Intro::query()
                ->visibleOnSite()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['title', 'slug']);
        }

        if (Schema::hasTable('service_categories') && Schema::hasTable('services')) {
            $serviceCategories = ServiceCategory::query()
                ->where('is_active', true)
                ->with([
                    'slugs',
                    'services' => fn ($query) => $query
                        ->where('is_active', true)
                        ->with('slugs')
                        ->orderBy('sort_order')
                        ->orderBy('id'),
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        if (! Schema::hasTable('settings') || ! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return compact('siteAssets', 'headerMenu', 'megaMenu', 'footerMenus', 'serviceCategories', 'aboutIntros');
        }

        try {
            $settings = app(WebsiteSettings::class);
            $menus = Menu::query()
                ->where('is_active', true)
                ->whereIn('location', ['header', 'footer'])
                ->with(['items' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with('childrenRecursive')])
                ->get();
            $headerMenus = $menus->where('location', 'header');
            $availableFooterMenus = $menus->where('location', 'footer');
            $headerMenu = $headerMenus->firstWhere('id', $settings->header_menu_id);
            $megaMenu = $headerMenus->firstWhere('id', $settings->mega_menu_id);
            $footerMenus = collect([
                $availableFooterMenus->firstWhere('id', $settings->footer_menu_1_id),
                $availableFooterMenus->firstWhere('id', $settings->footer_menu_2_id),
            ])->filter()->values();
        } catch (Throwable) {
            // Cho phép cài đặt và migrate khi settings chưa hoàn chỉnh.
        }

        return compact('siteAssets', 'headerMenu', 'megaMenu', 'footerMenus', 'serviceCategories', 'aboutIntros');
    }
}
