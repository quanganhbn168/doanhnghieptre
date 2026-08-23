@extends('layouts.master')

@section('title', 'Giới thiệu | DNT Bắc Ninh')
@section('meta_description', 'Các nội dung giới thiệu về Hội Doanh nhân trẻ tỉnh Bắc Ninh.')

@section('content')
<section class="dnt-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="dnt-about-page-heading">
            <h1 class="dnt-about-page-title">Giới thiệu</h1>
            <p>Các thông tin về Hội Doanh nhân trẻ tỉnh Bắc Ninh.</p>
        </div>
        <div class="dnt-intro-list">
            @forelse($intros as $intro)
                <article class="dnt-intro-list__item">
                    <h2><a href="{{ route('about.show', ['slug' => $intro->slug]) }}">{{ $intro->title }}</a></h2>
                    @if($intro->summary)<p>{{ $intro->summary }}</p>@endif
                    <a class="dnt-text-link" href="{{ route('about.show', ['slug' => $intro->slug]) }}">Xem chi tiết <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </article>
            @empty
                <p class="dnt-empty-state">Nội dung giới thiệu đang được cập nhật.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
