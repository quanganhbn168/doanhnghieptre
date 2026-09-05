<?php

namespace App\Filament\Association\Actions;

use App\Models\TradePost;
use App\Services\TradePostService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class TradeReviewActions
{
    public static function make(): array
    {
        $actions = [];
        foreach (['approve' => ['Duyệt đăng', 'success', ['pending']], 'reject' => ['Yêu cầu bổ sung', 'danger', ['pending']], 'close' => ['Đóng tin', 'gray', ['pending', 'approved']], 'submit' => ['Gửi duyệt lại', 'gray', ['draft', 'rejected', 'closed']]] as $decision => [$label, $color, $statuses]) {
            $action = Action::make($decision)->label($label)->color($color)
                ->visible(fn (TradePost $record) => auth('admin')->user()?->canModerateContent() && in_array($record->status, $statuses, true))
                ->action(function (TradePost $record, array $data) use ($decision): void {
                    app(TradePostService::class)->moderate($record, auth('admin')->user(), $decision, $data['review_note'] ?? null);
                    $record->refresh();
                    Notification::make()->title('Đã cập nhật trạng thái tin')->success()->send();
                });
            if ($decision === 'reject') {
                $action->schema([Textarea::make('review_note')->label('Nội dung cần bổ sung')->required()->maxLength(2000)->rows(4)]);
            } else {
                $action->requiresConfirmation();
            }
            $actions[] = $action;
        }

        return $actions;
    }
}
