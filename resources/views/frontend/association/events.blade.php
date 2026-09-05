@extends('layouts.master')
@section('title', 'Sự kiện | DNT Bắc Ninh')
@section('meta_description', 'Lịch sự kiện, chương trình kết nối và các hoạt động của Hội Doanh nhân trẻ tỉnh Bắc Ninh.')

@section('content')
<div class="dnt-publication">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-frontend.breadcrumb :items="$breadcrumbs" :contained="false" />
        <header class="dnt-publication-heading">
            <h1>Sự kiện của Hội</h1>
            <p>Lịch gặp gỡ, chia sẻ kinh nghiệm và kết nối cộng đồng doanh nghiệp hội viên.</p>
        </header>
        <div class="dnt-publication-layout">
            <section aria-label="Lịch sự kiện">
                <nav class="dnt-publication-tabs" aria-label="Thời gian sự kiện">
                    <a href="{{ route('events.index', array_filter(['q' => $search])) }}" @if($period === 'upcoming')aria-current="page"@endif>Sắp & đang diễn ra <span>{{ $upcomingCount }}</span></a>
                    <a href="{{ route('events.index', array_filter(['period' => 'past', 'q' => $search])) }}" @if($period === 'past')aria-current="page"@endif>Đã diễn ra <span>{{ $pastCount }}</span></a>
                </nav>
                <div class="dnt-publication-toolbar">
                    <p>{{ $events->total() }} sự kiện @if($search !== '') cho “{{ $search }}”@endif</p>
                    <form class="dnt-publication-search" role="search" method="GET" action="{{ route('events.index') }}">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <label class="sr-only" for="event-search">Tìm sự kiện</label>
                        <input id="event-search" name="q" type="search" value="{{ $search }}" placeholder="Tên sự kiện, địa điểm…" maxlength="100">
                        <button type="submit" aria-label="Tìm sự kiện"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                    </form>
                </div>
                <div class="dnt-publication-list">
                    @forelse($events as $item)
                        <article class="dnt-event-entry">
                            <time class="dnt-event-date" datetime="{{ $item['event']->starts_at->toIso8601String() }}">
                                <strong>{{ $item['event']->starts_at->format('d') }}</strong>
                                <span>Tháng {{ $item['event']->starts_at->format('m') }}</span>
                                <span>{{ $item['event']->starts_at->format('Y') }}</span>
                            </time>
                            <div class="dnt-event-entry__body">
                                <h2><a href="{{ route('events.show', $item['event']->slug) }}">{{ $item['event']->title }}</a></h2>
                                <div class="dnt-publication-meta">
                                    <span class="dnt-event-phase">{{ $item['phaseLabel'] }}</span>
                                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $item['event']->starts_at->format('H:i · d/m/Y') }}</span>
                                </div>
                                @if($item['event']->venue_name || $item['event']->venue_address)<p class="dnt-event-venue"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $item['event']->venue_name ?: $item['event']->venue_address }}</p>@endif
                                @if($item['event']->summary)<p>{{ $item['event']->summary }}</p>@endif
                                <div class="dnt-event-entry__actions">
                                    <a class="dnt-publication-link" href="{{ route('events.show', $item['event']->slug) }}">Thông tin chương trình <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                                    @if($item['registrationIsOpen'])<a class="dnt-publication-button dnt-publication-button--outline" href="{{ route('events.show', $item['event']->slug) }}#dang-ky">Đăng ký tham dự</a>@endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="dnt-publication-empty">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <h2>{{ $search !== '' ? 'Không tìm thấy sự kiện phù hợp' : ($period === 'past' ? 'Chưa có sự kiện đã diễn ra' : 'Lịch sự kiện mới đang được cập nhật') }}</h2>
                            <p>{{ $search !== '' ? 'Anh/chị có thể thử từ khóa khác hoặc xem lịch sự kiện đầy đủ.' : 'Theo dõi trang để cập nhật các chương trình của Hội.' }}</p>
                            <a class="dnt-publication-link" href="{{ route('events.index', $search !== '' ? ['period' => $period] : ['period' => $period === 'past' ? 'upcoming' : 'past']) }}">{{ $search !== '' ? 'Bỏ từ khóa tìm kiếm' : ($period === 'past' ? 'Xem lịch sắp diễn ra' : 'Xem sự kiện đã diễn ra') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    @endforelse
                </div>
                <x-frontend.publication-pagination :paginator="$events" />
            </section>
            <aside class="dnt-publication-sidebar" aria-label="Thông tin tham dự">
                <section class="dnt-publication-side-section">
                    <h2>Tham dự cùng Hội</h2>
                    <p>Mỗi chương trình có thông tin thời gian, địa điểm và thời hạn đăng ký riêng.</p>
                    <ol class="dnt-publication-steps">
                        <li>Chọn chương trình anh/chị quan tâm.</li>
                        <li>Xem nội dung và thông tin tham dự.</li>
                        <li>Đăng ký tại trang sự kiện khi mở nhận đăng ký.</li>
                    </ol>
                </section>
                <section class="dnt-publication-side-section">
                    <h2>Thông tin từ Hội</h2>
                    <p>Tin hoạt động, thông tin chi hội và những câu chuyện từ cộng đồng doanh nghiệp.</p>
                    <a class="dnt-publication-link" href="{{ route('news.index') }}">Đọc tin tức <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </section>
                <section class="dnt-publication-side-section">
                    <h2>Liên hệ Ban tổ chức</h2>
                    <p>Liên hệ Hội khi cần trao đổi thêm về chương trình hoặc thông tin tham dự.</p>
                    <a class="dnt-publication-link" href="{{ route('contact') }}">Thông tin liên hệ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
