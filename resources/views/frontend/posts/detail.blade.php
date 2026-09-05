@extends('layouts.master')
@section('title', $article['seo_title'] ?: $article['title'].' | '.$website['name'])
@section('meta_description', $article['seo_description'] ?: $article['excerpt'])
@section('meta_keywords', $article['seo_keywords'] ?: '')
@section('og_type', 'article')
@if($article['has_image'])@section('seo_image', $article['image'])@endif
@include('partials.frontend.structured-data', ['schema' => $articleSchema])

@section('content')
<div class="dnt-publication">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-frontend.breadcrumb :items="$breadcrumbs" :contained="false" />
        <div class="dnt-publication-layout dnt-publication-layout--detail">
            <article class="dnt-publication-article">
                <header class="dnt-publication-heading dnt-publication-heading--article">
                    <h1>{{ $article['title'] }}</h1>
                    <div class="dnt-publication-meta">
                        <time datetime="{{ $article['published_at'] }}">{{ $article['date'] }}</time>
                        @if($article['category_url'])<a href="{{ $article['category_url'] }}">{{ $article['category_name'] }}</a>@endif
                    </div>
                    @if($article['excerpt'])<p class="dnt-publication-intro">{{ $article['excerpt'] }}</p>@endif
                </header>
                @if($article['has_image'])
                    <figure class="dnt-publication-cover"><img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" width="1200" height="750"></figure>
                @endif
                @if(!empty($article['toc']))
                    <nav class="dnt-publication-toc" aria-labelledby="article-toc-title">
                        <h2 id="article-toc-title">Trong bài viết</h2>
                        <ol>
                            @foreach($article['toc'] as $tocItem)
                                <li @class(['dnt-publication-toc__sub' => $tocItem['level'] === 3])><a href="#{{ $tocItem['id'] }}">{{ $tocItem['label'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                @endif
                <div class="news-article-content dnt-publication-content">{!! $article['content'] !!}</div>
                <footer class="dnt-publication-article-footer"><a class="dnt-publication-link" href="{{ route('news.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Tất cả tin tức</a></footer>
                @include('partials.frontend.comments', ['commentable' => $commentable, 'commentableType' => 'post', 'comments' => $comments])
            </article>
            <aside class="dnt-publication-sidebar" aria-label="Tin tức liên quan">
                @if($relatedNews->isNotEmpty())
                    <section class="dnt-publication-side-section">
                        <h2>Tin mới từ Hội</h2>
                        <div class="dnt-publication-side-list">
                            @foreach($relatedNews as $related)
                                <article><h3><a href="{{ $related['url'] }}">{{ $related['title'] }}</a></h3><p><time datetime="{{ $related['published_at'] }}">{{ $related['date'] }}</time></p></article>
                            @endforeach
                        </div>
                        <a class="dnt-publication-link" href="{{ route('news.index') }}">Xem tất cả tin <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </section>
                @endif
                <section class="dnt-publication-side-section">
                    <h2>Hoạt động của Hội</h2>
                    <p>Theo dõi lịch chương trình và đăng ký tham dự các hoạt động kết nối doanh nghiệp.</p>
                    <a class="dnt-publication-link" href="{{ route('events.index') }}">Lịch sự kiện <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </section>
                @include('partials.frontend.publication-newsletter')
            </aside>
        </div>
    </div>
</div>
@endsection
