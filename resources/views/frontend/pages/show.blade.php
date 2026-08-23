@extends('layouts.master')

@section('title', $page->getTranslation('seo_title', 'vi') ?: $page->getTranslation('name', 'vi'))
@section('meta_description', $page->getTranslation('seo_description', 'vi') ?: $page->getTranslation('sub_title', 'vi') ?: '')
@section('meta_keywords', $page->getTranslation('seo_keywords', 'vi') ?: '')
@php
    $pageName = $page->getTranslation('name', 'vi');
    $pageDescription = $page->getTranslation('seo_description', 'vi') ?: $page->getTranslation('sub_title', 'vi') ?: '';
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::webPage($pageName, $pageDescription, url()->current())])

@section('content')
    <section class="page-hero"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>{{ $pageName }}</h1>@if($pageDescription)<p>{{ $pageDescription }}</p>@endif</div></section>
    <x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => $pageName]]" />

    <section class="section-space">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <article class="content-card">
                <div class="page-content">{!! $page->getTranslation('content', 'vi') !!}</div>
            </article>
        </div>
    </section>
@endsection
