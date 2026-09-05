@extends('layouts.master')
@section('title', 'Theo dõi hồ sơ gia nhập Hội | DNT Bắc Ninh')
@section('robots', 'noindex, nofollow')
@push('styles') @vite('resources/css/business-application.css') @endpush
@section('content')
<div class="dnt-application-page"><div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="dnt-application-page__heading">
        <a class="dnt-text-link" href="{{ route('home') }}">Trang chủ</a>
        <h1>Theo dõi hồ sơ gia nhập Hội</h1>
        <p>{{ $business->name }} · Mã hồ sơ: <strong class="dnt-membership-code">{{ $business->application_code }}</strong></p>
    </div>
    <div class="dnt-business-application">
        <section>
            <h2>{{ \App\Models\Business::STATUS_LABELS[$business->status] }}</h2>
            <p>Chi hội đăng ký: {{ $business->chapter?->name ?: 'Chưa phân công' }}</p>
            <ol class="dnt-membership-progress">
                @foreach($steps as $step)<li class="{{ $step['done'] ? 'is-done' : ($step['active'] ? 'is-current' : '') }}" @if($step['active']) aria-current="step" @endif><span>{{ $loop->iteration }}</span><strong>{{ $step['label'] }}</strong><small>{{ $step['done'] ? 'Đã hoàn tất' : ($step['active'] ? 'Đang xử lý' : 'Chờ bước trước') }}</small></li>@endforeach
            </ol>
            @if($business->status === 'approved')
                <p>Mã hội viên: <strong>{{ $business->membership_code }}</strong>. Thông tin đăng nhập và hướng dẫn thiết lập mật khẩu được gửi đến email người đại diện. Nếu đã có tài khoản, tiếp tục dùng tài khoản hiện tại.</p>
                <a class="dnt-button dnt-button--dark" href="{{ route('login') }}">Đăng nhập cổng hội viên</a>
            @elseif($business->status === 'changes_requested')
                <p>Vui lòng bổ sung theo ghi chú bên dưới. Sau khi gửi lại, Văn phòng sẽ kiểm tra hồ sơ từ đầu.</p>
            @elseif($business->status === 'rejected')
                <p>Hồ sơ chưa được chấp thuận kết nạp. Anh/chị có thể liên hệ Văn phòng Hội để được giải đáp theo lý do bên dưới.</p>
            @else
                <p>Hồ sơ đang được xử lý theo ba cấp. Tài khoản hội viên được cấp sau khi Trưởng ban Hội viên chuẩn y.</p>
            @endif
            @if($editUrl)<a class="dnt-button dnt-button--dark" href="{{ $editUrl }}">Bổ sung và gửi lại hồ sơ</a>@endif
        </section>
        <section><h2>Đơn gia nhập Hội</h2>@if($documentUrl)<a class="dnt-text-link" href="{{ $documentUrl }}">Tải đơn đã gửi</a>@else<p>Chưa có bản đơn đã ký, đóng dấu.</p>@endif</section>
        <section><h2>Lịch sử xử lý</h2><ol class="dnt-membership-history">
            @forelse($business->statusHistories as $history)<li><strong>{{ \App\Models\Business::STATUS_LABELS[$history->to_status] ?? $history->to_status }}</strong><time>{{ $history->changed_at?->format('d/m/Y H:i') }}</time><p>{{ $history->reason }}</p></li>@empty<li>Hồ sơ chưa có cập nhật.</li>@endforelse
        </ol></section>
    </div>
</div></div>
@endsection
