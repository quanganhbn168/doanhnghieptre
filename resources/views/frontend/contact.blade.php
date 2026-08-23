@extends('layouts.master')

@section('title', 'Liên hệ tư vấn — ' . $website['name'])
@section('meta_description', 'Gửi yêu cầu tư vấn dịch vụ truyền thông, sản xuất hình ảnh, sự kiện và thương hiệu tới THT Media.')
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::webPage('Liên hệ tư vấn', 'Gửi yêu cầu tư vấn dịch vụ truyền thông tới THT Media.', route('contact'))])

@section('content')
<section class="page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>Cùng làm rõ nhu cầu trước khi bắt đầu</h1><p>Chia sẻ mục tiêu, phạm vi và thời gian dự kiến. THT Media sẽ liên hệ để trao đổi hướng triển khai phù hợp.</p></div>
</section>

<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Liên hệ']]" />

<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 xl:gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <aside class="contact-info-card h-full">
                    @if($siteAssets?->getFirstMediaUrl('logo_footer') ?: $siteAssets?->getFirstMediaUrl('logo'))
                        <img class="footer-logo mb-4" src="{{ $siteAssets->getFirstMediaUrl('logo_footer') ?: $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                    @else
                        <div class="mb-6 text-2xl font-extrabold text-white">THT MEDIA VN</div>
                    @endif
                    <h2 class="text-lg font-bold">{{ $website['company'] }}</h2>
                    <p class="text-white/60">Đầu mối tiếp nhận yêu cầu tư vấn, báo giá và hợp tác truyền thông.</p>
                    @if($website['address'])<div class="contact-info-row"><i class="fa-solid fa-location-dot"></i><span>{{ $website['address'] }}</span></div>@endif
                    @php($contactPhones = $website['phones'] ?? [])
                    @if(empty($contactPhones) && $website['phone'])
                        @php($contactPhones = [['label' => 'Điện thoại', 'number' => $website['phone']]])
                    @endif
                    @foreach($contactPhones as $contactPhone)
                        @if(filled($contactPhone['number'] ?? null))<div class="contact-info-row"><i class="fa-solid fa-phone"></i><span>{{ $contactPhone['label'] ?? 'Điện thoại' }}: </span><a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $contactPhone['number']) }}">{{ $contactPhone['number'] }}</a></div>@endif
                    @endforeach
                    @if($website['email'])<div class="contact-info-row"><i class="fa-solid fa-envelope"></i><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a></div>@endif
                    @if($contactSettings?->working_hours)<div class="contact-info-row"><i class="fa-solid fa-clock"></i><span>{{ $contactSettings->working_hours }}</span></div>@endif
                    <div class="social-links mt-4">@foreach(['facebook', 'instagram', 'youtube', 'tiktok'] as $network)@if($website['social'][$network] ?? null)<a class="social-link" href="{{ $website['social'][$network] }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"><i class="fa-brands fa-{{ $network }}"></i></a>@endif @endforeach</div>
                </aside>
            </div>
            <div class="lg:col-span-8">
                <div class="content-card consultation-card">

                    <h2>Chúng tôi có thể hỗ trợ gì?</h2>
                    <p class="text-muted">Các trường về ngân sách và thời gian giúp đội ngũ chuẩn bị cuộc trao đổi sát hơn; anh/chị có thể để trống nếu chưa xác định.</p>
                    @if(session('success'))<div class="ui-alert ui-alert--success">{{ session('success') }}</div>@endif
                    @if($errors->any())<div class="ui-alert ui-alert--error">{{ $errors->first() }}</div>@endif
                    @include('partials.frontend.consultation-form', ['extended' => true, 'formId' => 'contact', 'selectedServiceId' => request('service')])
                </div>
            </div>
            @if(! empty($website['branches'] ?? []))
                <div class="lg:col-span-12">
                    <div class="content-card">

                        <h2>Chi nhánh</h2>
                        <div class="grid gap-6 md:grid-cols-2">
                            @foreach($website['branches'] as $branch)
                                <div>
                                    <article class="h-full rounded-2xl border border-line p-6">
                                        <h3 class="text-base font-bold">{{ $branch['name'] }}</h3>
                                        <div class="contact-info-row"><i class="fa-solid fa-location-dot"></i><span>{{ $branch['address'] }}</span></div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <div class="lg:col-span-12">
                @php($mapEmbedUrl = \App\Support\MapEmbed::url($contactSettings?->map_embed))
                @if($mapEmbedUrl)<div class="contact-map-embed"><iframe src="{{ $mapEmbedUrl }}" title="Bản đồ vị trí {{ $website['name'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>@endif
            </div>
        </div>
    </div>
</section>
@endsection
