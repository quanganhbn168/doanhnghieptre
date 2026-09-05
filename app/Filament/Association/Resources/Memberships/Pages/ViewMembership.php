<?php

namespace App\Filament\Association\Resources\Memberships\Pages;

use App\Filament\Association\Actions\MembershipReviewActions;
use App\Filament\Association\Resources\Memberships\MembershipResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMembership extends ViewRecord
{
    protected static string $resource = MembershipResource::class;

    protected function getHeaderActions(): array
    {
        return MembershipReviewActions::make();
    }
}
