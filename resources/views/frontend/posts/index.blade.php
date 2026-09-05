@extends('layouts.master')
@section('title', $pageTitle.' | DNT Bắc Ninh')
@section('meta_description', strip_tags($pageLead))

@section('content')
<div class="dnt-publication">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-frontend.breadcrumb :items="$breadcrumbs" :contained="false" />
        <header class="dnt-publication-heading">
            <h1>{{ $pageTitle }}</h1>
            <p>{{ strip_tags($pageLead) }}</p>
        </header>
        <div class="dnt-publication-layout">
            <section aria-label="Danh sách tin tức">
                <details class="dnt-publication-category-picker">
                    <summary>Chọn chuyên mục</summary>
                    @include('partials.frontend.publication-categories')
                </details>
                <div class="dnt-publication-toolbar">
                    <p>{{ $newsItems->total() }} bài viết @if($search !== '') cho “{{ $search }}”@endif</p>
                    <form class="dnt-publication-search" role="search" action="{{ url()->current() }}" method="GET">
                        <label class="sr-only" for="news-search">Tìm trong tin tức</label>
                        <input id="news-search" name="q" type="search" value="{{ $search }}" placeholder="Tìm trong tin tức…" maxlength="100">
                        <button type="submit" aria-label="Tìm tin tức"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                    </form>
                </div>
                <div class="dnt-publication-list">
                    @forelse($newsItems as $article)
                        <article class="dnt-news-entry {{ $article['has_image'] ? '' : 'dnt-news-entry--text' }}">
                            @if($article['has_image'])
                                <a class="dnt-news-entry__image" href="{{ $article['url'] }}" tabindex="-1" aria-hidden="true"><img src="{{ $article['image'] }}" alt="" loading="lazy" width="640" height="400"></a>
                            @endif
                            <div class="dnt-news-entry__body">
                                <h2><a href="{{ $article['url'] }}">{{ $article['title'] }}</a></h2>
                                <div class="dnt-publication-meta">
                                    <time datetime="{{ $article['published_at'] }}">{{ $article['date'] }}</time>
                                    @if($article['category_url'])<a href="{{ $article['category_url'] }}">{{ $article['category_name'] }}</a>@endif
                                </div>
                                @if($article['excerpt'])<p>{{ $article['excerpt'] }}</p>@endif
                                <a class="dnt-publication-link" href="{{ $article['url'] }}" aria-label="Đọc bài: {{ $article['title'] }}">Đọc bài viết <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @empty
                        <div class="dnt-publication-empty">
                            <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                            <h2>{{ $search !== '' ? 'Không tìm thấy bài viết phù hợp' : 'Chưa có bài viết trong mục này' }}</h2>
                            <p>{{ $search !== '' ? 'Anh/chị có thể thử từ khóa khác hoặc xem lại toàn bộ tin tức.' : 'Các thông tin mới từ Hội sẽ được cập nhật tại đây.' }}</p>
                            @if($search !== '' || $currentCategory)<a class="dnt-publication-link" href="{{ route('news.index') }}">Xem tất cả tin tức <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>@endif
                        </div>
                    @endforelse
                </div>
                <x-frontend.publication-pagination :paginator="$newsItems" />
            </section>
            <aside class="dnt-publication-sidebar" aria-label="Chuyên mục và hoạt động Hội">
                <section class="dnt-publication-side-section dnt-publication-desktop-categories">
                    <h2>Chuyên mục</h2>
                    @include('partials.frontend.publication-categories')
                </section>
                @if($upcomingEvents->isNotEmpty())
                    <section class="dnt-publication-side-section">
                        <h2>Lịch hoạt động</h2>
                        <div class="dnt-publication-side-list">
                            @foreach($upcomingEvents as $event)
                                <article>
                                    <h3><a href="{{ route('events.show', $event->slug) }}">{{ $event->title }}</a></h3>
                                    <p><time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('H:i · d/m/Y') }}</time></p>
                                </article>
                            @endforeach
                        </div>
                        <a class="dnt-publication-link" href="{{ route('events.index') }}">Xem lịch sự kiện <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </section>
                @endif
                @include('partials.frontend.publication-newsletter')
            </aside>
        </div>
    </div>
</div>
@endsection
