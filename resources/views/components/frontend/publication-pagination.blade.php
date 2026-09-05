@props(['paginator'])
@if($paginator->hasPages())
    <nav class="dnt-publication-pagination" aria-label="Phân trang">
        @if($paginator->onFirstPage())<span aria-disabled="true">← Trang trước</span>
        @else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Trang trước</a>@endif
        <span>Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Trang sau →</a>
        @else<span aria-disabled="true">Trang sau →</span>@endif
    </nav>
@endif
