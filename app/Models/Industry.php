<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $guarded = [];

    public function scopeMemberGroups(Builder $query): Builder
    {
        return $query->where('is_member_group', true);
    }
}
