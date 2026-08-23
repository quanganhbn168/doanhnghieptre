@extends('layouts.master')

@section('title', 'Tài khoản của tôi | DNT Bắc Ninh')
@section('meta_description', 'Theo dõi hồ sơ hội viên và các doanh nghiệp đã đăng ký với DNT Bắc Ninh.')
@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Bản nháp',
            'pending' => 'Chờ Hội duyệt',
            'approved' => 'Đã công bố',
            'rejected' => 'Cần bổ sung',
        ];
    @endphp

    <div class="dnt-account">
        <section class="dnt-account__hero">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div>
                    <p class="dnt-account__welcome">Tài khoản cộng đồng</p>
                    <h1>Xin chào, {{ $user->name }}</h1>
                    <p>Quản lý hồ sơ của anh/chị và theo dõi doanh nghiệp đã gửi tới Hội Doanh nhân trẻ Bắc Ninh.</p>
                </div>
                @if($member?->status === 'approved')
                    <a class="dnt-button dnt-button--gold" href="{{ url('/thanh-vien') }}">Vào cổng doanh nghiệp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @else
                    <a class="dnt-button dnt-button--gold" href="{{ route('account.businesses.create') }}">Nộp hồ sơ doanh nghiệp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @endif
            </div>
        </section>

        <section class="dnt-account__content">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="dnt-account__metrics" aria-label="Tổng quan hồ sơ">
                    <article><span>Doanh nghiệp đã gửi</span><strong>{{ $businesses->count() }}</strong></article>
                    <article><span>Đang chờ duyệt</span><strong>{{ $businesses->where('status', 'pending')->count() }}</strong></article>
                    <article><span>Đang công bố</span><strong>{{ $businesses->where('status', 'approved')->count() }}</strong></article>
                </div>

                <div class="dnt-account__grid">
                    <section class="dnt-account-panel dnt-account-panel--profile">
                        <div class="dnt-account-panel__heading"><h2>Hồ sơ tài khoản</h2></div>
                        <dl class="dnt-account-profile">
                            <div><dt>Họ và tên</dt><dd>{{ $user->name }}</dd></div>
                            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                            <div><dt>Số điện thoại</dt><dd>{{ $user->phone ?: 'Chưa cập nhật' }}</dd></div>
                            <div><dt>Tư cách hội viên</dt><dd><span class="dnt-status dnt-status--{{ $member?->status ?: 'draft' }}">{{ $member ? ($statusLabels[$member->status] ?? $member->status) : 'Sẽ được duyệt cùng hồ sơ doanh nghiệp' }}</span></dd></div>
                        </dl>
                        <p class="dnt-account-panel__hint">Tài khoản là thông tin đăng nhập. Khi Hội duyệt hồ sơ doanh nghiệp, người nộp sẽ được công nhận là hội viên đại diện.</p>
                    </section>

                    <section class="dnt-account-panel">
                        <div class="dnt-account-panel__heading"><h2>Việc cần làm</h2></div>
                        <ul class="dnt-account-checklist">
                            <li class="{{ $businesses->isNotEmpty() ? 'is-done' : '' }}"><i class="fa-solid {{ $businesses->isNotEmpty() ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Nộp hồ sơ đăng ký doanh nghiệp.</span></li>
                            <li class="{{ $businesses->where('status', 'approved')->isNotEmpty() ? 'is-done' : '' }}"><i class="fa-solid {{ $businesses->where('status', 'approved')->isNotEmpty() ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Hội duyệt doanh nghiệp và xác thực thông tin.</span></li>
                            <li class="{{ $member?->status === 'approved' ? 'is-done' : '' }}"><i class="fa-solid {{ $member?->status === 'approved' ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Người đại diện trở thành hội viên.</span></li>
                            <li><i class="fa-solid fa-circle" aria-hidden="true"></i><span>Tham gia sự kiện và cập nhật các cơ hội giao thương phù hợp.</span></li>
                        </ul>
                    </section>
                </div>

                <section class="dnt-account-panel dnt-account-businesses">
                    <div class="dnt-account-panel__heading">
                        <div><h2>Doanh nghiệp của tôi</h2><p>Hồ sơ được công bố tại danh bạ sau khi Hội duyệt.</p></div>
                        <a class="dnt-text-link" href="{{ $member?->status === 'approved' ? url('/thanh-vien') : route('account.businesses.create') }}">{{ $member?->status === 'approved' ? 'Mở cổng doanh nghiệp' : 'Thêm doanh nghiệp' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>

                    @forelse($businesses as $business)
                        <article class="dnt-account-business-card">
                            <div class="dnt-account-business-card__logo">
                                @if($logo = $business->getFirstMediaUrl('logo'))
                                    <img src="{{ $logo }}" alt="Logo {{ $business->name }}" width="120" height="80">
                                @else
                                    <span>{{ mb_strtoupper(mb_substr($business->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="dnt-account-business-card__body">
                                <div class="dnt-account-business-card__title"><h3>{{ $business->name }}</h3><span class="dnt-status dnt-status--{{ $business->status }}">{{ $statusLabels[$business->status] ?? $business->status }}</span></div>
                                <p>{{ $business->category?->name ?: 'Đang cập nhật nhóm doanh nghiệp' }} · {{ $business->industries->first()?->name ?: 'Đang cập nhật lĩnh vực' }}</p>
                                @if($business->status === 'rejected')
                                    <p class="dnt-account-business-card__notice">Hồ sơ cần được bổ sung. Anh/chị hãy chỉnh sửa rồi gửi lại để Hội duyệt.</p>
                                @elseif($business->status === 'pending')
                                    <p class="dnt-account-business-card__notice">Hồ sơ đã gửi và đang được Hội kiểm tra.</p>
                                @endif
                            </div>
                            <div class="dnt-account-business-card__actions">
                                @if($business->status === 'rejected' || $business->status === 'draft')
                                    <a class="dnt-button dnt-button--dark" href="{{ route('account.businesses.edit', $business) }}">Bổ sung hồ sơ</a>
                                @elseif($business->status === 'approved')
                                    <a class="dnt-text-link" href="{{ route('directory.index', ['q' => $business->name]) }}">Xem trên danh bạ <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="dnt-account-empty">
                            <h3>Chưa có hồ sơ doanh nghiệp</h3><p>Nộp hồ sơ doanh nghiệp để Hội xác thực; khi được duyệt, người đại diện sẽ trở thành hội viên.</p><a class="dnt-button dnt-button--dark" href="{{ route('account.businesses.create') }}">Bắt đầu đăng ký</a>
                        </div>
                    @endforelse
                </section>

                @if($histories->isNotEmpty())
                    <section class="dnt-account-panel dnt-account-history">
                        <div class="dnt-account-panel__heading"><h2>Cập nhật hồ sơ gần đây</h2></div>
                        <ol>
                            @foreach($histories as $history)
                                <li><span class="dnt-status dnt-status--{{ $history->to_status }}">{{ $statusLabels[$history->to_status] ?? $history->to_status }}</span><div><strong>{{ $history->business?->name }}</strong>@if($history->reason)<p>{{ $history->reason }}</p>@endif</div><time datetime="{{ $history->changed_at?->toDateString() }}">{{ $history->changed_at?->format('d/m/Y H:i') }}</time></li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            </div>
        </section>
    </div>
@endsection
