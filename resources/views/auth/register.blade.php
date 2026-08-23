@extends('layouts.auth')

@section('title', 'Tạo tài khoản | DNT Bắc Ninh')

@section('content')
    <div class="dnt-auth-card__heading">
        <h1 id="dnt-auth-title">Tạo tài khoản</h1>
        <p>Tạo tài khoản để nộp hồ sơ doanh nghiệp, theo dõi phản hồi từ Hội và trở thành hội viên sau khi hồ sơ được duyệt.</p>
    </div>

    <form class="dnt-auth-form" method="POST" action="{{ route('register') }}">
        @csrf
        <label for="name">Họ và tên</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
        @error('name')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label for="phone">Số điện thoại</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required>
        @error('phone')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label for="password">Mật khẩu</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required>
        @error('password')<p class="dnt-auth-message">{{ $message }}</p>@enderror

        <label for="password_confirmation">Xác nhận mật khẩu</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

        <button type="submit">Tạo tài khoản <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="dnt-auth-card__footer"><p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p></div>
@endsection
