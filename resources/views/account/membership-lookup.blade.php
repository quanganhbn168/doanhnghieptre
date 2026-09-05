@extends('layouts.master')
@section('title', 'Tra cứu hồ sơ gia nhập Hội | DNT Bắc Ninh')
@section('robots', 'noindex, nofollow')
@push('styles') @vite('resources/css/business-application.css') @endpush
@section('content')
<div class="dnt-application-page"><div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="dnt-application-page__heading"><a class="dnt-text-link" href="{{ route('membership.create') }}">Đăng ký hội viên</a><h1>Tra cứu hồ sơ gia nhập Hội</h1><p>Nhập mã hồ sơ và email người đại diện để nhận lại liên kết theo dõi.</p></div>
    <form class="dnt-business-application" method="POST" action="{{ route('membership.lookup.send') }}">@csrf
        @if(session('success'))<div class="ui-alert ui-alert--success" role="status">{{ session('success') }}</div>@endif
        <section><div class="dnt-business-application__grid">
            <label><span>Mã hồ sơ *</span><input class="ui-input" name="application_code" value="{{ old('application_code') }}" maxlength="40" required></label>
            <label><span>Email người đại diện *</span><input class="ui-input" type="email" name="email" value="{{ old('email') }}" maxlength="255" required></label>
        </div></section>
        @if($errors->any())<div class="ui-alert ui-alert--error">{{ $errors->first() }}</div>@endif
        <div class="dnt-business-application__footer"><button class="dnt-button dnt-button--dark" type="submit">Gửi liên kết theo dõi</button></div>
    </form>
</div></div>
@endsection
