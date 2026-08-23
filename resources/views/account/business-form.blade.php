@extends('layouts.master')

@section('title', $mode === 'create' ? 'Đăng ký doanh nghiệp | DNT Bắc Ninh' : 'Bổ sung hồ sơ doanh nghiệp | DNT Bắc Ninh')
@section('meta_description', 'Gửi hồ sơ doanh nghiệp để kết nối với Hội Doanh nhân trẻ Bắc Ninh.')
@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $isEditing = $mode === 'edit';
        $selectedIndustryId = old('industry_id', $business->industries->first()?->id);
    @endphp
    <div class="dnt-application-page">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-application-page__heading">
                <a class="dnt-text-link" href="{{ route('account.dashboard') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại tài khoản</a>
                <h1>{{ $isEditing ? 'Bổ sung hồ sơ doanh nghiệp' : 'Đăng ký doanh nghiệp' }}</h1>
                <p>Hồ sơ được Hội kiểm tra trước khi xuất hiện trên danh bạ. Khi hồ sơ được duyệt, người đại diện nộp hồ sơ sẽ trở thành hội viên.</p>
            </div>

            <form class="dnt-business-application" method="POST" action="{{ $isEditing ? route('account.businesses.update', $business) : route('account.businesses.store') }}" enctype="multipart/form-data">
                @csrf
                @if($isEditing) @method('PATCH') @endif

                <section>
                    <h2>Thông tin pháp lý và nhận diện</h2>
                    <div class="dnt-business-application__grid">
                        <label>Tên giao dịch <b>*</b><input class="ui-input" name="name" value="{{ old('name', $business->name) }}" required autocomplete="organization"></label>
                        <label>Tên pháp lý<input class="ui-input" name="legal_name" value="{{ old('legal_name', $business->legal_name) }}"></label>
                        <label>Mã số thuế <b>*</b><input class="ui-input" name="tax_code" value="{{ old('tax_code', $business->tax_code) }}" required></label>
                        <label>Loại hình doanh nghiệp <b>*</b><select class="ui-select" name="business_type" required><option value="">Chọn loại hình</option>@foreach(['limited' => 'Công ty TNHH', 'joint_stock' => 'Công ty cổ phần', 'private' => 'Doanh nghiệp tư nhân', 'household' => 'Hộ kinh doanh', 'cooperative' => 'Hợp tác xã', 'other' => 'Loại hình khác'] as $value => $label)<option value="{{ $value }}" @selected(old('business_type', $business->business_type) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label>Logo doanh nghiệp<input class="ui-input" name="logo" type="file" accept="image/png,image/jpeg,image/webp"><small>PNG, JPG hoặc WebP, tối đa 5 MB.</small></label>
                    </div>
                </section>

                <section>
                    <h2>Lĩnh vực và quy mô</h2>
                    <div class="dnt-business-application__grid">
                        <label>Nhóm doanh nghiệp<select class="ui-select" name="business_category_id"><option value="">Chọn nhóm</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('business_category_id', $business->business_category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                        <label>Lĩnh vực hoạt động chính<select class="ui-select" name="industry_id"><option value="">Chọn lĩnh vực</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string) $selectedIndustryId === (string) $industry->id)>{{ $industry->name }}</option>@endforeach</select></label>
                        <label>Quy mô <b>*</b><select class="ui-select" name="business_size" required><option value="">Chọn quy mô</option>@foreach(['small' => 'Quy mô nhỏ', 'medium' => 'Quy mô vừa', 'large' => 'Quy mô lớn'] as $value => $label)<option value="{{ $value }}" @selected(old('business_size', $business->business_size) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label>Chức danh của người đại diện<input class="ui-input" name="job_title" value="{{ old('job_title', $business->representative_job_title) }}" placeholder="Ví dụ: Giám đốc điều hành"></label>
                    </div>
                </section>

                <section>
                    <h2>Thông tin liên hệ</h2>
                    <div class="dnt-business-application__grid">
                        <label>Điện thoại <b>*</b><input class="ui-input" name="phone" type="tel" value="{{ old('phone', $business->phone) }}" required autocomplete="tel"></label>
                        <label>Email doanh nghiệp <b>*</b><input class="ui-input" name="email" type="email" value="{{ old('email', $business->email) }}" required autocomplete="email"></label>
                        <label>Website<input class="ui-input" name="website" type="url" value="{{ old('website', $business->website) }}" placeholder="https://"></label>
                        <label>Tỉnh / thành phố <b>*</b><input class="ui-input" name="province" value="{{ old('province', $business->province) }}" required></label>
                        <label>Quận / huyện<input class="ui-input" name="district" value="{{ old('district', $business->district) }}"></label>
                        <label class="dnt-business-application__full">Địa chỉ <b>*</b><textarea class="ui-input" name="address" required>{{ old('address', $business->address) }}</textarea></label>
                    </div>
                </section>

                <section>
                    <h2>Giới thiệu ngắn</h2>
                    <label>Doanh nghiệp đang cung cấp sản phẩm, dịch vụ hoặc năng lực gì? <b>*</b><textarea class="ui-input" name="summary" required maxlength="1000">{{ old('summary', $business->summary) }}</textarea><small>Tối đa 1.000 ký tự; nội dung này sẽ được Hội sử dụng để giới thiệu trong danh bạ.</small></label>
                </section>

                @if($errors->any())
                    <div class="ui-alert ui-alert--error"><strong>Hồ sơ chưa gửi được.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <label class="dnt-business-application__confirm"><input name="confirm_information" type="checkbox" value="1" @checked(old('confirm_information')) required> <span>Tôi xác nhận thông tin doanh nghiệp là chính xác và đồng ý để Hội liên hệ xác thực.</span></label>
                <div class="dnt-business-application__actions"><a class="dnt-button dnt-button--outline-dark" href="{{ route('account.dashboard') }}">Hủy</a><button class="dnt-button dnt-button--dark" type="submit">{{ $isEditing ? 'Gửi lại để duyệt' : 'Gửi hồ sơ doanh nghiệp' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div>
            </form>
        </div>
    </div>
@endsection
