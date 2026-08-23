@extends('layouts.master')

@section('title', $project->getTranslation('seo_title', 'vi') ?: $project->getTranslation('name', 'vi') . ' — ' .
    $website['name'])
@section('meta_description', $project->getTranslation('seo_description', 'vi') ?: $project->getTranslation('summary',
    'vi') ?: '')
@section('meta_keywords', $project->getTranslation('seo_keywords', 'vi') ?: '')
@section('seo_image', $project->shareImage?->url ?: $project->cover?->url ?: $project->getFirstMediaUrl('share_image') ?: $project->getFirstMediaUrl('cover') ?: '')
@php
    $projectName = $project->getTranslation('name', 'vi');
    $projectDescription =
        $project->getTranslation('seo_description', 'vi') ?: $project->getTranslation('summary', 'vi') ?: '';
    $projectUrl = route('projects.show', $project->getSlug('vi'));
@endphp
@include('partials.frontend.structured-data', [
    'schema' => \App\Support\SchemaMarkup::project([
        'name' => $projectName,
        'description' => $projectDescription,
        'url' => $projectUrl,
        'image' => $project->shareImage?->url ?: $project->cover?->url ?: $project->getFirstMediaUrl('share_image') ?: $project->getFirstMediaUrl('cover'),
        'client' => $project->client?->getTranslation('name', 'vi'),
        'services' => $project->services->map(fn ($service): string => $service->getTranslation('name', 'vi'))->all(),
        'video' => $project->video_url,
    ]),
])

@section('content')
    <section class="detail-hero detail-hero--project">
        @if ($cover = ($project->cover?->url ?: $project->getFirstMediaUrl('cover')))
            <img class="detail-hero__image" src="{{ $cover }}" alt="{{ $project->getTranslation('name', 'vi') }}"
                fetchpriority="high">
        @endif
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 detail-hero__content">
            <h1>{{ $project->getTranslation('name', 'vi') }}</h1>
            <p>{{ $project->getTranslation('summary', 'vi') }}</p>
            <div class="detail-hero__meta">
                @if ($project->client)
                    <span><strong>Khách hàng</strong>{{ $project->client->getTranslation('name', 'vi') }}</span>
                @endif
                @if ($project->industry)
                    <span><strong>Lĩnh vực</strong>{{ $project->industry }}</span>
                @endif
                @if ($project->completed_year)
                    <span><strong>Năm thực hiện</strong>{{ $project->completed_year }}</span>
                @endif
            </div>
        </div>
    </section>

    <x-frontend.breadcrumb :items="array_values(
        array_filter([
            ['label' => 'Trang chủ', 'url' => route('home')],
            ['label' => 'Dự án', 'url' => route('projects.index')],
            $project->category
                ? [
                    'label' => $project->category->getTranslation('name', 'vi'),
                    'url' => route('projects.show', $project->category->getSlug('vi')),
                ]
                : null,
            ['label' => $projectName],
        ]),
    )" />

    <section class="section-space">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    @foreach ([['Bối cảnh và yêu cầu', $project->getTranslation('context', 'vi')], ['Giải pháp của THT Media', $project->getTranslation('solution', 'vi')]] as [$heading, $content])
                        @if ($content)
                            <section class="case-section">
                                <div class="rich-content">{!! $content !!}</div>
                            </section>
                        @endif
                    @endforeach

                    @if ($workItems = $project->getTranslation('work_items', 'vi'))
                        <section class="case-section">
                            <ul class="check-list">
                                @foreach ($workItems as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @php
                        $galleryImages = $project->galleryMedia
                            ->map(fn ($image): array => ['url' => $image->url, 'alt' => $image->alt ?: $project->getTranslation('name', 'vi')])
                            ->values();
                        if ($galleryImages->isEmpty()) {
                            $galleryImages = $project->getMedia('gallery')
                                ->map(fn ($image): array => ['url' => $image->getUrl(), 'alt' => $project->getTranslation('name', 'vi')])
                                ->values();
                        }
                    @endphp
                    @if ($galleryImages->isNotEmpty())
                            <section class="case-section">
                                <div class="project-gallery">
                                    @foreach ($galleryImages as $image)
                                        <a class="glightbox" href="{{ $image['url'] }}"
                                            data-gallery="project-{{ $project->id }}"><img src="{{ $image['url'] }}"
                                                alt="{{ $image['alt'] }}" loading="lazy"></a>
                                    @endforeach
                                </div>
                            </section>
                    @endif

                    @if ($project->video_url)
                        <section class="case-section">
                            <div class="video-link-panel"><i class="fa-solid fa-circle-play"></i>
                                <div>
                                    <h2>Xem video hoàn thiện</h2>
                                    <p>Video sẽ được mở ngay trong cửa sổ xem của dự án.</p>
                                </div><a
                                    class="glightbox inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-ink px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-black hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink lg:ml-auto"
                                    href="{{ $project->video_url }}" data-type="video"
                                    data-gallery="project-video-{{ $project->id }}" data-title="{{ $projectName }}">Xem video</a>
                            </div>
                        </section>
                    @endif

                    @if ($results = $project->getTranslation('results', 'vi'))
                        <section class="case-section case-results">
                            <ul class="check-list">
                                @foreach ($results as $result)
                                    <li>{{ $result }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
                <aside class="lg:col-span-4">
                    <div class="sticky-panel">

                        <div class="stacked-links">
                            @foreach ($project->services as $service)
                                <a href="{{ route('services.show', $service->slug) }}"><span>{{ $service->getTranslation('name', 'vi') }}</span><i
                                        class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            @endforeach
                        </div>
                        <hr>
                        <h2>Đang chuẩn bị một dự án tương tự?</h2>
                        <p>Gửi mục tiêu và phạm vi dự kiến để THT Media cùng làm rõ giải pháp.</p>
                        <a class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                            href="#project-consultation">Trao đổi về dự án</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @include('partials.frontend.comments', [
        'commentable' => $project,
        'commentableType' => 'project',
        'comments' => $project->comments,
    ])

    @if ($relatedProjects->isNotEmpty())
        <section class="section-space section-muted">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8"><x-section-heading title="Dự án liên quan"
                    :href="route('projects.index')" />
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($relatedProjects as $related)
                        <div>@include('partials.frontend.project-card', ['project' => $related])</div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section-space" id="project-consultation">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="consultation-panel">
                <div class="consultation-panel__intro">
                    <h2>Cho chúng tôi biết mục tiêu dự án</h2>
                    <p>THT Media sẽ liên hệ để làm rõ phạm vi, cách triển khai và các đầu việc cần bàn giao.</p>
                </div>
                <div>@include('partials.frontend.consultation-form', [
                    'extended' => true,
                    'formId' => 'project',
                    'selectedServiceId' => $project->services->first()?->id,
                ])</div>
            </div>
        </div>
    </section>
@endsection
