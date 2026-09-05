<?php

namespace App\Filament\Association\Resources\Memberships\Pages;

use App\Filament\Association\Actions\MembershipReviewActions;
use App\Filament\Association\Resources\Memberships\MembershipResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewMembership extends ViewRecord
{
    protected static string $resource = MembershipResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function getSubheading(): ?string
    {
        return 'Hồ sơ hội viên doanh nghiệp';
    }

    public function getBreadcrumb(): string
    {
        return 'Chi tiết hồ sơ';
    }

    public function infolist(Schema $schema): Schema
    {
        $this->getRecord()->loadMissing(['category', 'statusHistories.changedBy']);

        return parent::infolist($schema);
    }

    protected function afterActionCalled(Action $action): void
    {
        if (in_array($action->getName(), ['approve_association', 'receive_chapter', 'request_changes', 'ratify_membership', 'reject_membership', 'approve_profile', 'request_profile_changes'], true)) {
            $this->getRecord()->refresh();
        }
    }

    protected function getHeaderActions(): array
    {
        return MembershipReviewActions::make();
    }
}
