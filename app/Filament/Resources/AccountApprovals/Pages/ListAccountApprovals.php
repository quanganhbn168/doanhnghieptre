<?php

namespace App\Filament\Resources\AccountApprovals\Pages;

use App\Filament\Resources\AccountApprovals\AccountApprovalResource;
use Filament\Resources\Pages\ListRecords;

class ListAccountApprovals extends ListRecords
{
    protected static string $resource = AccountApprovalResource::class;
}
