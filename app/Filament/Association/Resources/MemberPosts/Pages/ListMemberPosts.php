<?php

namespace App\Filament\Association\Resources\MemberPosts\Pages;

use App\Filament\Association\Resources\MemberPosts\MemberPostResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberPosts extends ListRecords
{
    protected static string $resource = MemberPostResource::class;
}
