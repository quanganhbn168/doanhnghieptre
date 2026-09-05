<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusinessApprovalService
{
    public function approveForChapter(Business $business, User $reviewer, int $chapterId): void
    {
        abort_unless($reviewer->canReviewAssociation(), 403);
        DB::transaction(function () use ($business, $reviewer, $chapterId): void {
            $business = Business::query()->lockForUpdate()->findOrFail($business->id);
            $this->requireStatus($business, ['pending']);
            $this->requireDocument($business);
            abort_unless(BusinessChapter::query()->whereKey($chapterId)->where('is_active', true)->exists(), 422, 'Chi hội không còn hoạt động.');
            $business->forceFill([
                'business_chapter_id' => $chapterId,
                'association_approved_at' => now(),
                'association_approved_by' => $reviewer->id,
            ])->save();
            $business->transitionTo('chapter_pending', $reviewer->id, 'Hội đã duyệt hồ sơ và chuyển Chi hội tiếp nhận.');
        });
    }

    public function receive(Business $business, User $reviewer): void
    {
        DB::transaction(function () use ($business, $reviewer): void {
            $business = Business::query()->lockForUpdate()->findOrFail($business->id);
            abort_unless($reviewer->canReceiveChapter() && $reviewer->managedChapters()->where('is_active', true)->whereKey($business->business_chapter_id)->exists(), 403);
            $this->requireStatus($business, ['chapter_pending']);
            $this->requireDocument($business);
            if (! $business->association_approved_at) {
                throw ValidationException::withMessages(['status' => 'Hồ sơ chưa được Hội duyệt.']);
            }
            $reviewerId = $reviewer->id;
            $business->loadMissing('submittedBy');
            $member = $this->memberForRepresentative($business, $reviewerId);

            if ($member) {
                $business->members()->syncWithoutDetaching([
                    $member->id => [
                        'role' => 'representative',
                        'job_title' => $business->representative_job_title,
                        'is_primary' => true,
                        'status' => 'active',
                        'started_at' => now()->toDateString(),
                    ],
                ]);
            }

            $business->forceFill(['membership_code' => $business->membership_code ?: 'DNTBN-DN-'.str_pad((string) $business->id, 6, '0', STR_PAD_LEFT)])->save();
            $business->transitionTo('approved', $reviewerId, 'Chi hội đã tiếp nhận; doanh nghiệp được công nhận là hội viên chính thức.');
        });
    }

    public function requestChanges(Business $business, User $reviewer, string $reason): void
    {
        DB::transaction(function () use ($business, $reviewer, $reason): void {
            $business = Business::query()->lockForUpdate()->findOrFail($business->id);
            abort_unless($reviewer->canReviewAssociation() || ($business->status === 'chapter_pending' && $reviewer->canReceiveChapter() && $reviewer->managedChapters()->where('is_active', true)->whereKey($business->business_chapter_id)->exists()), 403);
            $this->requireStatus($business, ['pending', 'chapter_pending']);
            if (trim($reason) === '' || mb_strlen($reason) > 2000) {
                throw ValidationException::withMessages(['reason' => 'Nhập lý do cần bổ sung, tối đa 2.000 ký tự.']);
            }
            $business->transitionTo('rejected', $reviewer->id, trim($reason));
        });
    }

    private function requireStatus(Business $business, array $statuses): void
    {
        if (! in_array($business->status, $statuses, true)) {
            throw ValidationException::withMessages(['status' => 'Hồ sơ đã đổi trạng thái. Vui lòng tải lại trang.']);
        }
    }

    private function requireDocument(Business $business): void
    {
        if (! $business->hasMedia('signed_membership_application')) {
            throw ValidationException::withMessages(['membership_application' => 'Cần có đơn gia nhập Hội đã ký, đóng dấu trước khi duyệt.']);
        }
    }

    private function memberForRepresentative(Business $business, int $reviewerId): ?Member
    {
        $user = $business->submitted_by_user_id ? User::query()->lockForUpdate()->find($business->submitted_by_user_id) : null;
        if (! $user) {
            return null;
        }

        $member = Member::query()->where('user_id', $user->id)->first();
        $previousStatus = $member?->status;

        if ($member) {
            $member->update([
                'full_name' => $business->representative_name ?: $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => 'approved',
                'joined_at' => $member->joined_at ?? now(),
                'approved_at' => now(),
                'approved_by' => $reviewerId,
            ]);
        } else {
            $member = Member::query()->create([
                'user_id' => $user->id,
                'member_code' => 'DNT-REP-'.$user->id,
                'full_name' => $business->representative_name ?: $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $business->address,
                'province' => $business->province,
                'district' => $business->district,
                'introduction' => $business->summary,
                'status' => 'approved',
                'joined_at' => now(),
                'approved_at' => now(),
                'approved_by' => $reviewerId,
            ]);
        }

        DB::table('member_status_histories')->insert([
            'member_id' => $member->id,
            'from_status' => $previousStatus,
            'to_status' => 'approved',
            'reason' => 'Liên kết người đại diện với doanh nghiệp hội viên đã được tiếp nhận.',
            'changed_by' => $reviewerId,
            'changed_at' => now(),
        ]);

        return $member;
    }
}
