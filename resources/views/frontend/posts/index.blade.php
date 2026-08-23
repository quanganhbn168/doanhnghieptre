@extends('layouts.master')

@section('title', 'Tin tức & hoạt động | DNT Bắc Ninh')
@section('meta_description', 'Tin tức, hoạt động và góc nhìn từ cộng đồng doanh nhân trẻ Bắc Ninh.')

@section('content')
<section class="dnt-page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><p>Tin tức & hoạt động</p><h1>Cập nhật từ DNT Bắc Ninh</h1><span>Những thông tin mới về doanh nghiệp, sự kiện và hoạt động kết nối.</span></div>
</section>
<section class="dnt-section dnt-section--soft">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        @if($newsItems->isNotEmpty())
            <div class="dnt-news-grid">
                @foreach($newsItems as $article)
                    <article class="dnt-news-card">
                        <a class="dnt-news-card__image" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}"><img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500"></a>
                        <div><span>{{ $article['category_name'] ?: 'Tin tức' }} · {{ $article['date'] }}</span><h2><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h2><p>{{ $article['excerpt'] }}</p></div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="dnt-empty-card"><i class="fa-regular fa-newspaper" aria-hidden="true"></i><div><h2>Chưa có tin tức được xuất bản</h2><p>Các bài viết được tạo trong Posts sẽ xuất hiện ở đây.</p></div></div>
        @endif
    </div>
</section>
@endsection
