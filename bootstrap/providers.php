<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AssociationPanelProvider;
use App\Providers\Filament\MemberPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ObserverServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    MemberPanelProvider::class,
    AssociationPanelProvider::class,
    FortifyServiceProvider::class,
    ObserverServiceProvider::class,
];
