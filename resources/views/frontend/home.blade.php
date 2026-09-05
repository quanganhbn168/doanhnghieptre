@extends('layouts.master')

@section('title', $homepageTitle)
@section('meta_description', $homepageDescription)
@section('canonical', url('/'))
@section('og_type', 'website')

@section('content')
<div class="dnt-home">
    <section class="dnt-hero" aria-labelledby="dnt-hero-title">
        <img class="dnt-hero__image" src="{{ asset('assets/images/dnt/association-hero.png') }}" alt="Doanh nhân trẻ kết nối và hợp tác" fetchpriority="high" width="1664" height="936">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 dnt-hero__container">
            <div class="dnt-hero__content">
                <h1 id="dnt-hero-title">Cộng đồng <span>doanh nhân trẻ Bắc Ninh</span></h1>
                <p class="dnt-hero__lead">Kết nối doanh nghiệp · Lan tỏa hoạt động · Mở rộng hợp tác và cơ hội giao thương trong cộng đồng.</p>
                <div class="dnt-hero__actions">
                    <a class="dnt-button dnt-button--gold" href="{{ route('businesses.index') }}">Khám phá doanh nghiệp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <a class="dnt-button dnt-button--outline" href="{{ route('events.index') }}">Xem sự kiện <i class="fa-regular fa-calendar-days" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="dnt-section dnt-intro" id="gioi-thieu" aria-labelledby="dnt-intro-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 dnt-intro__grid">
            <div class="dnt-intro__visual">
                <img src="{{ asset('assets/images/home-demo/factory.jpg') }}" alt="Hoạt động logistics và sản xuất của doanh nghiệp" loading="lazy" width="1200" height="800">
                <span class="dnt-intro__visual-label"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Bắc Ninh · Việt Nam</span>
            </div>
            <div class="dnt-intro__content">
                <h2 id="dnt-intro-title">Đồng hành · Kết nối · Phát triển</h2>
                <p>Hội Doanh nhân trẻ tỉnh Bắc Ninh là nơi doanh nghiệp trẻ gặp gỡ, trao đổi kinh nghiệm, chia sẻ cơ hội và cùng xây dựng một cộng đồng kinh doanh năng động, trách nhiệm.</p>
                <div class="dnt-purpose-grid">
                    <article><span><i class="fa-solid fa-people-group" aria-hidden="true"></i></span><h3>Kết nối doanh nghiệp</h3><p>Mở rộng quan hệ hợp tác trong và ngoài tỉnh.</p></article>
                    <article><span><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span><h3>Lan tỏa hoạt động</h3><p>Cập nhật chương trình, câu chuyện và sáng kiến nổi bật.</p></article>
                    <article><span><i class="fa-solid fa-handshake" aria-hidden="true"></i></span><h3>Giao thương hợp tác</h3><p>Kết nối nhu cầu, năng lực và cơ hội phát triển.</p></article>
                </div>
                <a class="dnt-text-link" href="{{ route('about') }}">Tìm hiểu thêm về DNT Bắc Ninh <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

    @if($memberBenefits->isNotEmpty())
        <section class="dnt-member-benefits" id="loi-ich-hoi-vien" aria-labelledby="dnt-member-benefits-title">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="dnt-member-benefits__heading">
                    <span aria-hidden="true"></span>
                    <h2 id="dnt-member-benefits-title">{{ $memberBenefitTitle }}</h2>
                    <span aria-hidden="true"></span>
                </div>
                <div class="dnt-member-benefits__grid">
                    @foreach($memberBenefits as $benefit)
                        <article class="dnt-member-benefit">
                            <span class="dnt-member-benefit__icon"><i class="{{ $benefit['icon'] }}" aria-hidden="true"></i></span>
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="dnt-section dnt-section--soft" id="doanh-nghiep" aria-labelledby="dnt-business-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-section-heading">
                <h2 id="dnt-business-title">Các lĩnh vực hoạt động</h2>
                <a class="dnt-text-link" href="{{ route('directory.index') }}">Xem danh bạ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            @if($activityFields->isNotEmpty())
                <div class="dnt-activity-grid">
                    @foreach($activityFields as $field)
                        <a class="dnt-activity-card" href="{{ route('directory.index', ['industry' => $field->slug]) }}">
                            <img src="{{ asset($field->image) }}" alt="{{ $field->name }}" loading="lazy" width="800" height="600">
                            <span class="dnt-activity-card__content">
                                <strong>{{ $field->name }}</strong>
                                <small>{{ number_format($field->business_count) }} doanh nghiệp</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="dnt-empty-card"><i class="fa-solid fa-building-circle-check" aria-hidden="true"></i><div><h3>Lĩnh vực hoạt động đang được cập nhật</h3><p>Danh bạ doanh nghiệp sẽ sớm hiển thị theo từng lĩnh vực.</p></div></div>
            @endif
        </div>
    </section>

    <section class="dnt-section" id="su-kien" aria-labelledby="dnt-event-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-section-heading">
                <h2 id="dnt-event-title">Sự kiện sắp diễn ra</h2>
                <a class="dnt-text-link" href="{{ route('events.index') }}">Xem tất cả <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            @if($upcomingEvents->isNotEmpty())
                <div class="dnt-event-grid">
                    @foreach($upcomingEvents as $event)
                        <article class="dnt-event-card">
                            <time class="dnt-event-card__date" datetime="{{ $event->starts_at }}"><strong>{{ $event->date_day }}</strong><span>{{ $event->date_month }}</span><small>{{ $event->date_year }}</small></time>
                            <div class="dnt-event-card__content"><h3>{{ $event->title }}</h3><p>{{ $event->summary ?: 'Thông tin chương trình đang được cập nhật.' }}</p><span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $event->time_label }} @if($event->venue_name) · {{ $event->venue_name }} @endif</span><div class="dnt-event-card__actions">@if($event->registration_is_open)<button class="dnt-event-card__register" type="button" data-event-register-open data-event-name="{{ $event->title }}" data-event-slug="{{ $event->slug }}">Đăng ký tham dự <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>@else<span class="dnt-event-card__closed">Đăng ký chưa mở hoặc đã kết thúc</span>@endif</div></div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dnt-empty-card"><i class="fa-regular fa-calendar-plus" aria-hidden="true"></i><div><h3>Chưa có sự kiện được công bố</h3><p>Lịch hoạt động sắp tới sẽ hiển thị tại đây.</p></div></div>
            @endif
        </div>
    </section>

    <section class="dnt-section dnt-trade-section" id="giao-thuong" aria-labelledby="dnt-trade-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-trade-panel">
                <div class="dnt-trade-panel__header">
                    <h2 id="dnt-trade-title">Cơ hội giao thương</h2>
                    <a class="dnt-trade-panel__link" href="{{ route('trade.index') }}">Xem tất cả <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($tradePosts->isNotEmpty())
                    <div class="dnt-trade-list">
                        @foreach($tradePosts as $tradePost)
                            <article class="dnt-trade-list__item">
                                <span class="dnt-trade-list__icon"><i class="fa-solid {{ $tradePost->type_icon }}" aria-hidden="true"></i></span>
                                <div><h3>{{ $tradePost->title }}</h3><p>{{ $tradePost->business_name ?: $tradePost->summary }}</p></div>
                                <div class="dnt-trade-list__meta"><span>{{ $tradePost->location_label ?: 'Đang cập nhật khu vực' }}</span><strong>{{ $tradePost->budget_label ?: 'Liên hệ trao đổi' }}</strong></div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="dnt-empty-card dnt-empty-card--dark"><i class="fa-solid fa-handshake" aria-hidden="true"></i><div><h3>Chưa có cơ hội giao thương được duyệt</h3><p>Các nhu cầu mua bán và hợp tác sẽ xuất hiện tại đây sau khi được xác thực.</p></div></div>
                @endif
                <a class="dnt-trade-panel__more" href="{{ route('trade.index') }}">Xem thêm cơ hội giao thương <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

    <section class="dnt-section" id="tin-tuc" aria-labelledby="dnt-news-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-section-heading">
                <h2 id="dnt-news-title">Tin tức & hoạt động</h2>
                <a class="dnt-text-link" href="{{ route('news.index') }}">Xem tất cả <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            @if($newsItems->isNotEmpty())
                <div class="dnt-news-showcase">
                    @foreach($newsItems->take(1) as $article)
                        <article class="dnt-news-feature">
                            <a class="dnt-news-feature__image" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}"><img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500"></a>
                            <div class="dnt-news-feature__body">
                                <span>{{ $article['category'] ?: 'Tin tức' }} · {{ $article['date'] }}</span>
                                <h3><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h3>
                                <p>{{ $article['excerpt'] }}</p>
                                <a class="dnt-news-feature__link" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">Đọc tiếp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @endforeach
                    <div class="dnt-news-compact-grid">
                        @foreach($newsItems->skip(1) as $article)
                            <article class="dnt-news-compact">
                                <a class="dnt-news-compact__image" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}"><img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" width="480" height="300"></a>
                                <div><h3><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h3><span>{{ $article['date'] }}</span><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">Đọc tiếp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="dnt-empty-card"><i class="fa-regular fa-newspaper" aria-hidden="true"></i><div><h3>Chưa có tin tức được xuất bản</h3><p>Bài viết tạo trong mục Posts sẽ tự động hiển thị ở đây.</p></div></div>
            @endif
        </div>
    </section>

    <section class="dnt-partners" aria-labelledby="dnt-partners-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-partners__heading"><span aria-hidden="true"></span><h2 id="dnt-partners-title">Đối tác đồng hành</h2><span aria-hidden="true"></span></div>
            <div class="dnt-partners__rail">
                <span class="dnt-partners__control" aria-hidden="true"><i class="fa-solid fa-chevron-left"></i></span>
                <div class="dnt-partners__logos" aria-label="Danh sách đối tác đồng hành">
                    <span class="dnt-partner">Vietcombank</span>
                    <span class="dnt-partner">BIDV</span>
                    <span class="dnt-partner">VNPT</span>
                    <span class="dnt-partner">FPT</span>
                    <span class="dnt-partner">Vingroup</span>
                    <span class="dnt-partner">Viettel</span>
                    <span class="dnt-partner">VCCI</span>
                </div>
                <span class="dnt-partners__control" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </div>
        </div>
    </section>

    <section class="dnt-membership-cta" aria-labelledby="dnt-membership-cta-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-membership-cta__panel">
                <span class="dnt-membership-cta__icon" aria-hidden="true"><i class="fa-solid fa-people-group"></i></span>
                <div class="dnt-membership-cta__content"><h2 id="dnt-membership-cta-title">Gia nhập cộng đồng doanh nhân trẻ Bắc Ninh</h2><p>Kết nối · Học hỏi · Hợp tác · Phát triển bền vững</p></div>
                <a class="dnt-membership-cta__button" href="{{ auth()->check() ? route('membership.create') : route('membership.create') }}">Đăng ký hội viên ngay <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>

    <div class="dnt-event-registration-modal hidden" data-event-registration-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="dnt-event-registration-title">
        <button class="dnt-event-registration-modal__backdrop" type="button" data-event-register-close aria-label="Đóng biểu mẫu"></button>
        <div class="dnt-event-registration-modal__panel">
            <button class="dnt-event-registration-modal__close" type="button" data-event-register-close aria-label="Đóng"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            <h2 id="dnt-event-registration-title">Đăng ký tham dự</h2>
            <p data-event-registration-name></p>
            <form method="POST" action="{{ route('events.register', ['event' => '__event__']) }}" data-event-registration-form data-event-registration-action="{{ route('events.register', ['event' => '__event__']) }}">
                @csrf
                <label>Họ và tên<input name="full_name" type="text" required autocomplete="name"></label>
                <label>Số điện thoại<input name="phone" type="tel" required autocomplete="tel"></label>
                <label>Email <small>(không bắt buộc)</small><input name="email" type="email" autocomplete="email"></label>
                <label>Số khách đi cùng<input name="guest_count" type="number" value="0" min="0" max="5"></label>
                <button type="submit">Gửi đăng ký <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection
