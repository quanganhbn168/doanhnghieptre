@props([
    'items' => [],
    'contained' => true,
])

@php
    $breadcrumbItems = collect($items)
        ->map(fn (array $item): array => [
            'label' => trim((string) ($item['label'] ?? '')),
            'url' => filled($item['url'] ?? null) ? (string) $item['url'] : null,
        ])
        ->filter(fn (array $item): bool => $item['label'] !== '')
        ->values();
    $breadcrumbSchema = \App\Support\SchemaMarkup::breadcrumb($breadcrumbItems->all());
@endphp

<div class="breadcrumb-wrap border-b border-line bg-canvas">
    <nav class="{{ $contained ? 'container mx-auto px-4 sm:px-6 lg:px-8' : '' }} flex flex-wrap items-center gap-2 py-3 text-sm text-muted" aria-label="breadcrumb">
        @foreach($breadcrumbItems as $index => $item)
            @php($isLast = $index === $breadcrumbItems->count() - 1)
            @if($index > 0)<span aria-hidden="true">/</span>@endif
            <span class="{{ $isLast ? 'max-w-full truncate font-bold text-ink' : '' }}" @if($isLast)aria-current="page"@endif>
                @if(! $isLast && $item['url'])
                    <a class="transition hover:text-primary" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
            </span>
        @endforeach
    </nav>
</div>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
