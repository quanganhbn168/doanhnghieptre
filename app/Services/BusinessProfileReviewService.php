<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Industry;
use App\Models\User;
use App\Support\BusinessPresentation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BusinessProfileReviewService
{
    public const FIELDS = [
        'name' => 'Tên giao dịch', 'legal_name' => 'Tên pháp lý', 'tax_code' => 'Mã số thuế',
        'business_type' => 'Loại hình', 'business_size' => 'Quy mô', 'business_category_id' => 'Nhóm doanh nghiệp',
        'representative_name' => 'Người đại diện', 'representative_job_title' => 'Chức danh',
        'phone' => 'Điện thoại', 'email' => 'Email doanh nghiệp', 'website' => 'Website',
        'address' => 'Địa chỉ', 'province' => 'Tỉnh / thành phố', 'district' => 'Khu vực',
        'summary' => 'Giới thiệu ngắn', 'description' => 'Giới thiệu chi tiết',
    ];

    public function submit(Business $business, User $actor, array $data): void
    {
        DB::transaction(function () use ($business, $actor, $data): void {
            $record = Business::query()->representedBy($actor)->where('status', 'approved')->lockForUpdate()->findOrFail($business->id);
            abort_unless($actor->hasApprovedAccount(), 403);
            $this->validate($record, $data);
            $payload = Arr::only($data, [...array_keys(self::FIELDS), 'industry_ids']);
            if (isset($payload['description'])) {
                $payload['description'] = (string) str($payload['description'])->sanitizeHtml();
            }
            app(MediaService::class)->syncSingle($record, 'pending_logo', $data['logo'] ?? null);
            $record->forceFill(['pending_profile' => $payload, 'profile_review_note' => null, 'profile_revision' => $record->profile_revision + 1])->save();
        });
    }

    public function review(Business $business, User $actor, bool $approve, ?string $note = null): void
    {
        abort_unless($actor->canReviewAssociation(), 403);
        DB::transaction(function () use ($business, $actor, $approve, $note): void {
            $record = Business::query()->lockForUpdate()->findOrFail($business->id);
            if ($record->status !== 'approved' || ! $record->pending_profile || $record->profile_revision !== $business->profile_revision || filled($record->profile_review_note)) {
                throw ValidationException::withMessages(['status' => 'Bản chỉnh sửa đã thay đổi. Vui lòng mở lại hồ sơ.']);
            }
            if (! $approve) {
                if (blank($note) || mb_strlen($note) > 2000) {
                    throw ValidationException::withMessages(['reason' => 'Nhập nội dung cần chỉnh sửa, tối đa 2.000 ký tự.']);
                }
                $record->forceFill(['profile_review_note' => trim($note)])->save();
            } else {
                $this->validate($record, $record->pending_profile);
                $record->fill(Arr::only($record->pending_profile, array_keys(self::FIELDS)))->save();
                $record->industries()->sync(collect($record->pending_profile['industry_ids'])->values()->mapWithKeys(fn ($id, $index) => [(int) $id => ['is_primary' => $index === 0]])->all());
                if ($logo = $record->getFirstMedia('pending_logo')) {
                    $logo->move($record, 'logo', 'public_media');
                }
                $record->forceFill(['pending_profile' => null, 'profile_review_note' => null])->save();
            }
            $record->statusHistories()->create([
                'from_status' => 'approved', 'to_status' => 'approved', 'changed_by' => $actor->id, 'changed_at' => now(),
                'reason' => $approve ? 'Văn phòng đã duyệt bản cập nhật hồ sơ doanh nghiệp.' : 'Bản cập nhật cần chỉnh sửa: '.trim($note),
            ]);
        });
    }

    public function changes(Business $business): array
    {
        $rows = [];
        foreach (self::FIELDS as $field => $label) {
            if (array_key_exists($field, $business->pending_profile ?? []) && (string) $business->$field !== (string) $business->pending_profile[$field]) {
                $rows[] = ['field' => $label, 'before' => $this->displayValue($field, $business->$field), 'after' => $this->displayValue($field, $business->pending_profile[$field])];
            }
        }
        if (isset($business->pending_profile['industry_ids'])) {
            $rows[] = ['field' => 'Khối ngành nghề', 'before' => $business->industries->pluck('name')->join(', '), 'after' => Industry::query()->whereIn('id', $business->pending_profile['industry_ids'])->pluck('name')->join(', ')];
        }

        return $rows;
    }

    private function displayValue(string $field, mixed $value): string
    {
        return match ($field) {
            'business_type' => BusinessPresentation::TYPES[$value] ?? '—',
            'business_size' => BusinessPresentation::SIZES[$value] ?? '—',
            'business_category_id' => BusinessCategory::query()->find($value)?->name ?: '—',
            default => strip_tags((string) $value) ?: '—',
        };
    }

    private function validate(Business $business, array $data): void
    {
        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['required', 'string', 'max:32', Rule::unique('businesses', 'tax_code')->ignore($business->id)],
            'business_type' => ['required', Rule::in(array_keys(BusinessPresentation::TYPES))],
            'business_size' => ['required', Rule::in(array_keys(BusinessPresentation::SIZES))],
            'business_category_id' => ['nullable', Rule::exists('business_categories', 'id')->where('is_active', true)],
            'representative_name' => ['required', 'string', 'max:255'],
            'representative_job_title' => ['nullable', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:30'], 'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i'],
            'address' => ['required', 'string', 'max:500'], 'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'summary' => ['required', 'string', 'max:1000'], 'description' => ['nullable', 'string', 'max:50000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'industry_ids' => ['required', 'array', 'min:1', 'max:5'],
            'industry_ids.*' => ['integer', 'distinct', Rule::exists('industries', 'id')->where('is_member_group', true)->where('is_active', true)],
        ])->validate();
    }
}
