<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Industry extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_member_group' => 'boolean', 'show_on_home' => 'boolean', 'sort_order' => 'integer'];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_industries')->withPivot('is_primary')->withTimestamps();
    }

    public function hasAssignments(): bool
    {
        return $this->businesses()->exists()
            || DB::table('offering_industries')->where('industry_id', $this->id)->exists()
            || DB::table('trade_post_industries')->where('industry_id', $this->id)->exists();
    }

    public function scopeMemberGroups(Builder $query): Builder
    {
        return $query->where('is_member_group', true);
    }
}
