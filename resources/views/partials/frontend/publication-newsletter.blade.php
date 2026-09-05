<section class="dnt-publication-side-section dnt-publication-newsletter">
    <h2>Nhận tin từ Hội</h2>
    <p>Cập nhật tin tức và hoạt động mới qua email.</p>
    @if(session('newsletter_success'))
        <div class="dnt-publication-feedback" role="status">{{ session('newsletter_success') }}</div>
    @else
        <form action="{{ route('newsletter.store') }}" method="POST" class="dnt-publication-form">
            @csrf
            <input class="hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
            <label for="publication-newsletter-email">Địa chỉ email</label>
            <input id="publication-newsletter-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="Email của anh/chị" required>
            @error('email')<p class="ui-error">{{ $message }}</p>@enderror
            <button class="dnt-publication-button" type="submit">Đăng ký nhận tin</button>
        </form>
    @endif
</section>
