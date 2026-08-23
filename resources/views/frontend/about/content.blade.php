@extends('layouts.master')

@section('title', ($companyContent->getTranslation('seo_title', 'vi') ?: $contentTitle).' — '.$website['name'])
@section('meta_description', $companyContent->getTranslation('seo_description', 'vi') ?: $contentSummary)
@section('seo_image', $contentShareImage ?: $contentBanner ?: $contentImage)
@include('partials.frontend.structured-data', ['schema' => $contentSchema])

@section('content')
<x-frontend.breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('home')],
    ['label' => 'Giới thiệu', 'url' => route('about')],
    ['label' => $contentTitle],
]" />

<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <article class="w-full">
            <header class="mb-10">

                <h1 class="mt-3">{{ $contentTitle }}</h1>
                @if($contentSummary)<p class="mt-4 text-lg text-muted">{{ $contentSummary }}</p>@endif
            </header>

            @if($contentBanner)
                <figure class="mb-10 overflow-hidden rounded-2xl">
                    <img class="h-auto w-full object-cover" src="{{ $contentBanner }}" alt="{{ $contentTitle }}">
                </figure>
            @endif

            @if($contentImage)
                <figure class="mb-10 overflow-hidden rounded-2xl">
                    <img class="h-auto w-full object-cover" src="{{ $contentImage }}" alt="{{ $contentTitle }}">
                </figure>
            @endif

            @if(! empty($contentToc))
                <nav class="article-toc mb-10" aria-labelledby="company-content-toc-title">
                    <strong id="company-content-toc-title">Mục lục</strong>
                    <ol>
                        @foreach($contentToc as $tocItem)
                            <li class="article-toc__level-{{ $tocItem['level'] }}"><a href="#{{ $tocItem['id'] }}">{{ $tocItem['label'] }}</a></li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            <div class="content-copy news-article-content">
                {!! $contentBody ?: ($contentSummary ? '<p>'.e($contentSummary).'</p>' : '') !!}
            </div>

            <footer class="mt-10 border-t border-line pt-6">
                <a href="{{ route('about') }}"><i class="fa-solid fa-arrow-left mr-2"></i>Quay lại Giới thiệu</a>
            </footer>
        </article>
    </div>
</section>
@endsection
