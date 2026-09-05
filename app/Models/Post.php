<?php

namespace App\Models;

use App\Models\Concerns\HasComments;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Post extends Model implements HasMedia
{
    use HasComments, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    public const REVIEW_STATUSES = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Cần chỉnh sửa'];

    protected $fillable = [
        'image_id',
        'post_category_id',
        'name',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_featured',
        'is_active',
        'view_count',
        'created_by',
        'published_at',
    ];

    public $translatable = [
        'name',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('review_status', 'approved')
            ->where(fn (Builder $posts) => $posts->whereNull('business_id')->orWhereHas('business', fn (Builder $businesses) => $businesses->where('status', 'approved')))
            ->where(fn (Builder $posts) => $posts->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->whereHas('business', fn (Builder $businesses) => $businesses->representedBy($user));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function getSlugSourceKey(): string
    {
        return 'name';
    }

    /**
     * Đăng ký Media Collection cho ảnh đại diện bài viết.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('post_image')
            ->singleFile()
            ->useDisk('public_media');
    }
}
