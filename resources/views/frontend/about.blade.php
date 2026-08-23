@extends('layouts.master')

@section('title', $aboutPageLabel . ' — ' . $website['name'])

@php
    $story = data_get($aboutSettings?->about_story, 'vi');
    $history = data_get($aboutSettings?->about_history, 'vi');
    $mission = data_get($aboutSettings?->about_mission, 'vi');
    $vision = data_get($aboutSettings?->about_vision, 'vi');
    $coreValues = data_get($aboutSettings?->about_core_values, 'vi');
    $aboutImage = $siteAssets?->getFirstMediaUrl('about_image');
    $aboutContentGroups = $aboutContentGroups ?? collect();
    $aboutTeam = $aboutContentGroups->get('team', collect());
    $aboutFacilities = $aboutContentGroups->get('facility', collect());
    $aboutFaqs = $aboutContentGroups->get('faq', collect());
    $aboutArticles = $aboutContentGroups->get('article', collect());
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::webPage($aboutPageLabel, $aboutPageIntro, route('about'))])

@section('content')
<section class="page-hero"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>{{ $aboutPageTitle }}</h1>@if($aboutPageIntro)<p>{{ $aboutPageIntro }}</p>@endif</div></section>
<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => $aboutPageLabel]]" />

<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-6 lg:grid-cols-12">
            @if($aboutImage)
                <div class="lg:col-span-5"><img class="w-full rounded-2xl shadow-sm" src="{{ $aboutImage }}" alt="{{ $website['name'] }}" loading="lazy"></div>
            @endif
            <div class="{{ $aboutImage ? 'lg:col-span-7' : 'lg:col-span-12' }}">
                <div class="content-copy">{!! $story !!}</div>
            </div>
        </div>

        @if($history)
            <div class="content-card mt-12"><h2 class="text-xl font-bold">Lịch sử hình thành</h2><div class="content-copy">{!! $history !!}</div></div>
        @endif

        <div class="grid gap-6 mt-1 md:grid-cols-2">
            @if($vision)<div><div class="content-card h-full"><h2 class="text-lg font-bold">Tầm nhìn</h2><div class="content-copy">{!! $vision !!}</div></div></div>@endif
            @if($mission)<div><div class="content-card h-full"><h2 class="text-lg font-bold">Sứ mệnh</h2><div class="content-copy">{!! $mission !!}</div></div></div>@endif
        </div>

        @if($coreValues)
            <div class="mt-12"><div class="mb-6 text-center"><h2 class="text-xl font-bold">Giá trị cốt lõi</h2></div>{!! $coreValues !!}</div>
        @endif

        @if($aboutTeam->isNotEmpty())
            <section class="mt-12">
                <x-section-heading title="Đội ngũ" />
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($aboutTeam as $member)
                        <article class="content-card h-full">
                            @if($member->getFirstMediaUrl('company_image'))
                                <img class="mb-5 aspect-[4/3] w-full rounded-xl object-cover" src="{{ $member->getFirstMediaUrl('company_image') }}" alt="{{ $member->getTranslation('title', 'vi') }}" loading="lazy">
                            @endif

                            <h3 class="mt-3 text-lg font-bold"><a href="{{ route('about.content.show', ['slug' => $member->routeSlug('vi')]) }}">{{ $member->getTranslation('title', 'vi') }}</a></h3>
                            @if(filled($member->getTranslation('summary', 'vi')))<p class="mt-3 text-muted">{{ $member->getTranslation('summary', 'vi') }}</p>@endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($aboutFacilities->isNotEmpty())
            <section class="mt-12">
                <x-section-heading title="Cơ sở vật chất" />
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach($aboutFacilities as $facility)
                        <article class="content-card h-full">
                            @if($facility->getFirstMediaUrl('company_image'))
                                <img class="mb-5 aspect-[16/9] w-full rounded-xl object-cover" src="{{ $facility->getFirstMediaUrl('company_image') }}" alt="{{ $facility->getTranslation('title', 'vi') }}" loading="lazy">
                            @else
                                <i class="fa-solid fa-building text-2xl text-primary" aria-hidden="true"></i>
                            @endif
                            <h3 class="mt-4 text-lg font-bold"><a href="{{ route('about.content.show', ['slug' => $facility->routeSlug('vi')]) }}">{{ $facility->getTranslation('title', 'vi') }}</a></h3>
                            @if(filled($facility->getTranslation('summary', 'vi')))<p class="mt-2 text-muted">{{ $facility->getTranslation('summary', 'vi') }}</p>@endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($aboutFaqs->isNotEmpty())
            <section class="mt-12">
                <x-section-heading title="Câu hỏi thường gặp" />
                <div class="faq-list" id="aboutFaq">
                    @foreach($aboutFaqs as $faq)
                        <div class="faq-item">
                            <h3 class="faq-heading">
                                <button class="faq-button {{ $loop->first ? 'is-open' : '' }}" data-faq-toggle="about-faq-{{ $loop->iteration }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $faq->getTranslation('title', 'vi') }}<i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </button>
                            </h3>
                            <div id="about-faq-{{ $loop->iteration }}" class="faq-panel {{ $loop->first ? 'is-open' : '' }}"><div class="faq-body content-copy">{!! $faq->getTranslation('content', 'vi') !!}</div></div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($aboutArticles->isNotEmpty())
            <section class="mt-12">
                <x-section-heading title="Bài viết về THT Media" />
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($aboutArticles as $article)
                        <article class="content-card h-full">
                            @if($article->getFirstMediaUrl('company_image'))
                                <img class="mb-5 aspect-[16/10] w-full rounded-xl object-cover" src="{{ $article->getFirstMediaUrl('company_image') }}" alt="{{ $article->getTranslation('title', 'vi') }}" loading="lazy">
                            @endif

                            <h3 class="mt-3 text-lg font-bold"><a href="{{ route('about.content.show', ['slug' => $article->routeSlug('vi')]) }}">{{ $article->getTranslation('title', 'vi') }}</a></h3>
                            @if(filled($article->getTranslation('summary', 'vi')))<p class="mt-3 text-muted">{{ $article->getTranslation('summary', 'vi') }}</p>@endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-12 pt-3">
            <x-section-heading title="Ba nhóm năng lực triển khai" :href="route('services.index')" />
            <div class="grid gap-6 md:grid-cols-3">
                @foreach(\App\Models\Service::GROUPS as $group => $label)
                    <div><a class="content-card block h-full" href="{{ route('services.index', ['group' => $group]) }}"><h3 class="text-base font-bold">{{ $label }}</h3><p class="text-muted">Xem phạm vi dịch vụ, đầu việc và sản phẩm bàn giao thuộc nhóm năng lực này.</p></a></div>
                @endforeach
            </div>
        </section>
    </div>
</section>

<section class="section-space pt-0"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><div class="conversion-panel"><div><h2>Xem cách THT Media chuyển yêu cầu thành giải pháp triển khai</h2></div><div class="flex flex-wrap gap-2"><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-white px-5 py-3 text-sm font-bold leading-tight text-ink shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('projects.index') }}">Xem dự án</a><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('contact') }}">Nhận tư vấn</a></div></div></div></section>
@endsection
