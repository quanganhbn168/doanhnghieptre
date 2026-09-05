<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberPostService
{
    public function submit(User $actor, array $data, ?Post $post = null): Post
    {
        abort_unless($actor->hasApprovedAccount(), 403);
        Validator::make($data, [
            'title' => ['required', 'string', 'max:255'], 'summary_text' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string', 'max:50000'],
            'post_category_id' => ['required', Rule::exists('post_categories', 'id')->where('is_active', true)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ])->validate();

        return DB::transaction(function () use ($actor, $data, $post): Post {
            $business = Business::query()->representedBy($actor)->where('status', 'approved')->lockForUpdate()->find($data['business_id'] ?? null);
            if (! $business) {
                throw ValidationException::withMessages(['business_id' => 'Chọn doanh nghiệp hội viên do anh/chị đại diện.']);
            }
            if ($post) {
                $post = Post::query()->ownedBy($actor)->lockForUpdate()->findOrFail($post->id);
            }
            $payload = [
                'post_category_id' => $data['post_category_id'], 'name' => ['vi' => $data['title']],
                'summary' => ['vi' => $data['summary_text']], 'content' => ['vi' => (string) str($data['body'])->sanitizeHtml()],
                'is_active' => false, 'published_at' => null,
            ];
            if (array_key_exists('image', $data)) {
                $payload['image'] = $data['image'];
            }
            if ($post) {
                app(PostService::class)->update($post, $payload);
            } else {
                $post = app(PostService::class)->create($payload);
            }
            $post->forceFill([
                'business_id' => $business->id, 'created_by' => $actor->id,
                'review_status' => 'pending', 'review_note' => null, 'reviewed_by' => null,
                'is_active' => false, 'published_at' => null,
            ])->save();

            return $post;
        });
    }

    public function moderate(Post $post, User $actor, string $decision, ?string $note = null): void
    {
        abort_unless($actor->canModerateContent(), 403);
        DB::transaction(function () use ($post, $actor, $decision, $note): void {
            $business = Business::query()->lockForUpdate()->find($post->business_id);
            $record = Post::query()->lockForUpdate()->findOrFail($post->id);
            if (! $business || $record->business_id !== $business->id || $record->review_status !== 'pending' || ! in_array($decision, ['approve', 'reject'], true)) {
                throw ValidationException::withMessages(['status' => 'Bài viết đã thay đổi. Vui lòng tải lại để kiểm tra.']);
            }
            if ($decision === 'approve' && $business->status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Doanh nghiệp chưa là hội viên chính thức.']);
            }
            if ($decision === 'reject' && (blank($note) || mb_strlen($note) > 2000)) {
                throw ValidationException::withMessages(['review_note' => 'Nhập nội dung cần chỉnh sửa, tối đa 2.000 ký tự.']);
            }
            $record->forceFill([
                'review_status' => $decision === 'approve' ? 'approved' : 'rejected',
                'is_active' => $decision === 'approve', 'published_at' => $decision === 'approve' ? now() : null,
                'reviewed_by' => $actor->id, 'review_note' => $decision === 'reject' ? trim($note) : null,
            ])->save();
        });
    }
}
