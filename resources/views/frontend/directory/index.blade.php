@extends('layouts.master')

@section('title', 'Danh bạ doanh nghiệp | DNT Bắc Ninh')
@section('meta_description', 'Tra cứu doanh nghiệp hội viên theo khối ngành nghề, quy mô và chi hội tại DNT Bắc Ninh.')

@section('content')
<div class="dnt-publication dnt-member-directory">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-frontend.breadcrumb :items="$breadcrumbs" :contained="false" />
        <header class="dnt-publication-heading dnt-member-directory__heading">
            <div>
                <h1>Danh bạ doanh nghiệp</h1>
                <p>Tìm hiểu doanh nghiệp hội viên và kết nối theo lĩnh vực, quy mô, Chi hội.</p>
            </div>
            <a class="dnt-publication-link" href="{{ route('membership.create') }}">Đăng ký hội viên <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </header>

        <form class="dnt-member-directory__filter" method="GET" action="{{ route('directory.index') }}" role="search" aria-label="Tra cứu doanh nghiệp">
            <div class="dnt-member-directory__search">
                <label for="directory-search" class="sr-only">Tên doanh nghiệp hoặc ngành nghề</label>
                <div><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="directory-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Tên doanh nghiệp, ngành nghề…" maxlength="100"></div>
            </div>
            <div class="dnt-member-directory__filter-options">
                <label for="directory-industry">Khối ngành nghề
                    <select id="directory-industry" name="industry">
                        <option value="">Tất cả khối ngành nghề</option>
                        @foreach($industries as $industry)
                            <option value="{{ $industry->slug }}" @selected(($filters['industry'] ?? '') === $industry->slug)>{{ $industry->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label for="directory-chapter">Chi hội
                    <select id="directory-chapter" name="chapter">
                        <option value="">Tất cả Chi hội</option>
                        @foreach($chapters as $chapter)
                            <option value="{{ $chapter->slug }}" @selected(($filters['chapter'] ?? '') === $chapter->slug)>{{ $chapter->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label for="directory-size">Quy mô doanh nghiệp
                    <select id="directory-size" name="size">
                        <option value="">Tất cả quy mô</option>
                        @foreach($sizeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['size'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <button class="dnt-button dnt-button--dark" type="submit">Tìm doanh nghiệp</button>
        </form>

        @if($activeFilters)
            <nav class="dnt-member-directory__active-filters" aria-label="Bộ lọc đang áp dụng">
                @foreach($activeFilters as $filter)
                    <a href="{{ $filter['url'] }}" aria-label="Bỏ lọc {{ $filter['label'] }}"><span>{{ $filter['label'] }}</span><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                @endforeach
                <a class="dnt-member-directory__clear" href="{{ route('directory.index') }}">Xóa tất cả bộ lọc</a>
            </nav>
        @endif

        <section aria-labelledby="directory-results-heading">
            <div class="dnt-member-directory__results-heading">
                <h2 id="directory-results-heading">{{ $activeFilters ? 'Kết quả tìm kiếm' : 'Doanh nghiệp hội viên' }} <span>{{ number_format($businesses->total()) }}</span></h2>
                @if($businesses->isNotEmpty())<p>Hiển thị {{ $businesses->firstItem() }}–{{ $businesses->lastItem() }} trong {{ number_format($businesses->total()) }} doanh nghiệp</p>@endif
            </div>

            @if($businesses->isNotEmpty())
                <div class="dnt-member-directory__grid">
                    @foreach($businesses as $business)
                        <article class="dnt-member-card">
                            <header class="dnt-member-card__heading">
                                <div class="dnt-member-card__logo" aria-hidden="true">
                                    @if($business->logo_url)<img src="{{ $business->logo_url }}" alt="" loading="lazy" width="72" height="72">
                                    @else<i class="fa-regular fa-building" aria-hidden="true"></i>@endif
                                </div>
                                <div>
                                    <h3>{{ $business->name }}</h3>
                                    @if($business->category_name)<p>{{ $business->category_name }}</p>@endif
                                </div>
                            </header>
                            @if($business->summary)<p class="dnt-member-card__summary">{{ $business->summary }}</p>@endif
                            <dl class="dnt-member-card__details">
                                <div class="dnt-member-card__industries">
                                    <dt>Khối ngành nghề</dt>
                                    <dd>
                                        @forelse($business->industry_labels as $industryName)<span>{{ $industryName }}</span>
                                        @empty<span class="dnt-member-card__muted">Đang cập nhật</span>@endforelse
                                    </dd>
                                </div>
                                @if($business->chapter_name)<div><dt>Chi hội</dt><dd>{{ $business->chapter_name }}</dd></div>@endif
                                @if($business->size_label)<div><dt>Quy mô</dt><dd>{{ $business->size_label }}</dd></div>@endif
                                @if($business->location_label)<div><dt>Khu vực</dt><dd>{{ $business->location_label }}</dd></div>@endif
                            </dl>
                            <footer class="dnt-member-card__contact">
                                @if($business->phone_url)<a href="{{ $business->phone_url }}" aria-label="Gọi {{ $business->name }}: {{ $business->phone }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $business->phone }}</a>@endif
                                @if($business->email_url)<a href="{{ $business->email_url }}" aria-label="Gửi email tới {{ $business->name }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i> Email</a>@endif
                                @if($business->website_url)<a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer" aria-label="Website của {{ $business->name }} (mở tab mới)"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Website</a>@endif
                                @if(! $business->phone_url && ! $business->email_url && ! $business->website_url)<span>Thông tin liên hệ đang cập nhật.</span>@endif
                            </footer>
                        </article>
                    @endforeach
                </div>
                <x-frontend.publication-pagination :paginator="$businesses" />
            @else
                <div class="dnt-publication-empty">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <h3>{{ $activeFilters ? 'Chưa tìm thấy doanh nghiệp phù hợp' : 'Danh bạ đang được cập nhật' }}</h3>
                    <p>{{ $activeFilters ? 'Thử từ khóa ngắn hơn hoặc bỏ bớt điều kiện lọc để mở rộng kết quả.' : 'Thông tin doanh nghiệp sẽ xuất hiện sau khi hoàn tất quy trình kết nạp hội viên.' }}</p>
                    @if($activeFilters)<a class="dnt-publication-link" href="{{ route('directory.index') }}">Xem toàn bộ danh bạ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>@endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
