@extends('layouts.auth')

@section('title', 'Đăng nhập | DNT Bắc Ninh')

@section('content')
    <div class="dnt-auth-card__heading">
        <h1 id="dnt-auth-title">Đăng nhập</h1>
        <p>Truy cập tài khoản để kết nối và theo dõi hoạt động của cộng đồng doanh nhân trẻ.</p>
    </div>

    @if(session('status'))<p class="dnt-auth-message dnt-auth-message--success">{{ session('status') }}</p>@endif

    <form class="dnt-auth-form" method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label for="password">Mật khẩu</label>
        <div class="dnt-auth-password">
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="dnt-auth-password__toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false">
                <i class="fa-regular fa-eye" aria-hidden="true"></i><span class="sr-only">Hiện mật khẩu</span>
            </button>
        </div>
        @error('password')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label class="dnt-auth-form__remember"><input name="remember" type="checkbox" value="1"> Ghi nhớ đăng nhập</label>
        <button type="submit">Đăng nhập <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="dnt-auth-card__footer">
        @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Quên mật khẩu?</a>@endif
        <p>Doanh nghiệp chưa là hội viên? <a href="{{ route('membership.create') }}">Đăng ký hội viên</a></p>
    </div>
@endsection
