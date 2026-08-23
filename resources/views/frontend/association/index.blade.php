@extends('layouts.master')

@section('title', $pageTitle . ' | DNT Bắc Ninh')
@section('meta_description', $pageLead)

@push('styles')
<style>.dnt-directory-card h2 a{color:inherit;text-decoration:none}.dnt-directory-card h2 a:hover{color:var(--dnt-brand-red)}</style>
@endpush

@section('content')
<section class="dnt-page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>{{ $pageTitle }} DNT Bắc Ninh</h1><span>{{ $pageLead }}</span></div>
</section>
<section class="dnt-section dnt-section--soft">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        @if($itemType === 'trade')
            <form class="dnt-directory-filter" method="GET" action="{{ route('trade.index') }}">
                <label><span>Loại cơ hội</span><select name="type"><option value="">Tất cả cơ hội</option>@foreach($tradeTypes as $value => $label)<option value="{{ $value }}" @selected($activeTradeType === $value)>{{ $label }}</option>@endforeach</select></label>
                <div class="dnt-directory-filter__actions"><button type="submit">Lọc cơ hội</button>@if($activeTradeType)<a href="{{ route('trade.index') }}">Xóa lọc</a>@endif</div>
                @auth
                    <a class="dnt-button dnt-button--dark" href="{{ auth()->user()->member?->status === 'approved' ? url('/thanh-vien/my-trade-posts/create') : route('account.dashboard') }}">Đăng cơ hội</a>
                @else
                    <a class="dnt-button dnt-button--dark" href="{{ route('register') }}">Đăng ký để đăng cơ hội</a>
                @endauth
            </form>
        @endif
        @if($items->isNotEmpty())
            <div class="dnt-directory-grid">
                @foreach($items as $item)
                    <article class="dnt-directory-card">
                        @if($itemType === 'business')
                            <div class="dnt-directory-card__logo">
                                @if($item->logo_url)
                                    <img src="{{ $item->logo_url }}" alt="Logo {{ $item->name }}" loading="lazy" width="160" height="96">
                                @else
                                    <span>Chưa có logo</span>
                                @endif
                            </div>
                            <h2>{{ $item->name }}</h2><span>{{ $item->province ?: 'Bắc Ninh' }}</span><div>{{ $item->summary ?: 'Thông tin đang được cập nhật.' }}</div>
                        @elseif($itemType === 'event')
                            <span class="dnt-directory-card__icon"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>
                            <h2>{{ $item->title }}</h2><span>{{ $item->date_day }} {{ $item->date_month }} {{ $item->date_year }} @if($item->venue_name) · {{ $item->venue_name }} @endif</span><div>{{ $item->summary ?: 'Thông tin chương trình đang được cập nhật.' }}</div>
                        @else
                            <h2><a href="{{ route('trade.show', $item->slug) }}">{{ $item->title }}</a></h2><span>{{ $item->business_name ?: 'Doanh nghiệp hội viên' }} · {{ $item->location_label ?: 'Đang cập nhật khu vực' }}</span><div>{{ $item->summary ?: 'Thông tin giao thương đang được cập nhật.' }}</div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="dnt-empty-card"><i class="fa-solid {{ $pageIcon }}" aria-hidden="true"></i><div><h2>{{ $pageTitle }} đang được cập nhật</h2><p>{{ $pageLead }}</p></div></div>
        @endif
    </div>
</section>
@endsection
