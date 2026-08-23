<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_members')
            ->withPivot(['role', 'job_title', 'is_primary', 'status', 'started_at', 'ended_at'])
            ->withTimestamps();
    }
}
