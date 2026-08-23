@extends('layouts.master')

@section('title', 'Dự án đã thực hiện — ' . $website['name'])
@section('meta_description', 'Các dự án truyền thông, sản xuất hình ảnh, sự kiện và thương hiệu do THT Media triển
    khai.')
    @php
        $projectSchemaItems = $projects
            ->getCollection()
            ->map(
                fn($project): array => [
                    'name' => $project->getTranslation('name', 'vi'),
                    'url' => route('projects.show', $project->getSlug('vi')),
                ],
            )
            ->all();
    @endphp
    @include('partials.frontend.structured-data', [
        'schema' => \App\Support\SchemaMarkup::collection(
            'Dự án đã thực hiện',
            'Các dự án truyền thông, sản xuất hình ảnh, sự kiện và thương hiệu do THT Media triển khai.',
            $projectSchemaItems,
            route('projects.index')),
    ])

@section('content')
    <section class="page-hero page-hero--portfolio">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">

            <h1>Năng lực được chứng minh bằng công việc thực tế</h1>
            <p>Khám phá cách THT Media tiếp nhận yêu cầu, xây dựng giải pháp và triển khai từng dự án truyền thông.</p>
        </div>
    </section>

    <x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Dự án']]" />

    <section class="section-space">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <form class="grid items-end gap-4 rounded-2xl border border-line bg-canvas p-5 md:grid-cols-2 xl:grid-cols-4"
                action="{{ route('projects.index') }}" method="GET">
                <div>
                    <label class="ui-label" for="project-category">Danh mục</label>
                    <select class="ui-select" id="project-category" name="category">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->getSlug('vi') }}" @selected(request('category') === $category->getSlug('vi'))>
                                {{ $category->getTranslation('name', 'vi') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label" for="project-service">Dịch vụ</label>
                    <select class="ui-select" id="project-service" name="service">
                        <option value="">Tất cả dịch vụ</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->slug }}" @selected(request('service') === $service->slug)>
                                {{ $service->getTranslation('name', 'vi') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label" for="project-industry">Lĩnh vực</label>
                    <select class="ui-select" id="project-industry" name="industry">
                        <option value="">Tất cả lĩnh vực</option>
                        @foreach ($industries as $industry)
                            <option value="{{ $industry }}" @selected(request('industry') === $industry)>{{ $industry }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-2 md:col-span-2 xl:col-span-1">
                    <button
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-transparent bg-ink px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-black hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink sm:w-auto"
                        type="submit">Lọc dự án</button>
                    @if (request()->hasAny(['category', 'service', 'industry']))
                        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full px-3 py-2 text-sm font-bold text-primary transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                            href="{{ route('projects.index') }}">Xóa bộ lọc</a>
                    @endif
                </div>
            </form>

            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse($projects as $project)
                    <div>@include('partials.frontend.project-card', ['project' => $project])</div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <div class="empty-state"><i class="fa-solid fa-photo-film"></i>
                            <h2>Chưa có dự án phù hợp</h2>
                            <p>Hãy thử bỏ bớt điều kiện lọc hoặc trao đổi trực tiếp với THT Media về nhu cầu của anh/chị.
                            </p><a
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                href="{{ route('contact') }}">Nhận tư vấn</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($projects->hasPages())
                <div class="mt-5">{{ $projects->links() }}</div>
            @endif
        </div>
    </section>
@endsection
