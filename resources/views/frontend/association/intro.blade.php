@extends('layouts.master')

@section('title', $intro->title.' | DNT Bắc Ninh')
@section('meta_description', $intro->summary ?: 'Thông tin giới thiệu về Hội Doanh nhân trẻ tỉnh Bắc Ninh.')

@section('content')
<section class="dnt-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 900px">
        <div class="dnt-about-page-heading">
            <h1 class="dnt-about-page-title">{{ $intro->title }}</h1>
            @if($intro->summary)<p>{{ $intro->summary }}</p>@endif
        </div>
        <div class="dnt-about-content">{!! $intro->content !!}</div>
        <p class="mt-8"><a class="dnt-text-link" href="{{ route('about') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại trang giới thiệu</a></p>
    </div>
</section>
@endsection
