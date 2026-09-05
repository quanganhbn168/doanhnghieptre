<?php

namespace App\Filament\Association\Resources\MemberPosts\Pages;

use App\Filament\Association\Resources\MemberPosts\MemberPostResource;
use App\Models\Post;
use App\Services\MemberPostService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewMemberPost extends ViewRecord
{
    protected static string $resource = MemberPostResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->getTranslation('name', 'vi');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')->label('Duyệt và xuất bản')->color('success')->requiresConfirmation()
                ->visible(fn (Post $record) => $record->review_status === 'pending' && auth('admin')->user()->canModerateContent())
                ->action(fn (Post $record) => app(MemberPostService::class)->moderate($record, auth('admin')->user(), 'approve')),
            Action::make('reject')->label('Yêu cầu chỉnh sửa')->color('gray')->schema([Textarea::make('review_note')->label('Nội dung cần chỉnh sửa')->required()->maxLength(2000)])
                ->visible(fn (Post $record) => $record->review_status === 'pending' && auth('admin')->user()->canModerateContent())
                ->action(fn (Post $record, array $data) => app(MemberPostService::class)->moderate($record, auth('admin')->user(), 'reject', $data['review_note'])),
        ];
    }

    protected function afterActionCalled(Action $action): void
    {
        $this->getRecord()->refresh();
    }
}
