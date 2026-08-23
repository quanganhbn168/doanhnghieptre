@extends('layouts.master')

@section('title', 'Chính sách bảo mật — '.$website['name'])
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::webPage('Chính sách bảo mật', 'Chính sách bảo mật của THT Media VN.', route('policies.privacy'))])

@section('content')
<section class="page-hero"><div class="container mx-auto px-4 sm:px-6 lg:px-8"><h1>Chính sách bảo mật</h1><p>Thông tin về cách THT Media tiếp nhận và sử dụng dữ liệu liên hệ.</p></div></section>
<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Chính sách bảo mật']]" />
<section class="section-space bg-soft">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8"><article class="content-card policy-content mx-auto">
        <h1 class="text-2xl">Chính sách bảo mật</h1>
        <p>THT MEDIA VN thu thập thông tin liên hệ mà khách hàng cung cấp để tiếp nhận yêu cầu, tư vấn và triển khai dịch vụ phù hợp.</p>
        <h2 class="text-lg">Thông tin được sử dụng</h2>
        <p>Thông tin có thể gồm họ tên, số điện thoại, email, đơn vị công tác và nội dung cần tư vấn.</p>
        <h2 class="text-lg">Bảo vệ và chia sẻ dữ liệu</h2>
        <p>Thông tin chỉ được sử dụng trong phạm vi cần thiết để liên hệ, tư vấn và thực hiện dịch vụ đã thống nhất.</p>
        <h2 class="text-lg">Yêu cầu cập nhật thông tin</h2>
        <p class="mb-0">Khách hàng có thể liên hệ qua <a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a> hoặc hotline {{ $website['phone'] }} để yêu cầu hỗ trợ.</p>
    </article></div>
</section>
@endsection
