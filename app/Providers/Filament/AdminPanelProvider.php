<?php

namespace App\Providers\Filament;

use App\Models\SiteAsset;
use Awcodes\Curator\CuratorPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\AssociationOverview;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin')
            ->brandName('DNT Bắc Ninh')
            ->brandLogo(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('logo') ?: null)
            ->brandLogoHeight('2.5rem')
            ->favicon(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('favicon') ?: asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#c21f2b'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make('Nội dung chính'),
                NavigationGroup::make('Nội dung website'),
                NavigationGroup::make('Nội dung trang chủ'),
                NavigationGroup::make('Quản lý Hội'),
                NavigationGroup::make('Khách hàng & liên hệ'),
                NavigationGroup::make('SEO'),
                NavigationGroup::make('Thư viện media'),
                NavigationGroup::make('Hệ thống'),
                NavigationGroup::make('Cài đặt website'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                AssociationOverview::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Hệ thống')
                    ->navigationSort(10)
                    ->navigationLabel('Vai trò & quyền'),
                CuratorPlugin::make()
                    ->label('Thư viện media')
                    ->pluralLabel('Thư viện media')
                    ->navigationGroup('Thư viện media')
                    ->navigationSort(1)
                    ->showBadge(true)
                    ->curations(true)
                    ->fileSwap(true),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
