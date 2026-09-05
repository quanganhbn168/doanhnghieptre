@extends('layouts.master')
@section('title', $event->title.' | Sự kiện | DNT Bắc Ninh')
@section('meta_description', $event->summary ?: 'Thông tin sự kiện Hội Doanh nhân trẻ tỉnh Bắc Ninh.')
@push('styles')
<style>
.dnt-event-detail{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:1.5rem;align-items:start}.dnt-event-detail article,.dnt-event-detail aside{padding:clamp(1.25rem,3vw,2rem);background:var(--dnt-surface);border:1px solid var(--dnt-line);border-radius:.5rem;min-width:0}.dnt-event-detail h2{font-size:1.25rem;margin:0 0 1rem}.dnt-event-detail__meta{display:grid;gap:.5rem;margin:0 0 1.5rem;color:var(--dnt-muted)}.dnt-event-detail form,.dnt-event-detail label{display:grid;gap:.5rem}.dnt-event-detail form{gap:1rem}.dnt-event-detail input{width:100%;padding:.65rem .75rem;border:1px solid var(--dnt-line);border-radius:.25rem;font-size:1rem}.dnt-event-detail .dnt-about-content{overflow-wrap:anywhere}.dnt-event-feedback{padding:1rem;margin-bottom:1rem;background:var(--dnt-brand-red-soft);border-radius:.5rem}.dnt-event-detail__lead{margin-bottom:1.25rem;line-height:1.7}@media(max-width:767px){.dnt-event-detail{grid-template-columns:1fr}}
</style>
@endpush
@section('content')
<section class="dnt-page-hero"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>{{ $event->title }}</h1><span>{{ $event->starts_at->format('d/m/Y · H:i') }}@if($event->venue_name) · {{ $event->venue_name }}@endif</span></div></section>
<section class="dnt-section dnt-section--soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))<div class="dnt-event-feedback" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="dnt-event-feedback" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="dnt-event-detail">
        <article>
            <h2>Thông tin chương trình</h2>
            <div class="dnt-event-detail__meta">
                <p>Bắt đầu: {{ $event->starts_at->format('H:i · d/m/Y') }}@if($event->ends_at)<br>Kết thúc: {{ $event->ends_at->format('H:i · d/m/Y') }}@endif</p>
                @if($event->venue_address)<p>Địa chỉ: {{ $event->venue_address }}</p>@endif
            </div>
            @if($event->summary)<p class="dnt-event-detail__lead">{{ $event->summary }}</p>@endif
            <div class="dnt-about-content">{!! str($event->content ?? '')->sanitizeHtml() !!}</div>
        </article>
        <aside>
            <h2>Đăng ký tham dự</h2>
            @if($registrationIsOpen)
                @if($remainingSlots !== null)<p>Còn {{ $remainingSlots }} chỗ (bao gồm người đi cùng).</p>@endif
                @if($event->registration_closes_at)<p>Đăng ký trước {{ $event->registration_closes_at->format('H:i · d/m/Y') }}.</p>@endif
                <form method="POST" action="{{ route('events.register', $event->slug) }}">
                    @csrf
                    <label>Họ và tên<input name="full_name" autocomplete="name" required maxlength="255" value="{{ old('full_name', auth()->user()?->name) }}"></label>
                    <label>Số điện thoại<input name="phone" type="tel" autocomplete="tel" required maxlength="30" value="{{ old('phone', auth()->user()?->phone) }}"></label>
                    <label>Email<input name="email" type="email" autocomplete="email" maxlength="255" value="{{ old('email', auth()->user()?->email) }}"></label>
                    <label>Số người đi cùng<input name="guest_count" type="number" min="0" max="5" value="{{ old('guest_count', 0) }}"></label>
                    <button class="dnt-button dnt-button--dark" type="submit">Gửi đăng ký</button>
                </form>
            @else
                <p>{{ $remainingSlots === 0 ? 'Sự kiện đã đủ số lượng đăng ký.' : 'Đăng ký chưa mở hoặc đã kết thúc.' }}</p>
            @endif
        </aside>
    </div>
    <p class="mt-8"><a class="dnt-text-link" href="{{ route('events.index') }}">← Xem tất cả sự kiện</a></p>
</div></section>
@endsection
