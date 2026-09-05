<div class="dnt-site-header" data-site-header>
    <header>
        <div class="dnt-site-header__top">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="dnt-site-header__top-row">
                    @php($headerLogo = $siteAssets?->getFirstMediaUrl('logo'))
                    <a class="dnt-brand" href="{{ route('home') }}" aria-label="DNT Bắc Ninh - Trang chủ">
                        @if($headerLogo)
                            <img class="dnt-brand__logo" src="{{ $headerLogo }}" alt="Hội Doanh nhân trẻ tỉnh Bắc Ninh" width="395" height="100">
                        @else
                            <span class="dnt-brand__mark" aria-hidden="true"><i class="fa-solid fa-compass"></i><b>★</b></span>
                            <span><strong>DNT BẮC NINH</strong><small>Hội Doanh nhân trẻ tỉnh Bắc Ninh</small></span>
                        @endif
                    </a>

                    <form class="dnt-header-search" action="{{ route('directory.index') }}" method="GET" role="search">
                        <label class="sr-only" for="dnt-header-search-input">Tìm kiếm doanh nghiệp</label>
                        <input id="dnt-header-search-input" name="q" type="search" value="{{ request('q') }}" placeholder="Tìm kiếm doanh nghiệp, ngành nghề...">
                        <button type="submit" aria-label="Tìm kiếm"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                    </form>

                    <div class="dnt-header-actions">
                        @auth
                            <a class="dnt-header-actions__register" href="{{ auth()->user()->hasApprovedBusiness() ? url('/thanh-vien') : route('account.dashboard') }}">{{ auth()->user()->hasApprovedBusiness() ? 'Cổng doanh nghiệp' : 'Tài khoản' }}</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf <button class="dnt-header-actions__login" type="submit">Đăng xuất</button></form>
                        @else
                            <a class="dnt-header-actions__register" href="{{ route('membership.create') }}">Đăng ký hội viên</a>
                            <a class="dnt-header-actions__login" href="{{ route('login') }}">Đăng nhập</a>
                        @endauth
                    </div>
                    <button class="dnt-mobile-toggle" type="button" data-mobile-menu-open aria-controls="mobileMenu" aria-label="Mở menu"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>

        <div class="dnt-site-header__nav-bar">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="dnt-main-nav" aria-label="Điều hướng chính">
                    @php($aboutIntros = $aboutIntros ?? collect())
                    @if(isset($headerMenu) && $headerMenu?->items?->isNotEmpty())
                        @foreach($headerMenu->items as $item)
                            @php($isAboutMenu = $item->route === 'about' || rtrim($item->href, '/') === rtrim(route('about'), '/'))
                            @php($hasAboutDropdown = $isAboutMenu && $aboutIntros->isNotEmpty())
                            @php($hasChildren = $hasAboutDropdown || $item->childrenRecursive->isNotEmpty())
                            <div class="dnt-main-nav__item {{ $hasChildren ? 'has-children' : '' }}">
                                <a class="{{ $item->isCurrent() || $item->hasCurrentDescendant() || ($isAboutMenu && request()->routeIs('about*')) ? 'is-active' : '' }}" href="{{ $item->href }}">{{ $item->title }} @if($hasChildren)<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>@endif</a>
                                @if($hasAboutDropdown)
                                    <ul class="dnt-main-nav__dropdown">
                                        @foreach($aboutIntros as $intro)
                                            <li><a href="{{ route('about.show', ['slug' => $intro->slug]) }}">{{ $intro->title }}</a></li>
                                        @endforeach
                                    </ul>
                                @elseif($item->childrenRecursive->isNotEmpty())
                                    <ul class="dnt-main-nav__dropdown">
                                        @foreach($item->childrenRecursive as $child)
                                            <li class="{{ $child->childrenRecursive->isNotEmpty() ? 'has-children' : '' }}">
                                                <a href="{{ $child->href }}">{{ $child->title }} @if($child->childrenRecursive->isNotEmpty())<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>@endif</a>
                                                @if($child->childrenRecursive->isNotEmpty())
                                                    <ul class="dnt-main-nav__dropdown dnt-main-nav__dropdown--nested">
                                                        @foreach($child->childrenRecursive as $grandchild)
                                                            <li><a href="{{ $grandchild->href }}">{{ $grandchild->title }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="dnt-main-nav__item"><a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Trang chủ</a></div>
                        <div class="dnt-main-nav__item {{ $aboutIntros->isNotEmpty() ? 'has-children' : '' }}">
                            <a class="{{ request()->routeIs('about*') ? 'is-active' : '' }}" href="{{ route('about') }}">Giới thiệu @if($aboutIntros->isNotEmpty())<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>@endif</a>
                            @if($aboutIntros->isNotEmpty())
                                <ul class="dnt-main-nav__dropdown">
                                    @foreach($aboutIntros as $intro)
                                        <li><a href="{{ route('about.show', ['slug' => $intro->slug]) }}">{{ $intro->title }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="dnt-main-nav__item has-children"><a class="{{ request()->routeIs('businesses.*', 'directory.*', 'account.businesses.*') ? 'is-active' : '' }}" href="{{ route('directory.index') }}">Hội viên <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></a><ul class="dnt-main-nav__dropdown"><li><a href="{{ route('directory.index') }}">Danh bạ doanh nghiệp</a></li><li><a href="{{ route('home') }}#loi-ich-hoi-vien">Lợi ích hội viên</a></li><li>@auth<a href="{{ route('membership.create') }}">Nộp hồ sơ doanh nghiệp</a>@else<a href="{{ route('membership.create') }}">Đăng ký hội viên</a>@endauth</li></ul></div>
                        <div class="dnt-main-nav__item has-children"><a class="{{ request()->routeIs('businesses.*', 'trade.*') ? 'is-active' : '' }}" href="{{ route('businesses.index') }}">Doanh nghiệp <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></a><ul class="dnt-main-nav__dropdown"><li><a href="{{ route('businesses.index') }}">Hoạt động doanh nghiệp</a></li><li><a href="{{ route('trade.index') }}">Cơ hội giao thương</a></li></ul></div>
                        <div class="dnt-main-nav__item"><a class="{{ request()->routeIs('events.*') ? 'is-active' : '' }}" href="{{ route('events.index') }}">Sự kiện</a></div>
                        <div class="dnt-main-nav__item"><a class="{{ request()->routeIs('news.*') ? 'is-active' : '' }}" href="{{ route('news.index') }}">Tin tức</a></div>
                        <div class="dnt-main-nav__item"><a class="{{ request()->routeIs('contact*') ? 'is-active' : '' }}" href="{{ route('contact') }}">Liên hệ</a></div>
                    @endif
                </nav>
            </div>
        </div>
    </header>
</div>

<div class="dnt-mobile-menu" id="mobileMenu" data-mobile-menu>
    <button class="dnt-mobile-menu__backdrop" type="button" data-mobile-menu-backdrop aria-label="Đóng menu"></button>
    <div class="dnt-mobile-menu__panel" data-mobile-menu-panel aria-hidden="true" tabindex="-1">
        <div class="dnt-mobile-menu__top"><span class="dnt-mobile-menu__brand">DNT BẮC NINH</span><button type="button" data-mobile-menu-close aria-label="Đóng menu"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>
        <nav aria-label="Điều hướng trên điện thoại">
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('about') }}">Giới thiệu</a>
            <a href="{{ route('directory.index') }}">Danh bạ doanh nghiệp</a>
            <a href="{{ route('businesses.index') }}">Hoạt động doanh nghiệp</a>
            <a href="{{ route('events.index') }}">Sự kiện</a>
            <a href="{{ route('trade.index') }}">Giao thương</a>
            <a href="{{ route('news.index') }}">Tin tức</a>
            <a href="{{ route('contact') }}">Liên hệ</a>
            @auth<a href="{{ auth()->user()->hasApprovedBusiness() ? url('/thanh-vien') : route('account.dashboard') }}">{{ auth()->user()->hasApprovedBusiness() ? 'Cổng doanh nghiệp' : 'Tài khoản của tôi' }}</a>@else<a href="{{ route('membership.create') }}">Đăng ký hội viên</a>@endauth
        </nav>
    </div>
</div>
