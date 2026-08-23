@extends('layouts.master')
@section('title', ($service->getTranslation('seo_title', 'vi') ?: $service->getTranslation('name', 'vi')).' — '.$website['name'])
@section('meta_description', $service->getTranslation('seo_description', 'vi') ?: $service->getTranslation('summary', 'vi') ?: '')
@section('meta_keywords', $service->getTranslation('seo_keywords', 'vi') ?: '')
@section('seo_image', $service->shareImage?->url ?: $service->banner?->url ?: $service->getFirstMediaUrl('share_image') ?: $service->getFirstMediaUrl('banner') ?: '')
@php
    $serviceName = $service->getTranslation('name', 'vi');
    $serviceSummary = $service->getTranslation('seo_description', 'vi') ?: $service->getTranslation('summary', 'vi') ?: '';
    $serviceUrl = route('services.show', $service->getSlug('vi'));
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::service($serviceName, $serviceSummary, $serviceUrl, $service->shareImage?->url ?: $service->banner?->url ?: $service->getFirstMediaUrl('share_image') ?: $service->getFirstMediaUrl('banner'))])
@if($faqSchema)
    @push('structured_data')
        <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush
@endif
@section('content')
@php
    $locale = app()->getLocale();
    $lists = collect(['problems','audiences','work_items','deliverables','benefits','process_steps'])->mapWithKeys(fn($field) => [$field => collect($service->getTranslation($field, $locale, false) ?: [])]);
    $faqs = collect($service->getTranslation('faqs', $locale, false) ?: []);
    $pricingHref = $pricingPlans->isNotEmpty() ? '#service-pricing' : route('pricing');
@endphp
<section class="detail-hero detail-hero--service">@if($banner = ($service->banner?->url ?: $service->getFirstMediaUrl('banner')))<img src="{{ $banner }}" alt="{{ $service->getTranslation('name', 'vi') }}">@endif<div class="container mx-auto px-4 sm:px-6 lg:px-8 detail-hero__content"><h1>{{ $service->getTranslation('name', 'vi') }}</h1><p>{{ $service->getTranslation('summary', 'vi') }}</p><div class="flex flex-wrap gap-2"><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="#nhan-tu-van">Nhận tư vấn dịch vụ</a><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-white px-5 py-3 text-sm font-bold leading-tight text-ink shadow-sm transition hover:-translate-y-px hover:bg-primary-soft hover:text-primary" href="{{ $pricingHref }}">Xem bảng giá</a>@if($service->video_url)<a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-white px-5 py-3 text-sm font-bold leading-tight text-ink shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ $service->video_url }}"><i class="fa-solid fa-circle-play mr-1"></i>Xem video</a>@endif</div></div></section>
<x-frontend.breadcrumb :items="array_values(array_filter([
    ['label' => 'Trang chủ', 'url' => route('home')],
    ['label' => 'Dịch vụ', 'url' => route('services.index')],
    $service->category ? ['label' => $service->category->getTranslation('name', 'vi'), 'url' => route('services.show', $service->category->getSlug('vi'))] : null,
    ['label' => $serviceName],
]))" />

<section class="section-space"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="grid items-start gap-12 lg:grid-cols-12"><div class="lg:col-span-7"><h2 class="text-4xl font-bold">Từ nhu cầu truyền thông đến sản phẩm có thể sử dụng thực tế</h2><div class="content-copy">{!! $service->getTranslation('intro', 'vi') !!}</div></div><div class="lg:col-span-5">@if($lists['problems']->isNotEmpty())<div class="insight-panel"><h2>Vấn đề thường gặp</h2><ul class="check-list">@foreach($lists['problems'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>@endif</div></div></div></section>

@if($lists['audiences']->isNotEmpty())<section class="section-space bg-soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Dịch vụ phù hợp với ai?" /><div class="pill-grid">@foreach($lists['audiences'] as $item)<span><i class="fa-solid fa-building-circle-check"></i>{{ $item }}</span>@endforeach</div></div></section>@endif

@if($lists['work_items']->isNotEmpty() || $lists['deliverables']->isNotEmpty())<section class="section-space"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="grid gap-6 md:grid-cols-2"><div><div class="scope-card h-full"><span class="scope-card__number">01</span><h2>Hạng mục thực hiện</h2><ul class="check-list">@foreach($lists['work_items'] as $item)<li>{{ $item }}</li>@endforeach</ul></div></div><div><div class="scope-card h-full"><span class="scope-card__number">02</span><h2>Sản phẩm bàn giao</h2><ul class="check-list">@foreach($lists['deliverables'] as $item)<li>{{ $item }}</li>@endforeach</ul></div></div></div></div></section>@endif

@if($lists['benefits']->isNotEmpty())<section class="section-space bg-soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Giá trị doanh nghiệp nhận được" /><div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">@foreach($lists['benefits'] as $item)<div><div class="benefit-item"><i class="fa-solid fa-circle-arrow-up-right"></i><span>{{ $item }}</span></div></div>@endforeach</div></div></section>@endif

@if($lists['process_steps']->isNotEmpty())<section class="section-space"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Quy trình triển khai rõ ràng" /><div class="process-grid">@foreach($lists['process_steps'] as $step)<article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $step }}</h3></article>@endforeach</div></div></section>@endif

@if($pricingPlans->isNotEmpty())<section class="section-space bg-soft" id="service-pricing"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Bảng giá tham khảo" :href="route('pricing')" linkText="Xem toàn bộ bảng giá" :center="false" /><div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">@foreach($pricingPlans as $plan)<article class="home-pricing-card {{ $plan->is_featured ? 'home-pricing-card--featured' : '' }}"><h3>{{ $plan->name }}</h3>@if($plan->summary)<p>{{ $plan->summary }}</p>@endif<div class="home-pricing-card__price">{{ $plan->price ?: 'Liên hệ' }}</div>@if($plan->price_note)<small class="home-pricing-card__price-note">{{ $plan->price_note }}</small>@endif<ul>@foreach($plan->features ?? [] as $feature)<li>{{ $feature }}</li>@endforeach</ul><a class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-transparent px-5 py-3 text-sm font-bold leading-tight transition hover:-translate-y-px {{ $plan->is_featured ? 'bg-white text-primary hover:bg-primary-soft hover:text-primary' : 'bg-primary text-white hover:bg-primary-hover' }}" href="{{ route('contact', ['service' => $service->id]) }}">Trao đổi gói này <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>@endforeach</div></div></section>@endif

@if($projects->isNotEmpty())<section class="section-space bg-soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Dự án liên quan" :href="route('projects.index', ['service' => $service->slug])" /><div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach($projects as $project)<div>@include('partials.frontend.project-card')</div>@endforeach</div></div></section>@endif

@if($faqs->isNotEmpty())<section class="section-space"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="grid gap-12 lg:grid-cols-12"><div class="lg:col-span-4"><h2 class="font-bold">Thông tin cần làm rõ trước khi bắt đầu</h2></div><div class="lg:col-span-8"><div class="faq-list" id="serviceFaq">@foreach($faqs as $faq)<div class="faq-item"><h3 class="faq-heading"><button class="faq-button {{ $loop->first ? 'is-open' : '' }}" data-faq-toggle="faq-{{ $loop->iteration }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">{{ $faq['question'] }}<i class="fa-solid fa-chevron-down"></i></button></h3><div id="faq-{{ $loop->iteration }}" class="faq-panel {{ $loop->first ? 'is-open' : '' }}"><div class="faq-body">{{ $faq['answer'] }}</div></div></div>@endforeach</div></div></div></div></section>@endif

@if($service->relatedServices->isNotEmpty())<section class="section-space bg-soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Dịch vụ có thể kết hợp" /><div class="grid gap-6 md:grid-cols-3">@foreach($service->relatedServices as $relatedService)<div>@include('partials.frontend.service-card', ['service' => $relatedService])</div>@endforeach</div></div></section>@endif

<section class="section-space" id="nhan-tu-van"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="consultation-panel"><div class="consultation-panel__intro"><h2>Trao đổi mục tiêu trước khi chốt hạng mục</h2><p>Cho THT Media biết doanh nghiệp cần đạt điều gì, thời gian triển khai và kênh sử dụng. Đội ngũ sẽ đề xuất phạm vi phù hợp.</p>@if($website['phone'])<a class="contact-line" href="tel:{{ preg_replace('/[^0-9+]/', '', $website['phone']) }}"><i class="fa-solid fa-phone"></i>{{ $website['phone'] }}</a>@endif</div><div>@include('partials.frontend.consultation-form', ['selectedServiceId' => $service->id, 'formId' => 'service'])</div></div></div></section>
@endsection
