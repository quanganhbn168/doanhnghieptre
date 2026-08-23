@extends('layouts.master')

@section('title', ($category->getTranslation('seo_title', 'vi') ?: $categoryName).' — '.$website['name'])
@section('meta_description', $category->getTranslation('seo_description', 'vi') ?: $categoryDescription)
@php
    $categoryUrl = route('projects.show', $category->getSlug('vi'));
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::collection($categoryName, $categoryDescription, $projectSchemaItems, $categoryUrl)])

@section('content')
<section class="page-hero page-hero--portfolio">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <h1>{{ $categoryName }}</h1>
        @if($categoryDescription)<p>{{ $categoryDescription }}</p>@endif
    </div>
</section>

<x-frontend.breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('home')],
    ['label' => 'Dự án', 'url' => route('projects.index')],
    ['label' => $categoryName],
]" />

<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        @if($category->children->isNotEmpty())
            <div class="mb-6 flex flex-wrap gap-2" aria-label="Danh mục dự án liên quan">
                @foreach($category->children as $child)
                    <a class="inline-flex min-h-10 items-center justify-center gap-2 rounded-full border border-primary bg-transparent px-4 py-2 text-xs font-bold leading-tight text-primary shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('projects.show', $child->getSlug('vi')) }}">{{ $child->getTranslation('name', 'vi') }}</a>
                @endforeach
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse($projects as $project)
                <div>@include('partials.frontend.project-card')</div>
            @empty
                <div class="md:col-span-2 xl:col-span-3"><div class="empty-state"><i class="fa-solid fa-photo-film"></i><h2>Chưa có dự án phù hợp</h2><p>Danh mục này chưa có dự án hiển thị trên website.</p><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('contact') }}">Nhận tư vấn</a></div></div>
            @endforelse
        </div>

        @if($projects->hasPages())<div class="mt-5">{{ $projects->links() }}</div>@endif
    </div>
</section>
@endsection
