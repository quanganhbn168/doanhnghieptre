<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Roles and permissions on this model belong to the admin panel guard.
     * The frontend keeps using Laravel's separate `web` session guard.
     */
    protected string $guard_name = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'approval_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'member') {
            return $this->hasApprovedAccount() && $this->hasApprovedBusiness();
        }

        if ($panel->getId() === 'association') {
            return $this->canReviewAssociation() || $this->canReceiveChapter() || $this->canRatifyMembership() || $this->canModerateContent();
        }

        if ($this->hasRole(['association_manager', 'chapter_manager', 'membership_head', 'communications_manager'], 'admin') && ! $this->hasRole(['super_admin', 'admin'], 'admin')) {
            return false;
        }

        return $this->hasApprovedAccount()
            && ($this->hasRole(['super_admin', 'admin'], 'admin') || $this->getAllPermissions()->whereNotIn('name', ['association.review', 'association.manage', 'chapters.receive', 'memberships.ratify', 'content.moderate'])->isNotEmpty());
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'account_approved_at' => 'datetime',
        ];
    }

    public function hasApprovedAccount(): bool
    {
        return $this->is_active && $this->approval_status === 'approved';
    }

    public function accountApprovedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'account_approved_by');
    }

    public function canBeManagedBy(User $actor): bool
    {
        if ($actor->hasRole(['super_admin', 'admin'], 'admin')) {
            return true;
        }

        if ($this->hasRole(['super_admin', 'admin'], 'admin')) {
            return false;
        }

        return $this->getAllPermissions()->pluck('name')
            ->diff($actor->getAllPermissions()->pluck('name'))
            ->isEmpty();
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function managedChapters(): BelongsToMany
    {
        return $this->belongsToMany(BusinessChapter::class)->withTimestamps();
    }

    public function hasApprovedBusiness(): bool
    {
        return Business::query()->representedBy($this)->where('status', 'approved')->exists();
    }

    public function canReviewAssociation(): bool
    {
        return $this->hasApprovedAccount() && ($this->hasRole(['super_admin', 'admin'], 'admin') || $this->hasPermissionToSafely('association.review'));
    }

    public function canManageAssociation(): bool
    {
        return $this->hasApprovedAccount() && ($this->hasRole(['super_admin', 'admin'], 'admin') || $this->hasPermissionToSafely('association.manage'));
    }

    public function canReceiveChapter(): bool
    {
        return $this->hasApprovedAccount() && $this->hasPermissionToSafely('chapters.receive') && $this->managedChapters()->where('is_active', true)->exists();
    }

    public function canRatifyMembership(): bool
    {
        return $this->hasApprovedAccount() && ($this->hasRole(['super_admin', 'admin'], 'admin') || $this->hasPermissionToSafely('memberships.ratify'));
    }

    public function canModerateContent(): bool
    {
        return $this->hasApprovedAccount() && ($this->hasRole(['super_admin', 'admin'], 'admin') || $this->hasPermissionToSafely('content.moderate'));
    }

    private function hasPermissionToSafely(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }
}
