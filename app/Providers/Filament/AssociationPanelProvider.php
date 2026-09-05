<?php

namespace App\Providers\Filament;

use App\Filament\Association\Pages\AssociationDashboard;
use App\Models\SiteAsset;
use Awcodes\Curator\CuratorPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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

class AssociationPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('association')
            ->path('hoi')
            ->login()
            ->authGuard('admin')
            ->databaseTransactions()
            ->brandName('Quản lý Hội DNT Bắc Ninh')
            ->brandLogo(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('logo') ?: null)
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => SiteAsset::current()->getFirstMediaUrl('favicon') ?: asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#c21f2b'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                CuratorPlugin::make()->label('Thư viện ảnh')->pluralLabel('Thư viện ảnh')->navigationGroup('Nội dung Hội')->navigationSort(40),
            ])
            ->navigationGroups([
                NavigationGroup::make('Hội viên doanh nghiệp'),
                NavigationGroup::make('Chợ doanh nghiệp'),
                NavigationGroup::make('Nội dung Hội'),
                NavigationGroup::make('Tổ chức Hội'),

            ])
            ->discoverResources(in: app_path('Filament/Association/Resources'), for: 'App\Filament\Association\Resources')
            ->pages([
                AssociationDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Association/Widgets'), for: 'App\Filament\Association\Widgets')
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
