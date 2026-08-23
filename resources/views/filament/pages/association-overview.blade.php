<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($metrics as $metric)
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="m-0 text-sm font-semibold text-gray-600 dark:text-gray-300">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($metric['value']) }}</p>
                    </div>
                    <x-filament::icon :icon="$metric['icon']" class="h-7 w-7 text-danger-600 dark:text-danger-400" />
                </div>
                <p class="mb-0 mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $metric['description'] }}</p>
            </section>
        @endforeach
    </div>

    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <h2 class="m-0 text-lg font-bold text-gray-950 dark:text-white">Quản lý nhanh</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Xử lý hồ sơ doanh nghiệp/hội viên, sau đó cập nhật thiết lập chung của website.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a class="inline-flex items-center gap-2 rounded-lg bg-danger-600 px-4 py-2.5 text-sm font-bold text-white no-underline transition hover:bg-danger-700" href="{{ \App\Filament\Resources\Businesses\BusinessResource::getUrl('index') }}">Duyệt hồ sơ doanh nghiệp <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" /></a>
            <a class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-800 no-underline transition hover:border-danger-300 hover:text-danger-700 dark:border-white/20 dark:bg-gray-800 dark:text-white" href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('index') }}">Quản lý hội viên <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" /></a>
            <a class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-800 no-underline transition hover:border-danger-300 hover:text-danger-700 dark:border-white/20 dark:bg-gray-800 dark:text-white" href="{{ route('filament.admin.pages.settings') }}">Cài đặt website <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" /></a>
        </div>
    </section>
</x-filament-panels::page>
