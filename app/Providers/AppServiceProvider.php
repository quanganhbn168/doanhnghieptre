<?php

namespace App\Providers;

use App\Models\Intro;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteAsset;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Services\PopupService;
use App\Services\SiteChromeCache;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\TrackingSettings;
use App\Settings\WebsiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WebsiteSettingsService::class);
        $this->app->singleton(SiteChromeCache::class);
    }

    public function boot(): void
    {
        $this->applyWebsiteRuntimeSettings();

        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(\Awcodes\Curator\Models\Media::class, MediaPolicy::class);

        // Super admin luôn vượt qua policy/permission lẻ, kể cả khi có
        // resource mới được thêm sau lần chạy seeder gần nhất.
        Gate::before(function ($user): ?bool {
            return $user instanceof User && $user->hasRole('super_admin', 'admin')
                ? true
                : null;
        });

        RateLimiter::for('frontend-forms', fn (Request $request) => Limit::perMinute(5)
            ->by($request->ip().'|'.$request->route()?->getName()));
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        View::composer('*', function ($view): void {
            $view->with('website', app(WebsiteSettingsService::class)->all());
        });

        View::composer(['layouts.master', 'layouts.auth'], function ($view): void {
            $aboutSettings = null;
            $homepageSettings = null;
            $seoSettings = null;
            $trackingSettings = null;
            $chrome = app(SiteChromeCache::class)->get();
            $popup = app(PopupService::class)->activeForPage(request()->routeIs('home'));

            if (Schema::hasTable('settings')) {
                try {
                    $aboutSettings = app(AboutSettings::class);
                    $homepageSettings = app(HomepageSettings::class);
                    $seoSettings = app(SeoSettings::class);
                    $trackingSettings = app(TrackingSettings::class);
                } catch (\Throwable) {
                    // Cho phép giao diện hoạt động trong lúc cài đặt.
                }
            }

            $view->with([
                ...$chrome,
                'aboutSettings' => $aboutSettings,
                'homepageSettings' => $homepageSettings,
                'seoSettings' => $seoSettings,
                'trackingSettings' => $trackingSettings,
                'popup' => $popup,
            ]);
        });

        View::composer(['frontend.home', 'frontend.contact'], function ($view): void {
            $aboutSettings = null;
            $homepageSettings = null;
            $contactSettings = null;
            $siteAssets = Schema::hasTable('site_assets') ? SiteAsset::current() : null;

            if (Schema::hasTable('settings')) {
                try {
                    $aboutSettings = app(AboutSettings::class);
                    $homepageSettings = app(HomepageSettings::class);
                    $contactSettings = app(ContactSettings::class);
                } catch (\Throwable) {
                    // Cho phép giao diện hoạt động trong lúc cài đặt.
                }
            }

            $view->with(compact('aboutSettings', 'homepageSettings', 'contactSettings', 'siteAssets'));
        });

        foreach ([Intro::class, Menu::class, MenuItem::class, Service::class, ServiceCategory::class] as $model) {
            $model::saved(fn () => app(SiteChromeCache::class)->forget());
            $model::deleted(fn () => app(SiteChromeCache::class)->forget());
        }
    }

    private function applyWebsiteRuntimeSettings(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $settings = app(WebsiteSettings::class);
            if (in_array($settings->timezone, \DateTimeZone::listIdentifiers(), true)) {
                config(['app.timezone' => $settings->timezone]);
                date_default_timezone_set($settings->timezone);
            }

            config(['app.name' => $settings->site_name['vi'] ?? config('app.name')]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
