@extends('layouts.master')

@section('title', $mode === 'create' ? 'Đăng ký hội viên | DNT Bắc Ninh' : 'Bổ sung hồ sơ hội viên | DNT Bắc Ninh')
@section('meta_description', 'Gửi hồ sơ doanh nghiệp để kết nối với Hội Doanh nhân trẻ Bắc Ninh.')
@section('robots', 'noindex, nofollow')

@push('styles')
    @vite('resources/css/business-application.css')
@endpush

@push('scripts')
    <script src="{{ asset('vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
    @vite('resources/js/business-application.js')
@endpush

@section('content')
    <div class="dnt-application-page">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dnt-application-page__heading">
                <a class="dnt-text-link" href="{{ auth()->check() ? route('account.dashboard') : route('home') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ auth()->check() ? 'Theo dõi hồ sơ' : 'Trang chủ' }}</a>
                <h1>{{ $mode === 'edit' ? 'Bổ sung hồ sơ hội viên' : 'Đăng ký hội viên' }}</h1>
                <p>Mỗi hội viên là một doanh nghiệp. Gửi thông tin và đơn gia nhập Hội tại đây; Hội xét duyệt, sau đó Chi hội tiếp nhận doanh nghiệp.</p>
            </div>

            <form class="dnt-business-application" method="POST" action="{{ $mode === 'edit' ? route('account.businesses.update', $business) : route('membership.store') }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif

                <section>
                    <div class="dnt-business-application__section-head">
                        <h2>Thông tin pháp lý và nhận diện</h2>
                        <p>Thông tin này dùng để Hội đối chiếu hồ sơ doanh nghiệp.</p>
                    </div>
                    <div class="dnt-business-application__grid">
                        <label><span>Tên giao dịch <b>*</b></span><input class="ui-input" name="name" value="{{ old('name', $business->name) }}" required autocomplete="organization"></label>
                        <label><span>Tên pháp lý</span><input class="ui-input" name="legal_name" value="{{ old('legal_name', $business->legal_name) }}"></label>
                        <label><span>Mã số thuế <b>*</b></span><input class="ui-input" name="tax_code" value="{{ old('tax_code', $business->tax_code) }}" required></label>
                        <label><span>Loại hình doanh nghiệp <b>*</b></span><select class="ui-select" name="business_type" required><option value="">Chọn loại hình</option>@foreach(['limited' => 'Công ty TNHH', 'joint_stock' => 'Công ty cổ phần', 'private' => 'Doanh nghiệp tư nhân', 'household' => 'Hộ kinh doanh', 'cooperative' => 'Hợp tác xã', 'other' => 'Loại hình khác'] as $value => $label)<option value="{{ $value }}" @selected(old('business_type', $business->business_type) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label class="dnt-business-application__full"><span>Logo doanh nghiệp</span><input class="ui-input" name="logo" type="file" accept="image/png,image/jpeg,image/webp"><small>PNG, JPG hoặc WebP; tối đa 5 MB.</small></label>
                    </div>
                </section>

                <section>
                    <div class="dnt-business-application__section-head">
                        <h2>Khối ngành nghề và quy mô</h2>
                        @if(filled($legacyIndustryNames ?? null))<p>Phân loại cũ: {{ $legacyIndustryNames }}. Anh/chị vui lòng chọn lại theo danh mục khối ngành nghề hiện tại.</p>@endif
                        <p>Chọn khối ngành nghề chính và quy mô hiện tại của doanh nghiệp.</p>
                    </div>
                    <div class="dnt-business-application__grid">
                        <label><span>Nhóm doanh nghiệp</span><select class="ui-select" name="business_category_id"><option value="">Chọn nhóm</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('business_category_id', $business->business_category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                        <label><span>Quy mô <b>*</b></span><select class="ui-select" name="business_size" required><option value="">Chọn quy mô</option>@foreach(['small' => 'Quy mô nhỏ', 'medium' => 'Quy mô vừa', 'large' => 'Quy mô lớn'] as $value => $label)<option value="{{ $value }}" @selected(old('business_size', $business->business_size) === $value)>{{ $label }}</option>@endforeach</select></label>
                        <div class="dnt-business-application__full dnt-industry-picker">
                            <label for="industry_ids"><span>Khối ngành nghề <b>*</b></span></label>
                            <select id="industry_ids" name="industry_ids[]" multiple required
                                data-industry-picker data-selected="{{ json_encode(array_values($selectedIndustryIds)) }}"
                                aria-describedby="industry-help industry-count{{ $errors->has('industry_ids*') ? ' industry-error' : '' }}"
                                aria-invalid="{{ $errors->has('industry_ids*') ? 'true' : 'false' }}">
                                @foreach($industries as $industry)
                                    <option value="{{ $industry->id }}" @selected(in_array((string) $industry->id, $selectedIndustryIds, true))>{{ $industry->name }}</option>
                                @endforeach
                            </select>
                            <div class="dnt-industry-picker__help">
                                <small id="industry-help">Chọn từ 1 đến 5 khối. Khối chọn đầu tiên là khối chính.</small>
                                <small id="industry-count" role="status" aria-live="polite" aria-atomic="true"></small>
                            </div>
                            @if($errors->has('industry_ids*'))
                                <p id="industry-error" class="ui-error">{{ $errors->first('industry_ids*') }}</p>
                            @endif
                        </div>
                        <label class="dnt-business-application__full"><span>Chi hội mong muốn tham gia</span><select class="ui-select" name="business_chapter_id"><option value="">Để Hội phân công Chi hội phù hợp</option>@foreach($chapters as $chapter)<option value="{{ $chapter->id }}" @selected((string) old('business_chapter_id', $business->business_chapter_id) === (string) $chapter->id)>{{ $chapter->name }}</option>@endforeach</select><small>Hội xác nhận Chi hội tiếp nhận khi duyệt hồ sơ.</small></label>
                    </div>
                </section>

                <section>
                    <div class="dnt-business-application__section-head">
                        <h2>Thông tin liên hệ</h2>
                        <p>Hội dùng các thông tin này để xác minh và liên hệ khi cần.</p>
                    </div>
                    <div class="dnt-business-application__grid">
                        <label><span>Điện thoại <b>*</b></span><input class="ui-input" name="phone" type="tel" value="{{ old('phone', $business->phone) }}" required autocomplete="tel"></label>
                        <label><span>Email doanh nghiệp <b>*</b></span><input class="ui-input" name="email" type="email" value="{{ old('email', $business->email) }}" required autocomplete="email"></label>
                        <label><span>Website</span><input class="ui-input" name="website" type="text" inputmode="url" autocomplete="url" value="{{ old('website', $business->website) }}" placeholder="vinhgiang.com.vn"></label>
                        <label><span>Tỉnh / thành phố <b>*</b></span><input class="ui-input" name="province" value="{{ old('province', $business->province) }}" placeholder="Bắc Ninh hoặc tỉnh/thành phố khác" required><small>Hội viên có thể hoạt động ngoài tỉnh Bắc Ninh.</small></label>
                        <label class="dnt-business-application__full"><span>Khu vực (quận/huyện, xã/phường)</span><input class="ui-input" name="district" value="{{ old('district', $business->district) }}" placeholder="Ví dụ: phường Kinh Bắc"></label>
                        <label class="dnt-business-application__full"><span>Địa chỉ <b>*</b></span><textarea class="ui-input" name="address" required>{{ old('address', $business->address) }}</textarea></label>
                    </div>
                </section>

                <section>
                    <div class="dnt-business-application__section-head">
                        <h2>Giới thiệu ngắn</h2>
                        <p>Nêu sản phẩm, dịch vụ hoặc năng lực nổi bật của doanh nghiệp.</p>
                    </div>
                    <label><span>Thông tin giới thiệu <b>*</b></span><textarea class="ui-input" name="summary" required maxlength="1000">{{ old('summary', $business->summary) }}</textarea><small>Tối đa 1.000 ký tự; nội dung này sẽ được Hội sử dụng để giới thiệu trong danh bạ.</small></label>
                </section>

                <section>
                    <div class="dnt-business-application__section-head">
                        <h2>Người đại diện doanh nghiệp</h2>
                        <p>Người đại diện theo dõi hồ sơ và làm việc với Hội thay mặt doanh nghiệp.</p>
                    </div>
                    <div class="dnt-business-application__grid">
                        <label><span>Họ và tên người đại diện <b>*</b></span><input class="ui-input" name="representative_name" value="{{ old('representative_name', $business->representative_name ?: auth()->user()?->name) }}" required autocomplete="name" maxlength="255"></label>
                        <label><span>Chức danh</span><input class="ui-input" name="job_title" value="{{ old('job_title', $business->representative_job_title) }}" placeholder="Ví dụ: Giám đốc điều hành"></label>
                        @guest
                            <label class="dnt-business-application__full"><span>Email đăng nhập theo dõi hồ sơ <b>*</b></span><input class="ui-input" name="login_email" type="email" value="{{ old('login_email') }}" required autocomplete="username"><small>Đã có thông tin đăng nhập? <a class="dnt-text-link" href="{{ route('login') }}">Đăng nhập để dùng lại</a>.</small></label>
                            <label><span>Mật khẩu <b>*</b></span><input class="ui-input" name="password" type="password" required minlength="8" autocomplete="new-password"><small>Tối thiểu 8 ký tự. Dùng để theo dõi và bổ sung hồ sơ ngay sau khi gửi.</small></label>
                            <label><span>Nhập lại mật khẩu <b>*</b></span><input class="ui-input" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"></label>
                        @endguest
                    </div>
                </section>

                <section class="dnt-business-application__membership-application">
                    <div class="dnt-business-application__section-head">
                        <h2>Đơn gia nhập Hội</h2>
                        <p>Tải mẫu đơn, ký và đóng dấu, sau đó gửi bản chụp hoặc bản quét cùng hồ sơ.</p>
                    </div>
                    <div class="dnt-business-application__document-grid">
                        <div class="dnt-business-application__template">
                            <strong>1. Tải và hoàn thiện mẫu đơn</strong>
                            <p>In đơn, ký tên và đóng dấu doanh nghiệp trước khi quét hoặc chụp lại.</p>
                            <a class="dnt-button dnt-button--outline-dark" href="{{ asset('downloads/don-gia-nhap-hoi-082026.docx') }}" download>Tải mẫu đơn (.docx) <i class="fa-solid fa-download" aria-hidden="true"></i></a>
                        </div>
                        <div class="dnt-business-application__upload">
                            <label for="membership_application"><span>2. Tải bản đơn đã ký, đóng dấu @if(! $signedMembershipApplication)<b>*</b>@endif</span></label>
                            <input id="membership_application" class="ui-input" name="membership_application" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" @required(! $signedMembershipApplication)>
                            <small>Nhận PDF, JPG hoặc PNG; tối đa 10 MB.</small>
                            @if($signedMembershipApplication)
                                <p>Đã nhận tệp <a class="dnt-text-link" href="{{ route('business.membership-application.download', $business) }}">{{ $signedMembershipApplication->file_name }} <i class="fa-solid fa-download" aria-hidden="true"></i></a>. Chỉ tải lại khi cần thay thế.</p>
                            @endif
                        </div>
                    </div>
                </section>

                @if($errors->any())
                    <div class="ui-alert ui-alert--error"><strong>Hồ sơ chưa gửi được.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="dnt-business-application__footer">
                    <label class="dnt-business-application__confirm"><input name="confirm_information" type="checkbox" value="1" @checked(old('confirm_information')) required> <span>Tôi xác nhận thông tin doanh nghiệp là chính xác và đồng ý để Hội liên hệ xác thực.</span></label>
                    <div class="dnt-business-application__actions"><a class="dnt-button dnt-button--outline-dark" href="{{ auth()->check() ? route('account.dashboard') : route('home') }}">Quay lại</a><button class="dnt-button dnt-button--dark" type="submit">{{ $mode === 'edit' ? 'Gửi lại để duyệt' : 'Gửi đơn đăng ký hội viên' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div>
                </div>
            </form>
        </div>
    </div>
@endsection
