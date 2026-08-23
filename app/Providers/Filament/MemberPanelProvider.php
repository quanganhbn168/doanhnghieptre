<?php

namespace App\Providers\Filament;

use App\Models\SiteAsset;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MemberPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('member')
            ->path('thanh-vien')
            ->login()
            ->authGuard('web')
            ->brandName('Cổng Hội viên DNT Bắc Ninh')
            ->brandLogo(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('logo') ?: null)
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('favicon') ?: asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#c21f2b'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make('Doanh nghiệp của tôi'),
                NavigationGroup::make('Kết nối & giao thương'),
                NavigationGroup::make('Tài khoản'),
            ])
            ->discoverResources(in: app_path('Filament/Member/Resources'), for: 'App\Filament\Member\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Member/Widgets'), for: 'App\Filament\Member\Widgets')
            ->widgets([
                AccountWidget::class,
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
