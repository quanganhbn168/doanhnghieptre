@extends('layouts.master')
@section('title', 'Tài khoản của tôi | DNT Bắc Ninh')
@section('meta_description', 'Theo dõi hồ sơ hội viên và các doanh nghiệp đã đăng ký với DNT Bắc Ninh.')
@section('robots', 'noindex, nofollow')

@section('content')
    <div class="dnt-account">
        <section class="dnt-account__hero">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div>
                    <p class="dnt-account__welcome">Hồ sơ hội viên doanh nghiệp</p>
                    <h1>Xin chào, {{ $user->name }}</h1>
                    <p>Quản lý hồ sơ của anh/chị và theo dõi doanh nghiệp đã gửi tới Hội Doanh nhân trẻ Bắc Ninh.</p>
                </div>
                @if($hasApprovedBusiness)
                    <a class="dnt-button dnt-button--gold" href="{{ url('/thanh-vien') }}">Vào cổng doanh nghiệp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @else
                    <a class="dnt-button dnt-button--gold" href="{{ route('membership.create') }}">Nộp hồ sơ doanh nghiệp <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                @endif
            </div>
        </section>

        <section class="dnt-account__content">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="dnt-account__metrics" aria-label="Tổng quan hồ sơ">
                    <article><span>Doanh nghiệp đã gửi</span><strong>{{ $businesses->count() }}</strong></article>
                    <article><span>Đang chờ duyệt</span><strong>{{ $businesses->whereIn('status', ['pending', 'chapter_pending'])->count() }}</strong></article>
                    <article><span>Hội viên chính thức</span><strong>{{ $businesses->where('status', 'approved')->count() }}</strong></article>
                </div>

                <div class="dnt-account__grid">
                    <section class="dnt-account-panel dnt-account-panel--profile">
                        <div class="dnt-account-panel__heading"><h2>Người đại diện</h2></div>
                        <dl class="dnt-account-profile">
                            <div><dt>Họ và tên</dt><dd>{{ $user->name }}</dd></div>
                            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                            <div><dt>Số điện thoại</dt><dd>{{ $user->phone ?: 'Chưa cập nhật' }}</dd></div>

                        </dl>
                        <p class="dnt-account-panel__hint">Tư cách hội viên và mã hội viên thuộc về từng doanh nghiệp. Người đại diện sử dụng thông tin đăng nhập để theo dõi và quản lý hồ sơ.</p>
                    </section>

                    <section class="dnt-account-panel">
                        <div class="dnt-account-panel__heading"><h2>Việc cần làm</h2></div>
                        <ul class="dnt-account-checklist">
                            <li class="{{ $businesses->isNotEmpty() ? 'is-done' : '' }}"><i class="fa-solid {{ $businesses->isNotEmpty() ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Nộp hồ sơ đăng ký doanh nghiệp.</span></li>
                            <li class="{{ $businesses->whereIn('status', ['chapter_pending', 'approved'])->isNotEmpty() ? 'is-done' : '' }}"><i class="fa-solid {{ $businesses->whereIn('status', ['chapter_pending', 'approved'])->isNotEmpty() ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Hội duyệt doanh nghiệp và xác thực thông tin.</span></li>
                            <li class="{{ $hasApprovedBusiness ? 'is-done' : '' }}"><i class="fa-solid {{ $hasApprovedBusiness ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i><span>Chi hội tiếp nhận doanh nghiệp trở thành hội viên.</span></li>
                            <li><i class="fa-solid fa-circle" aria-hidden="true"></i><span>Tham gia sự kiện và cập nhật các cơ hội giao thương phù hợp.</span></li>
                        </ul>
                    </section>
                </div>

                <section class="dnt-account-panel dnt-account-businesses">
                    <div class="dnt-account-panel__heading">
                        <div><h2>Doanh nghiệp của tôi</h2><p>Doanh nghiệp xuất hiện trên danh bạ sau khi Hội duyệt và Chi hội tiếp nhận.</p></div>
                        <a class="dnt-text-link" href="{{ $hasApprovedBusiness ? url('/thanh-vien') : route('membership.create') }}">{{ $hasApprovedBusiness ? 'Mở cổng doanh nghiệp' : 'Thêm doanh nghiệp' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
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
                                @if($business->membership_code)<p>Mã hội viên: <strong>{{ $business->membership_code }}</strong> · {{ $business->chapter?->name }}</p>@endif
                                <p>{{ $business->category?->name ?: 'Đang cập nhật nhóm doanh nghiệp' }} · {{ $business->industries->first()?->name ?: 'Đang cập nhật lĩnh vực' }}</p>
                                @if($business->status === 'rejected')
                                    <p class="dnt-account-business-card__notice">Hồ sơ cần được bổ sung. Anh/chị hãy chỉnh sửa rồi gửi lại để Hội duyệt.</p>
                                @elseif($business->status === 'chapter_pending')
                                    <p class="dnt-account-business-card__notice">Hội đã duyệt. Hồ sơ đang chờ {{ $business->chapter?->name ?: 'Chi hội' }} tiếp nhận.</p>
                                @elseif($business->status === 'pending')
                                    <p class="dnt-account-business-card__notice">Hồ sơ đã gửi và đang được Hội kiểm tra.</p>
                                @endif
                                @if($signedMembershipApplication = $business->getFirstMedia('signed_membership_application'))
                                    <p class="dnt-account-business-card__notice">Đơn gia nhập Hội: <a class="dnt-text-link" href="{{ route('business.membership-application.download', $business) }}">{{ $signedMembershipApplication->file_name }} <i class="fa-solid fa-download" aria-hidden="true"></i></a></p>
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
                            <h3>Chưa có hồ sơ doanh nghiệp</h3><p>Gửi đơn gia nhập Hội để Hội xét duyệt và Chi hội tiếp nhận doanh nghiệp.</p><a class="dnt-button dnt-button--dark" href="{{ route('membership.create') }}">Bắt đầu đăng ký</a>
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
