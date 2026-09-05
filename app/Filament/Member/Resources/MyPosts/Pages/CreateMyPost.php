<?php

namespace App\Filament\Member\Resources\MyPosts\Pages;

use App\Filament\Member\Resources\MyPosts\MyPostResource;
use App\Services\MemberPostService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMyPost extends CreateRecord
{
    protected static string $resource = MyPostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(MemberPostService::class)->submit(auth('web')->user(), $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
