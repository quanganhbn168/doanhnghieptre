@extends('layouts.master')
@section('title', $event->title.' | Sự kiện | DNT Bắc Ninh')
@section('meta_description', $event->summary ?: 'Thông tin sự kiện Hội Doanh nhân trẻ tỉnh Bắc Ninh.')

@section('content')
<div class="dnt-publication">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-frontend.breadcrumb :items="$breadcrumbs" :contained="false" />
        @if(session('success'))<div class="dnt-publication-feedback" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="dnt-publication-feedback" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="dnt-publication-layout dnt-publication-layout--detail">
            <article class="dnt-publication-article">
                <header class="dnt-publication-heading dnt-publication-heading--article">
                    <h1>{{ $event->title }}</h1>
                    <div class="dnt-publication-meta">
                        <span class="dnt-event-phase">{{ $phaseLabel }}</span>
                        <time datetime="{{ $event->starts_at->toIso8601String() }}"><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $event->starts_at->format('d/m/Y') }}</time>
                    </div>
                    @if($event->summary)<p class="dnt-publication-intro">{{ $event->summary }}</p>@endif
                </header>
                <section class="dnt-event-facts" aria-labelledby="event-information-title">
                    <h2 id="event-information-title">Thông tin chương trình</h2>
                    <dl>
                        <div><dt><i class="fa-regular fa-clock" aria-hidden="true"></i> Thời gian</dt><dd><time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('H:i · d/m/Y') }}</time>@if($event->ends_at)<br>Đến <time datetime="{{ $event->ends_at->toIso8601String() }}">{{ $event->ends_at->format('H:i · d/m/Y') }}</time>@endif</dd></div>
                        <div><dt><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Địa điểm</dt><dd>{{ $event->venue_name ?: ($event->venue_address ? '' : 'Đang cập nhật') }}@if($event->venue_address)<span>{{ $event->venue_address }}</span>@endif</dd></div>
                    </dl>
                </section>
                <div class="news-article-content dnt-publication-content">{!! str($event->content ?? '')->sanitizeHtml() !!}</div>
                <footer class="dnt-publication-article-footer"><a class="dnt-publication-link" href="{{ route('events.index', $phase === 'past' ? ['period' => 'past'] : []) }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Xem tất cả sự kiện</a></footer>
            </article>
            <aside class="dnt-publication-sidebar" aria-label="Đăng ký tham dự sự kiện">
                <section class="dnt-publication-side-section dnt-event-registration" id="dang-ky">
                    <h2>Đăng ký tham dự</h2>
                    <p>{{ $registrationMessage }}</p>
                    @if($registrationIsOpen)
                        <div class="dnt-event-registration__notes">
                            @if($remainingSlots !== null)<p>Còn <strong>{{ $remainingSlots }} chỗ</strong>, bao gồm người đi cùng.</p>@endif
                            @if($event->registration_closes_at)<p>Hạn đăng ký: <strong>{{ $event->registration_closes_at->format('H:i · d/m/Y') }}</strong>.</p>@endif
                        </div>
                        <form class="dnt-publication-form" method="POST" action="{{ route('events.register', $event->slug) }}">
                            @csrf
                            <label>Họ và tên <span aria-hidden="true">*</span><input name="full_name" autocomplete="name" required maxlength="255" value="{{ old('full_name', auth()->user()?->name) }}"></label>
                            <label>Số điện thoại <span aria-hidden="true">*</span><input name="phone" type="tel" autocomplete="tel" required maxlength="30" value="{{ old('phone', auth()->user()?->phone) }}"></label>
                            <label>Email <span class="dnt-publication-optional">(không bắt buộc)</span><input name="email" type="email" autocomplete="email" maxlength="255" value="{{ old('email', auth()->user()?->email) }}"></label>
                            <label>Số người đi cùng<input name="guest_count" type="number" min="0" max="5" value="{{ old('guest_count', 0) }}" aria-describedby="event-guest-help"></label>
                            <p id="event-guest-help" class="dnt-publication-help">Không tính người đăng ký. Tối đa 5 người đi cùng.</p>
                            <button class="dnt-publication-button" type="submit">Gửi đăng ký</button>
                        </form>
                    @else
                        <a class="dnt-publication-link" href="{{ route('events.index') }}">Xem lịch sự kiện <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </section>
                <section class="dnt-publication-side-section">
                    <h2>Cần thêm thông tin?</h2>
                    <p>Liên hệ Hội để được hỗ trợ về chương trình và thông tin tham dự.</p>
                    <a class="dnt-publication-link" href="{{ route('contact') }}">Thông tin liên hệ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
