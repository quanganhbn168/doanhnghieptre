<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Business extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const STATUS_LABELS = [
        'draft' => 'Bản nháp',
        'pending' => 'Chờ Văn phòng kiểm tra',
        'chapter_pending' => 'Chờ Chi hội thẩm định',
        'board_pending' => 'Chờ Trưởng ban chuẩn y',
        'approved' => 'Hội viên chính thức',
        'changes_requested' => 'Cần bổ sung',
        'rejected' => 'Từ chối kết nạp',
    ];

    protected $fillable = [
        'business_category_id',
        'business_chapter_id',
        'name',
        'legal_name',
        'slug',
        'tax_code',
        'business_type',
        'business_size',
        'phone',
        'email',
        'website',
        'address',
        'province',
        'district',
        'summary',
        'description',
        'social_links',
        'location',
        'status',
        'is_featured',
        'submitted_by_user_id',
        'representative_job_title',
        'representative_name',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'location' => 'array',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
            'association_approved_at' => 'datetime',
            'chapter_reviewed_at' => 'datetime',
            'pending_profile' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(BusinessChapter::class, 'business_chapter_id');
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class, 'business_industries')->withPivot('is_primary')->withTimestamps();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'business_members')
            ->withPivot(['role', 'job_title', 'is_primary', 'status', 'started_at', 'ended_at'])
            ->withTimestamps();
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function representativeDisplayName(): ?string
    {
        return $this->representative_name ?: ($this->submittedBy?->name ?: $this->members->firstWhere('pivot.is_primary', true)?->full_name);
    }

    public function scopeRepresentedBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('submitted_by_user_id', $user->id)
                ->orWhereHas('members', fn (Builder $members) => $members->where('user_id', $user->id)->where('business_members.status', 'active'));
        });
    }

    public function scopeVisibleToReviewer(Builder $query, User $user): Builder
    {
        if ($user->canReviewAssociation() || $user->canRatifyMembership()) {
            return $query;
        }

        return $query->whereIn('business_chapter_id', $user->managedChapters()->where('is_active', true)->select('business_chapters.id'))
            ->whereIn('status', ['chapter_pending', 'board_pending', 'approved', 'changes_requested', 'rejected'])
            ->when(! $user->canReceiveChapter(), fn (Builder $query) => $query->whereRaw('1 = 0'));
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BusinessStatusHistory::class)->latest('changed_at')->latest('id');
    }

    public function transitionTo(string $status, ?int $changedBy = null, ?string $reason = null): void
    {
        $fromStatus = $this->status;

        $attributes = ['status' => $status];
        if ($status === 'approved') {
            $attributes['approved_at'] = now();
            $attributes['approved_by'] = $changedBy;
        } elseif ($status !== 'approved') {
            $attributes['approved_at'] = null;
            $attributes['approved_by'] = null;
        }

        if (in_array($status, ['draft', 'pending', 'changes_requested', 'rejected'], true)) {
            $attributes['association_approved_at'] = null;
            $attributes['association_approved_by'] = null;
            $attributes['chapter_reviewed_at'] = null;
            $attributes['chapter_reviewed_by'] = null;
        }

        $this->forceFill($attributes)->save();

        $this->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $status,
            'reason' => $reason,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->useDisk('public_media');
        $this->addMediaCollection('pending_logo')->singleFile()->useDisk('public_media');
        $this->addMediaCollection('signed_membership_application')->singleFile()->useDisk('local');
    }
}
