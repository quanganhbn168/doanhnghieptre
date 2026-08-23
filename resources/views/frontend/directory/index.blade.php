@extends('layouts.master')

@section('title', 'Danh bạ doanh nghiệp | DNT Bắc Ninh')
@section('meta_description', 'Tra cứu doanh nghiệp hội viên theo lĩnh vực hoạt động, quy mô và chi hội tại DNT Bắc Ninh.')

@section('content')
<section class="dnt-directory-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h1>Danh bạ doanh nghiệp</h1>
        <p>Tra cứu doanh nghiệp hội viên theo lĩnh vực, quy mô và chi hội để kết nối đúng nhu cầu.</p>
    </div>
</section>

<section class="dnt-section dnt-section--soft dnt-directory-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <form class="dnt-directory-filter" method="GET" action="{{ route('directory.index') }}">
            <label class="dnt-directory-filter__search">Từ khóa
                <span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Tên doanh nghiệp hoặc ngành nghề"></span>
            </label>
            <label>Lĩnh vực hoạt động
                <select name="industry">
                    <option value="">Tất cả lĩnh vực</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry->slug }}" @selected(($filters['industry'] ?? '') === $industry->slug)>{{ $industry->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Quy mô
                <select name="size">
                    <option value="">Tất cả quy mô</option>
                    @foreach($sizeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['size'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Thuộc chi hội
                <select name="chapter">
                    <option value="">Tất cả chi hội</option>
                    @foreach($chapters as $chapter)
                        <option value="{{ $chapter->slug }}" @selected(($filters['chapter'] ?? '') === $chapter->slug)>{{ $chapter->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="dnt-directory-filter__actions"><button type="submit">Lọc doanh nghiệp</button><a href="{{ route('directory.index') }}">Xóa lọc</a></div>
        </form>

        <div class="dnt-directory-result-heading"><h2>Doanh nghiệp đã xác thực</h2><span>{{ number_format($businesses->total()) }} doanh nghiệp</span></div>

        @if($businesses->isNotEmpty())
            <div class="dnt-business-directory-grid">
                @foreach($businesses as $business)
                    <article class="dnt-business-directory-card">
                        <div class="dnt-business-directory-card__logo">
                            @if($business->logo_url)
                                <img src="{{ $business->logo_url }}" alt="Logo {{ $business->name }}" loading="lazy" width="160" height="96">
                            @else
                                <span>Chưa có logo</span>
                            @endif
                        </div>
                        <div class="dnt-business-directory-card__body">
                            <span class="dnt-business-directory-card__category">{{ $business->category_name ?: 'Doanh nghiệp hội viên' }}</span>
                            <h2>{{ $business->name }}</h2>
                            <p>{{ $business->summary ?: 'Thông tin doanh nghiệp đang được cập nhật.' }}</p>
                        </div>
                        <dl>
                            <div><dt>Quy mô</dt><dd>{{ $sizeOptions[$business->business_size] ?? 'Đang cập nhật' }}</dd></div>
                            <div><dt>Chi hội</dt><dd>{{ $business->chapter_name ?: 'Đang cập nhật' }}</dd></div>
                            <div><dt>Khu vực</dt><dd>{{ collect([$business->district, $business->province])->filter()->join(' · ') ?: 'Bắc Ninh' }}</dd></div>
                        </dl>
                        <div class="dnt-business-directory-card__contact">
                            @if($business->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $business->phone) }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> Liên hệ</a>@endif
                            @if($business->website)<a href="{{ $business->website }}" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Website</a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="dnt-directory-pagination">{{ $businesses->links() }}</div>
        @else
            <div class="dnt-empty-card"><i class="fa-solid fa-building-circle-check" aria-hidden="true"></i><div><h2>Chưa tìm thấy doanh nghiệp phù hợp</h2><p>Hãy đổi điều kiện lọc hoặc bỏ lọc để xem toàn bộ danh bạ.</p></div></div>
        @endif
    </div>
</section>
@endsection
