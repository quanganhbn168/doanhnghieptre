<?php

namespace App\Filament\Association\Resources\Memberships\Pages;

use App\Filament\Association\Resources\Memberships\MembershipResource;
use App\Models\Business;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMemberships extends ListRecords
{
    protected static string $resource = MembershipResource::class;

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make('Tất cả')];
        foreach (Business::STATUS_LABELS as $status => $label) {
            if ($status === 'draft') {
                continue;
            }
            $tabs[$status] = Tab::make($label)->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
