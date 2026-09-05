<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public const STATUS_OPTIONS = ['draft' => 'Bản nháp', 'published' => 'Đã xuất bản', 'cancelled' => 'Đã hủy'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'registration_opens_at' => 'datetime', 'registration_closes_at' => 'datetime', 'published_at' => 'datetime', 'capacity' => 'integer', 'settings' => 'array', 'location' => 'array'];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('visibility', 'public')
            ->where(fn (Builder $events) => $events->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function registrationIsOpen(): bool
    {
        return $this->status === 'published' && $this->visibility === 'public' && ! $this->trashed()
            && (! $this->published_at || $this->published_at->lte(now()))
            && $this->starts_at->isFuture()
            && (! $this->registration_opens_at || $this->registration_opens_at->lte(now()))
            && (! $this->registration_closes_at || $this->registration_closes_at->gt(now()));
    }

    public function scopeOngoingOrUpcoming(Builder $query): Builder
    {
        return $query->whereRaw('COALESCE(ends_at, starts_at) >= ?', [now()]);
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereRaw('COALESCE(ends_at, starts_at) < ?', [now()]);
    }
}
