@extends('layouts.master')

@section('title', 'Bảng giá | THT Media')
@section('meta_description', 'Các gói dịch vụ truyền thông, sản xuất nội dung và marketing của THT Media.')
@section('canonical', route('pricing'))
@section('og_type', 'website')
@php
    $pricingSchemaPlans = $pricingPlans->map(fn ($plan): array => [
        'id' => $plan->id,
        'name' => $plan->name,
        'summary' => $plan->summary,
    ])->all();
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::pricing('Bảng giá dịch vụ', 'Các gói dịch vụ truyền thông, sản xuất nội dung và marketing của THT Media.', route('pricing'), $pricingSchemaPlans)])

@section('content')
<div class="page-shell pricing-page">
    <section class="page-hero">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            <h1>Chọn điểm bắt đầu cho kế hoạch truyền thông.</h1>
            <p>Phạm vi và ngân sách được chốt theo mục tiêu, bối cảnh và sản phẩm bàn giao thực tế.</p>
        </div>
    </section>

    <x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Bảng giá']]" />

    <section class="section-space">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            @forelse($pricingPlans as $plan)
                <article id="pricing-plan-{{ $plan->id }}" class="pricing-plan-card {{ $plan->is_featured ? 'pricing-plan-card--featured' : '' }}">
                    <div class="pricing-plan-card__content">

                        <h2>{{ $plan->name }}</h2>
                        @if($plan->summary)<p>{{ $plan->summary }}</p>@endif
                        <ul>@foreach($plan->features ?? [] as $feature)<li>{{ $feature }}</li>@endforeach</ul>
                    </div>
                    <div class="pricing-plan-card__aside"><strong>{{ $plan->price ?: 'Liên hệ' }}</strong>@if($plan->price_note)<span>{{ $plan->price_note }}</span>@endif<a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white transition hover:bg-primary-hover" href="{{ route('contact') }}">Trao đổi gói này <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
                </article>
            @empty
                <div class="empty-state"><i class="fa-solid fa-tags" aria-hidden="true"></i><h2>Bảng giá đang được cập nhật</h2><p>Vui lòng liên hệ để nhận phương án phù hợp.</p><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white" href="{{ route('contact') }}">Liên hệ tư vấn</a></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
