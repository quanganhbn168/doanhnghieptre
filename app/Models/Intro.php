<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Intro extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function nextSortOrder(): int
    {
        return ((int) static::query()->max('sort_order')) + 10;
    }
}
