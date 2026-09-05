<nav class="dnt-publication-categories" aria-label="Chuyên mục tin tức">
    <a href="{{ route('news.index') }}" @if(!$currentCategory)aria-current="page"@endif><span>Tất cả tin tức</span><span>{{ $totalNews }}</span></a>
    @foreach($categories as $category)
        <a href="{{ route('news.show', $category->slug) }}" @if($currentCategory?->is($category))aria-current="page"@endif><span>{{ $category->getTranslation('name', app()->getLocale()) }}</span><span>{{ $category->posts_count }}</span></a>
    @endforeach
</nav>
