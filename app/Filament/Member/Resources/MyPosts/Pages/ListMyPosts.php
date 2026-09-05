<?php

namespace App\Filament\Member\Resources\MyPosts\Pages;

use App\Filament\Member\Resources\MyPosts\MyPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyPosts extends ListRecords
{
    protected static string $resource = MyPostResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Gửi bài viết')];
    }
}
