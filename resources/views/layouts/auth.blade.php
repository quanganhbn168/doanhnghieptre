<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tài khoản hội viên | DNT Bắc Ninh')</title>
    @php($authFavicon = $siteAssets?->getFirstMediaUrl('favicon'))
    @if($authFavicon)
        <link rel="icon" href="{{ $authFavicon }}">
        <link rel="apple-touch-icon" href="{{ $authFavicon }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php($authBackground = $siteAssets?->getFirstMediaUrl('auth_background'))
<body class="dnt-auth-page" @if($authBackground) style="--dnt-auth-background: url('{{ $authBackground }}')" @endif>
    <div class="dnt-auth-page__scene" aria-hidden="true"></div>
    <main class="dnt-auth-page__main">
        <section class="dnt-auth-card" aria-labelledby="dnt-auth-title">
            <a class="dnt-auth-card__brand" href="{{ route('home') }}" aria-label="DNT Bắc Ninh - Trang chủ">
                @if($siteAssets?->getFirstMediaUrl('logo'))
                    <img src="{{ $siteAssets->getFirstMediaUrl('logo') }}" alt="Hội Doanh nhân trẻ tỉnh Bắc Ninh" width="395" height="100">
                @else
                    <strong>DNT BẮC NINH</strong>
                @endif
            </a>
            @yield('content')
        </section>
    </main>
</body>
</html>
