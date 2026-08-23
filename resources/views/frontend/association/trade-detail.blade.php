@extends('layouts.master')

@section('title', $tradePost->title.' | Cơ hội giao thương | DNT Bắc Ninh')
@section('meta_description', $tradePost->summary ?: 'Cơ hội giao thương trên nền tảng DNT Bắc Ninh.')

@push('styles')
<style>
.dnt-trade-detail{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(280px,.75fr);gap:24px;align-items:start}.dnt-trade-detail__content,.dnt-trade-detail__contact{padding:clamp(24px,4vw,38px);background:var(--dnt-surface);border:1px solid var(--dnt-line);border-radius:9px;box-shadow:0 9px 24px rgb(var(--dnt-brand-black-rgb) / .05)}.dnt-trade-detail__type{margin:0 0 12px;color:var(--dnt-brand-red);font-size:13px;font-weight:800;text-transform:uppercase}.dnt-trade-detail__summary{margin:0;color:var(--dnt-brand-black);font-size:19px;font-weight:700;line-height:1.6}.dnt-trade-detail__body{margin-top:22px;color:var(--dnt-muted);font-size:16px;line-height:1.8}.dnt-trade-detail__industries{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px}.dnt-trade-detail__industries span{padding:7px 10px;color:var(--dnt-brand-black);background:var(--dnt-brand-red-soft);border-radius:4px;font-size:12px;font-weight:800}.dnt-trade-detail__contact h2{margin:0 0 18px;color:var(--dnt-brand-black);font-size:20px;font-weight:800}.dnt-trade-detail__contact p{display:grid;gap:3px;margin:0 0 14px;color:var(--dnt-muted);font-size:14px;line-height:1.55}.dnt-trade-detail__contact strong{color:var(--dnt-brand-black);font-size:12px;text-transform:uppercase}.dnt-trade-detail__contact .dnt-button{width:100%;margin-top:8px}@media(max-width:767px){.dnt-trade-detail{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<section class="dnt-page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><p>{{ $tradePost->typeLabel($tradePost->type) }}</p><h1>{{ $tradePost->title }}</h1><span>{{ $tradePost->business?->name ?: 'Doanh nghiệp hội viên DNT Bắc Ninh' }}</span></div>
</section>
<section class="dnt-section dnt-section--soft">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="dnt-trade-detail">
            <article class="dnt-trade-detail__content">
                <p class="dnt-trade-detail__type">{{ $tradePost->typeLabel($tradePost->type) }}</p>
                @if($tradePost->summary)<p class="dnt-trade-detail__summary">{{ $tradePost->summary }}</p>@endif
                @if($tradePost->content)<div class="dnt-trade-detail__body">{!! nl2br(e(strip_tags($tradePost->content))) !!}</div>@endif
                @if($tradePost->industries->isNotEmpty())<div class="dnt-trade-detail__industries">@foreach($tradePost->industries as $industry)<span>{{ $industry->name }}</span>@endforeach</div>@endif
            </article>
            <aside class="dnt-trade-detail__contact">
                <h2>Thông tin kết nối</h2>
                @if($tradePost->location_label)<p><strong>Khu vực</strong>{{ $tradePost->location_label }}</p>@endif
                @if($tradePost->budget_label)<p><strong>Quy mô</strong>{{ $tradePost->budget_label }}</p>@endif
                @if($tradePost->expires_at)<p><strong>Hiệu lực đến</strong>{{ $tradePost->expires_at->format('d/m/Y') }}</p>@endif
                @if($tradePost->contact_name)<p><strong>Liên hệ</strong>{{ $tradePost->contact_name }}</p>@endif
                @if($tradePost->contact_phone)<a class="dnt-button dnt-button--dark" href="tel:{{ preg_replace('/[^0-9+]/', '', $tradePost->contact_phone) }}">Gọi {{ $tradePost->contact_phone }}</a>@endif
                @if($tradePost->contact_email)<a class="dnt-button dnt-button--outline-dark" href="mailto:{{ $tradePost->contact_email }}">Gửi email</a>@endif
            </aside>
        </div>
        <a class="dnt-text-link" href="{{ route('trade.index') }}">← Quay lại cơ hội giao thương</a>
    </div>
</section>
@endsection
