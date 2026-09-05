<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradePost extends Model
{
    use SoftDeletes;

    public const TYPE_OPTIONS = [
        'buy' => 'Cần tìm dịch vụ / sản phẩm',
        'sell' => 'Cung cấp dịch vụ / sản phẩm',
        'cooperate' => 'Mời hợp tác',
    ];

    public const STATUS_OPTIONS = [
        'draft' => 'Bản nháp', 'pending' => 'Chờ Hội duyệt', 'approved' => 'Đã duyệt',
        'rejected' => 'Cần bổ sung', 'closed' => 'Đã đóng',
    ];

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->whereHas('business', fn (Builder $businesses) => $businesses->representedBy($user));
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->whereHas('business', fn (Builder $businesses) => $businesses->where('status', 'approved'))
            ->where(fn (Builder $posts) => $posts->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function industries(): BelongsToMany
    {
        return $this->belongsToMany(Industry::class, 'trade_post_industries')->withTimestamps();
    }

    public static function typeLabel(?string $type): string
    {
        return self::TYPE_OPTIONS[$type] ?? 'Khác';
    }

    public static function typeIcon(?string $type): string
    {
        return match ($type) {
            'sell' => 'fa-cart-shopping',
            'cooperate' => 'fa-handshake',
            default => 'fa-magnifying-glass',
        };
    }
}
