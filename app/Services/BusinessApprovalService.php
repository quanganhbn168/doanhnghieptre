<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class BusinessApprovalService
{
    public function approve(Business $business, int $reviewerId): void
    {
        DB::transaction(function () use ($business, $reviewerId): void {
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

            $business->transitionTo('approved', $reviewerId, $member
                ? 'Hồ sơ doanh nghiệp đã được duyệt; người nộp được công nhận là hội viên đại diện.'
                : 'Hồ sơ doanh nghiệp đã được duyệt.');
        });
    }

    private function memberForRepresentative(Business $business, int $reviewerId): ?Member
    {
        $user = $business->submittedBy;
        if (! $user) {
            return null;
        }

        $member = Member::query()->where('user_id', $user->id)->first();
        $previousStatus = $member?->status;

        if ($member) {
            $member->update([
                'full_name' => $user->name,
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
                'member_code' => $this->nextMemberCode(),
                'full_name' => $user->name,
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
            'reason' => 'Duyệt hồ sơ doanh nghiệp do hội viên đại diện nộp.',
            'changed_by' => $reviewerId,
            'changed_at' => now(),
        ]);

        return $member;
    }

    private function nextMemberCode(): string
    {
        $next = ((int) Member::query()->max('id')) + 1;

        return 'DNTBN-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
