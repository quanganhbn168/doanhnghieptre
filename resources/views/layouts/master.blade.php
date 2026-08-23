<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DNT Bắc Ninh | Hội Doanh nhân trẻ tỉnh Bắc Ninh')</title>
    <meta name="description" content="@yield('meta_description', 'Nền tảng thông tin và kết nối của cộng đồng doanh nhân trẻ Bắc Ninh.')">
    <meta name="keywords" content="@yield('meta_keywords', 'DNT Bắc Ninh, doanh nhân trẻ, doanh nghiệp, sự kiện, giao thương')">
    <meta name="author" content="Hội Doanh nhân trẻ tỉnh Bắc Ninh">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="DNT Bắc Ninh">
    <meta property="og:title" content="@yield('title', 'DNT Bắc Ninh | Hội Doanh nhân trẻ tỉnh Bắc Ninh')">
    <meta property="og:description" content="@yield('meta_description', 'Nền tảng thông tin và kết nối của cộng đồng doanh nhân trẻ Bắc Ninh.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('seo_image', asset('assets/images/dnt/association-hero.png'))">
    <meta property="og:image:alt" content="@yield('title', 'DNT Bắc Ninh | Hội Doanh nhân trẻ tỉnh Bắc Ninh')">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'DNT Bắc Ninh | Hội Doanh nhân trẻ tỉnh Bắc Ninh')">
    <meta name="twitter:description" content="@yield('meta_description', 'Nền tảng thông tin và kết nối của cộng đồng doanh nhân trẻ Bắc Ninh.')">
    <meta name="twitter:image" content="@yield('seo_image', asset('assets/images/dnt/association-hero.png'))">

    <link rel="icon" href="{{ $siteAssets?->getFirstMediaUrl('favicon') }}">
    <link rel="apple-touch-icon" href="{{ $siteAssets?->getFirstMediaUrl('favicon') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('structured_data')
    @if($trackingSettings?->head_code)
        {!! $trackingSettings->head_code !!}
    @endif
    @if($trackingSettings?->google_analytics_code)
        {!! $trackingSettings->google_analytics_code !!}
    @endif
    @if($trackingSettings?->meta_pixel_code)
        {!! $trackingSettings->meta_pixel_code !!}
    @endif
    @stack('styles')
</head>
<body>
    @if($trackingSettings?->body_open_code)
        {!! $trackingSettings->body_open_code !!}
    @endif
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.floating-actions')
    @include('partials.popup')
    @stack('overlays')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const Toast = window.Swal?.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            @if (session('success'))
                Toast?.fire({ icon: 'success', title: @js(session('success')) });
            @endif

            @if (session('error'))
                Toast?.fire({ icon: 'error', title: @js(session('error')) });
            @endif

            @if ($errors->any())
                Toast?.fire({ icon: 'error', title: @js($errors->first()) });
            @endif
        });
    </script>
    @stack('scripts')
    @if($trackingSettings?->body_close_code)
        {!! $trackingSettings->body_close_code !!}
    @endif
</body>
</html>
