<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Business extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'location' => 'array',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
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

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BusinessStatusHistory::class)->latest('changed_at');
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
    }
}
