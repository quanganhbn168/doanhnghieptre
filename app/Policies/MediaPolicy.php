<?php

namespace App\Policies;

use App\Models\User;
use Filament\Facades\Filament;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        if (Filament::getCurrentPanel()?->getId() === 'association') {
            return $user->canManageAssociation();
        }

        return $user->canAccessPanel(Filament::getPanel('admin'));
    }

    public function view(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, $record): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }
}
