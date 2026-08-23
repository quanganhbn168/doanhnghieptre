@extends('layouts.master')
@section('title', 'Dịch vụ truyền thông — '.$website['name'])
@section('meta_description', 'Các nhóm dịch vụ sản xuất hình ảnh, truyền thông, marketing, sự kiện và thương hiệu của THT Media.')
@php
    $serviceSchemaItems = $serviceGroups->flatten()->map(fn ($service): array => [
        'name' => $service->getTranslation('name', 'vi'),
        'url' => route('services.show', $service->getSlug('vi')),
    ])->all();
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::collection('Dịch vụ truyền thông', 'Các nhóm dịch vụ sản xuất hình ảnh, truyền thông, marketing, sự kiện và thương hiệu của THT Media.', $serviceSchemaItems, route('services.index'))])
@section('content')
<section class="page-hero"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>Dịch vụ truyền thông theo đúng mục tiêu doanh nghiệp</h1><p>Từ một sản phẩm hình ảnh đến một hệ thống nội dung đa nền tảng, THT Media xác định rõ phạm vi, đầu ra và cách mỗi hạng mục được sử dụng.</p></div></section>
<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Dịch vụ']]" />
@forelse($serviceGroups as $services)
@php($firstService = $services->first())
@php($groupTitle = $firstService?->category?->getTranslation('name', 'vi') ?: (\App\Models\Service::GROUPS[$firstService?->group] ?? 'Dịch vụ'))
<section class="section-space {{ $loop->even ? 'bg-soft' : '' }}"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading :title="$groupTitle" :href="$firstService?->category ? route('services.show', $firstService->category->getSlug('vi')) : null" link-text="Xem danh mục" /><div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">@foreach($services as $service)<div>@include('partials.frontend.service-card')</div>@endforeach</div></div></section>
@empty<section class="section-space"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="content-card text-center">Danh sách dịch vụ đang được cập nhật.</div></div></section>@endforelse
<section class="section-space bg-soft"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="conversion-panel"><div><h2>Hãy bắt đầu bằng mục tiêu, không phải bằng danh sách hạng mục</h2><p>THT Media sẽ cùng doanh nghiệp xác định nội dung cần sản xuất, kênh sử dụng và phạm vi phù hợp.</p></div><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('contact') }}">Nhận tư vấn giải pháp</a></div></div></section>
@endsection
