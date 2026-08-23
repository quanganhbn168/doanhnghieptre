@extends('layouts.master')

@section('title', $pageTitle . ' | DNT Bắc Ninh')
@section('meta_description', $pageLead)

@section('content')
<section class="dnt-page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><p>{{ $pageTitle }}</p><h1>{{ $pageTitle }} DNT Bắc Ninh</h1><span>{{ $pageLead }}</span></div>
</section>
<section class="dnt-section dnt-section--soft">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
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
                            <p>{{ $item->category_name ?: 'Doanh nghiệp' }}</p><h2>{{ $item->name }}</h2><span>{{ $item->province ?: 'Bắc Ninh' }}</span><div>{{ $item->summary ?: 'Thông tin đang được cập nhật.' }}</div>
                        @elseif($itemType === 'event')
                            <span class="dnt-directory-card__icon"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>
                            <p>{{ $item->date_day }} {{ $item->date_month }} {{ $item->date_year }}</p><h2>{{ $item->title }}</h2><span>{{ $item->venue_name ?: 'Đang cập nhật địa điểm' }}</span><div>{{ $item->summary ?: 'Thông tin chương trình đang được cập nhật.' }}</div>
                        @else
                            <span class="dnt-directory-card__icon"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span>
                            <p>Cơ hội giao thương</p><h2>{{ $item->title }}</h2><span>{{ $item->location_label ?: 'Đang cập nhật khu vực' }}</span><div>{{ $item->summary ?: $item->business_name ?: 'Thông tin giao thương đang được cập nhật.' }}</div>
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
