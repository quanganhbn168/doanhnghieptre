<footer class="dnt-site-footer">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="dnt-site-footer__grid">
            <div>
                @php($footerLogo = $siteAssets?->getFirstMediaUrl('logo_footer') ?: $siteAssets?->getFirstMediaUrl('logo'))
                <a class="dnt-brand dnt-brand--footer" href="{{ route('home') }}" aria-label="DNT Bắc Ninh - Trang chủ">
                    @if($footerLogo)
                        <img class="dnt-brand__logo" src="{{ $footerLogo }}" alt="Hội Doanh nhân trẻ tỉnh Bắc Ninh" width="395" height="100">
                    @else
                        <span class="dnt-brand__mark" aria-hidden="true"><i class="fa-solid fa-compass"></i><b>★</b></span>
                        <span><strong>DNT BẮC NINH</strong><small>Hội Doanh nhân trẻ tỉnh Bắc Ninh</small></span>
                    @endif
                </a>
                <p class="dnt-site-footer__intro">Nền tảng thông tin, hoạt động và kết nối giao thương dành cho cộng đồng doanh nhân trẻ Bắc Ninh.</p>
                <p class="dnt-site-footer__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Bắc Ninh, Việt Nam</p>
            </div>
            <div>
                <h2>Liên kết nhanh</h2>
                <a href="{{ route('about') }}">Giới thiệu về Hội</a>
                <a href="{{ route('businesses.index') }}">Doanh nghiệp</a>
                <a href="{{ route('events.index') }}">Sự kiện</a>
                <a href="{{ route('trade.index') }}">Giao thương</a>
                <a href="{{ route('news.index') }}">Tin tức</a>
            </div>
            <div>
                <h2>Thông tin liên hệ</h2>
                <p>Thông tin liên hệ chính thức đang được cập nhật trên nền tảng.</p>
                <a class="dnt-site-footer__contact" href="{{ route('contact') }}"><i class="fa-regular fa-envelope" aria-hidden="true"></i> Gửi liên hệ cho DNT Bắc Ninh</a>
            </div>
            <div>
                <h2>Đăng ký nhận tin</h2>
                <p>Nhận thông tin mới về sự kiện, giao thương và hoạt động doanh nghiệp.</p>
                @if(session('newsletter_success'))<p class="dnt-site-footer__success">{{ session('newsletter_success') }}</p>@endif
                <form class="dnt-newsletter" action="{{ route('newsletter.store') }}" method="post">
                    @csrf
                    <input class="hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="sr-only" for="dnt-newsletter-email">Email</label>
                    <input id="dnt-newsletter-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="Nhập email của bạn" required>
                    <button type="submit">Đăng ký</button>
                </form>
                @error('email')<p class="dnt-site-footer__error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="dnt-site-footer__bottom">
            <span>© {{ date('Y') }} Hội Doanh nhân trẻ tỉnh Bắc Ninh.</span>
            <a href="{{ route('policies.privacy') }}">Chính sách bảo mật</a>
        </div>
    </div>
</footer>
