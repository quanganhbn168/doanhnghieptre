<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Member;
use App\Models\TradePost;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TradePostService
{
    public function submit(User $actor, array $data, ?TradePost $post = null): TradePost
    {
        abort_unless($actor->hasApprovedAccount(), 403);

        return DB::transaction(function () use ($actor, $data, $post): TradePost {
            $business = Business::query()->representedBy($actor)->where('status', 'approved')
                ->lockForUpdate()->find($data['business_id'] ?? null);
            if (! $business) {
                throw ValidationException::withMessages(['business_id' => 'Vui lòng chọn doanh nghiệp hội viên do bạn đại diện.']);
            }
            if ($post) {
                $post = TradePost::query()->ownedBy($actor)->lockForUpdate()->findOrFail($post->id);
            } else {
                $member = Member::query()->firstOrCreate(['user_id' => $actor->id], [
                    'member_code' => 'DNT-REP-'.$actor->id,
                    'full_name' => $actor->name, 'email' => $actor->email, 'phone' => $actor->phone,
                    'status' => 'approved',
                ]);
                $post = new TradePost(['member_id' => $member->id, 'slug' => (Str::slug($data['title']) ?: 'tin-giao-thuong').'-'.Str::lower((string) Str::ulid())]);
            }
            $post->fill(Arr::only($data, ['business_id', 'type', 'title', 'summary', 'content', 'budget_label', 'location_label', 'contact_name', 'contact_phone', 'contact_email', 'expires_at']));
            $post->fill(['status' => 'pending', 'approved_at' => null, 'closed_at' => null, 'review_note' => null, 'reviewed_by' => null]);
            $post->save();

            return $post;
        });
    }

    public function moderate(TradePost $post, User $actor, string $decision, ?string $note = null): void
    {
        abort_unless($actor->canManageAssociation(), 403);
        DB::transaction(function () use ($post, $actor, $decision, $note): void {
            // Lock the business first, matching member submission lock order.
            $business = Business::query()->lockForUpdate()->find($post->business_id);
            $record = TradePost::query()->lockForUpdate()->findOrFail($post->id);
            if ($record->business_id !== $post->business_id) {
                throw ValidationException::withMessages(['status' => 'Doanh nghiệp đăng tin đã thay đổi. Vui lòng tải lại tin trước khi duyệt.']);
            }
            $allowed = match ($decision) {
                'approve', 'reject' => ['pending'],
                'close' => ['approved', 'pending'],
                'submit' => ['draft', 'rejected', 'closed'],
                default => [],
            };
            if (! in_array($record->status, $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'Tin đã đổi trạng thái. Vui lòng tải lại danh sách.']);
            }
            if ($decision === 'approve' && (! $business || $business->status !== 'approved' || ($record->expires_at && $record->expires_at->isPast()))) {
                throw ValidationException::withMessages(['status' => 'Chỉ duyệt tin còn hiệu lực của doanh nghiệp hội viên đã được duyệt.']);
            }
            if ($decision === 'reject' && (blank($note) || mb_strlen($note) > 2000)) {
                throw ValidationException::withMessages(['review_note' => 'Nhập lý do yêu cầu bổ sung, tối đa 2.000 ký tự.']);
            }
            $record->update([
                'status' => match ($decision) {
                    'approve' => 'approved', 'reject' => 'rejected', 'close' => 'closed', default => 'pending'
                },
                'approved_at' => $decision === 'approve' ? now() : null,
                'closed_at' => $decision === 'close' ? now() : null,
                'review_note' => $decision === 'reject' ? trim($note) : null,
                'reviewed_by' => $actor->id,
            ]);
        });
    }

    public function close(TradePost $post, User $actor): void
    {
        abort_unless($actor->hasApprovedAccount(), 403);
        DB::transaction(function () use ($post, $actor): void {
            $record = TradePost::query()->ownedBy($actor)->lockForUpdate()->findOrFail($post->id);
            $record->update(['status' => 'closed', 'closed_at' => now()]);
        });
    }
}
