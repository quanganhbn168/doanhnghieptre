<?php

namespace App\Filament\Member\Resources\MyPosts\Pages;

use App\Filament\Member\Resources\MyPosts\MyPostResource;
use App\Services\MemberPostService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMyPost extends EditRecord
{
    protected static string $resource = MyPostResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['title'] = $this->record->getTranslation('name', 'vi');
        $data['summary_text'] = $this->record->getTranslation('summary', 'vi');
        $data['body'] = $this->record->getTranslation('content', 'vi');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(MemberPostService::class)->submit(auth('web')->user(), $data, $record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
