@extends('layouts.master')

@section('title', 'Khách hàng và đối tác — ' . $website['name'])
@section('meta_description', 'Khách hàng và đối tác đã đồng hành cùng THT Media trong các dự án truyền thông, hình ảnh, sự kiện và thương hiệu.')
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::collection('Khách hàng và đối tác', 'Khách hàng và đối tác đã đồng hành cùng THT Media.', $clientSchemaItems, route('clients.index'))])

@section('content')
<section class="page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>Sự tin tưởng được xây dựng qua từng lần hợp tác</h1><p>Danh sách được sắp xếp theo lĩnh vực để người xem dễ đối chiếu với nhu cầu dự án của mình.</p></div>
</section>

<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Khách hàng và đối tác']]" />

<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        @forelse($clientGroups as $industry => $clients)
            <section class="client-group">
                <div class="client-group__heading"><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2>{{ $industry }}</h2><small>{{ $clients->count() }} đơn vị</small></div>
                <div class="client-grid">
                    @foreach($clients as $client)
                            <article id="client-{{ $client->id }}" class="client-card">
                            <div class="client-card__logo">@if($logo = $client->getFirstMediaUrl('logo'))<img src="{{ $logo }}" alt="{{ $client->getTranslation('name', 'vi') }}" loading="lazy">@else<span>{{ mb_substr($client->getTranslation('name', 'vi'), 0, 2) }}</span>@endif</div>
                            <div><h3>{{ $client->getTranslation('name', 'vi') }}</h3>@if($description = $client->getTranslation('description', 'vi'))<p>{{ $description }}</p>@endif
                                @if($client->projects->isNotEmpty())<div class="client-card__projects">@foreach($client->projects->take(3) as $project)<a href="{{ route('projects.show', $project->slug) }}">{{ $project->getTranslation('name', 'vi') }} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>@endforeach</div>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state"><i class="fa-solid fa-building-circle-check"></i><h2>Thông tin khách hàng đang được cập nhật</h2><p>THT Media chỉ công bố các đơn vị đã được phép hiển thị trên website.</p></div>
        @endforelse
    </div>
</section>

<section class="section-space pt-0"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="conversion-panel"><div><h2>Doanh nghiệp của anh/chị đang cần một đội ngũ triển khai truyền thông?</h2></div><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('contact') }}">Trao đổi nhu cầu</a></div></div></section>
@endsection

