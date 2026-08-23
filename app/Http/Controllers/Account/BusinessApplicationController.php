<?php

namespace App\Http\Controllers\Account;

use App\Actions\BusinessApplications\StoreSignedMembershipApplication;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Industry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class BusinessApplicationController extends Controller
{
    public function create(Request $request): View
    {
        $business = new Business([
            'email' => $request->user()->email,
            'phone' => $request->user()->phone,
        ]);

        return view('account.business-form', [
            'business' => $business,
            'categories' => $this->categories(),
            'industries' => $this->industries(),
            'selectedIndustryIds' => collect($request->old('industry_ids', []))->map(static fn ($id): string => (string) $id)->all(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request, StoreSignedMembershipApplication $storeSignedMembershipApplication): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $business = DB::transaction(function () use ($data, $user): Business {
            $business = Business::query()->create([
                ...$this->payload($data),
                'slug' => $this->availableSlug($data['name']),
                'submitted_by_user_id' => $user->id,
                'status' => 'draft',
            ]);

            $this->syncIndustries($business, $data['industry_ids']);

            if ($user->member) {
                $business->members()->syncWithoutDetaching([
                    $user->member->id => [
                        'role' => 'representative',
                        'job_title' => $data['job_title'] ?? null,
                        'is_primary' => true,
                        'status' => 'active',
                        'started_at' => now()->toDateString(),
                    ],
                ]);
            }

            $business->transitionTo('pending', $user->id, 'Hồ sơ doanh nghiệp được nộp từ cổng tài khoản.');

            return $business;
        });

        $this->syncLogo($request, $business);
        if ($request->hasFile('membership_application')) {
            $storeSignedMembershipApplication($business, $request->file('membership_application'));
        }

        return to_route('account.dashboard')->with('success', 'Hồ sơ doanh nghiệp đã được gửi. Hội sẽ kiểm tra và phản hồi trên trang này.');
    }

    public function edit(Request $request, Business $business): View
    {
        $this->ensureCanManage($request, $business);
        $this->ensureEditable($business);

        $business->load('industries');

        return view('account.business-form', [
            'business' => $business,
            'categories' => $this->categories(),
            'industries' => $this->industries(),
            'selectedIndustryIds' => collect($request->old('industry_ids', $business->industries->modelKeys()))->map(static fn ($id): string => (string) $id)->all(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Business $business, StoreSignedMembershipApplication $storeSignedMembershipApplication): RedirectResponse
    {
        $this->ensureCanManage($request, $business);
        $this->ensureEditable($business);
        $data = $this->validated($request, $business);

        DB::transaction(function () use ($business, $data, $request): void {
            $business->update($this->payload($data));

            $this->syncIndustries($business, $data['industry_ids']);

            $business->transitionTo('pending', $request->user()->id, 'Hồ sơ được bổ sung và gửi lại để duyệt.');
        });

        $this->syncLogo($request, $business);
        if ($request->hasFile('membership_application')) {
            $storeSignedMembershipApplication($business, $request->file('membership_application'));
        }

        return to_route('account.dashboard')->with('success', 'Hồ sơ đã được gửi lại để Hội duyệt.');
    }

    private function categories()
    {
        return BusinessCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
    }

    private function industries()
    {
        return Industry::query()
            ->memberGroups()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validated(Request $request, ?Business $business = null): array
    {
        $website = trim((string) $request->input('website', ''));
        if ($website !== '') {
            $request->merge([
                'website' => rtrim((string) preg_replace('#^https?://#i', '', $website), '/'),
            ]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['required', 'string', 'max:32', Rule::unique('businesses', 'tax_code')->ignore($business)],
            'business_type' => ['required', Rule::in(['limited', 'joint_stock', 'private', 'household', 'cooperative', 'other'])],
            'business_size' => ['required', Rule::in(['small', 'medium', 'large'])],
            'business_category_id' => ['nullable', Rule::exists('business_categories', 'id')->where('is_active', true)],
            'industry_ids' => ['required', 'array', 'min:1', 'max:5'],
            'industry_ids.*' => ['required', 'integer', 'distinct', Rule::exists('industries', 'id')->where('is_active', true)->where('is_member_group', true)],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'summary' => ['required', 'string', 'max:1000'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'membership_application' => [
                $business?->hasMedia('signed_membership_application') ? 'nullable' : 'required',
                'file',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])->max('10mb'),
            ],
            'confirm_information' => ['accepted'],
        ], [
            'confirm_information.accepted' => 'Anh/chị cần xác nhận thông tin đã khai là chính xác.',
            'industry_ids.required' => 'Anh/chị cần chọn ít nhất một nhóm nghề nghiệp.',
            'industry_ids.min' => 'Anh/chị cần chọn ít nhất một nhóm nghề nghiệp.',
            'industry_ids.max' => 'Mỗi doanh nghiệp chỉ được chọn tối đa 5 nhóm nghề nghiệp.',
            'website.regex' => 'Website không đúng định dạng.',
            'membership_application.required' => 'Anh/chị cần tải lên bản đơn gia nhập Hội đã ký và đóng dấu.',
            'membership_application.file' => 'Bản đơn gia nhập Hội phải là tệp hợp lệ.',
        ]);
    }

    private function payload(array $data): array
    {
        return [
            'business_category_id' => $data['business_category_id'] ?? null,
            'name' => $data['name'],
            'legal_name' => ($data['legal_name'] ?? null) ?: null,
            'tax_code' => $data['tax_code'],
            'business_type' => $data['business_type'],
            'business_size' => $data['business_size'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'website' => ($data['website'] ?? null) ?: null,
            'address' => $data['address'],
            'province' => $data['province'],
            'district' => ($data['district'] ?? null) ?: null,
            'summary' => $data['summary'],
            'representative_job_title' => ($data['job_title'] ?? null) ?: null,
        ];
    }

    /** @param array<int, int|string> $industryIds */
    private function syncIndustries(Business $business, array $industryIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $industryIds)));

        abort_if(count($ids) > 5, 422, 'Mỗi doanh nghiệp được chọn tối đa 5 nhóm nghề nghiệp.');

        $business->industries()->sync(
            collect($ids)->mapWithKeys(fn (int $id, int $index): array => [
                $id => ['is_primary' => $index === 0],
            ])->all(),
        );
    }

    private function syncLogo(Request $request, Business $business): void
    {
        if ($request->hasFile('logo')) {
            $business->addMediaFromRequest('logo')->toMediaCollection('logo', 'public_media');
        }
    }

    private function ensureCanManage(Request $request, Business $business): void
    {
        $user = $request->user();
        $isSubmitter = $business->submitted_by_user_id === $user->id;
        $isLinkedMember = $user->member
            && $business->members()->whereKey($user->member->id)->exists();

        abort_unless($isSubmitter || $isLinkedMember, 403);
    }

    private function ensureEditable(Business $business): void
    {
        abort_unless(in_array($business->status, ['draft', 'rejected'], true), 403, 'Hồ sơ đang được Hội xử lý nên chưa thể thay đổi.');
    }

    private function availableSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'doanh-nghiep';
        $slug = $base;
        $suffix = 2;

        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
