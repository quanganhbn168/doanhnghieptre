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
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        @error('password')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label class="dnt-auth-form__remember"><input name="remember" type="checkbox" value="1"> Ghi nhớ đăng nhập</label>
        <button type="submit">Đăng nhập <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="dnt-auth-card__footer">
        @if(Route::has('password.request'))<a href="{{ route('password.request') }}">Quên mật khẩu?</a>@endif
        <p>Chưa có tài khoản? <a href="{{ route('register') }}">Tạo tài khoản</a></p>
    </div>
@endsection
