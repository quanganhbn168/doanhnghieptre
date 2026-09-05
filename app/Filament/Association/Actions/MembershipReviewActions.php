<?php

namespace App\Filament\Association\Actions;

use App\Models\Business;
use App\Models\BusinessChapter;
use App\Services\BusinessApprovalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class MembershipReviewActions
{
    public static function make(): array
    {
        return [
            Action::make('download_application')->label('Tải đơn đã ký')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->visible(fn (Business $record): bool => $record->hasMedia('signed_membership_application'))
                ->url(fn (Business $record): string => route('business.membership-application.download', $record), shouldOpenInNewTab: true),
            Action::make('approve_association')->label('Duyệt và chuyển Chi hội')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (Business $record): bool => $record->status === 'pending' && auth('admin')->user()->canReviewAssociation())
                ->disabled(fn (Business $record): bool => ! $record->hasMedia('signed_membership_application'))
                ->tooltip(fn (Business $record): ?string => $record->hasMedia('signed_membership_application') ? null : 'Cần có đơn gia nhập Hội đã ký, đóng dấu trước khi duyệt')
                ->modalHeading('Hội duyệt hồ sơ và chuyển Chi hội')
                ->modalDescription('Xác nhận thông tin và đơn gia nhập đã được kiểm tra. Doanh nghiệp trở thành hội viên sau khi Chi hội tiếp nhận.')
                ->modalSubmitActionLabel('Duyệt và chuyển Chi hội')
                ->schema([
                    Select::make('business_chapter_id')->label('Chi hội tiếp nhận')->required()->searchable()
                        ->options(fn () => BusinessChapter::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                        ->default(fn (Business $record) => $record->business_chapter_id),
                ])
                ->action(fn (Business $record, array $data) => app(BusinessApprovalService::class)->approveForChapter($record, auth('admin')->user(), (int) $data['business_chapter_id']))
                ->successNotificationTitle('Đã chuyển hồ sơ đến Chi hội'),
            Action::make('receive_chapter')->label('Chi hội tiếp nhận')->icon('heroicon-o-user-group')->color('success')
                ->visible(fn (Business $record): bool => $record->status === 'chapter_pending' && auth('admin')->user()->canReceiveChapter() && auth('admin')->user()->managedChapters()->where('is_active', true)->whereKey($record->business_chapter_id)->exists())
                ->disabled(fn (Business $record): bool => ! $record->hasMedia('signed_membership_application'))
                ->requiresConfirmation()->modalDescription('Xác nhận tiếp nhận doanh nghiệp làm hội viên chính thức. Hồ sơ sẽ được công bố trên danh bạ.')
                ->modalHeading('Tiếp nhận doanh nghiệp hội viên')->modalSubmitActionLabel('Xác nhận tiếp nhận')
                ->action(fn (Business $record) => app(BusinessApprovalService::class)->receive($record, auth('admin')->user()))
                ->successNotificationTitle('Đã tiếp nhận doanh nghiệp hội viên'),
            Action::make('request_changes')->label('Yêu cầu bổ sung')->icon('heroicon-o-arrow-path')->color('gray')->outlined()
                ->visible(fn (Business $record): bool => in_array($record->status, ['pending', 'chapter_pending'], true) && (auth('admin')->user()->canReviewAssociation() || ($record->status === 'chapter_pending' && auth('admin')->user()->canReceiveChapter() && auth('admin')->user()->managedChapters()->where('is_active', true)->whereKey($record->business_chapter_id)->exists())))
                ->modalHeading('Yêu cầu doanh nghiệp bổ sung hồ sơ')->modalSubmitActionLabel('Gửi yêu cầu bổ sung')
                ->modalDescription('Ghi rõ nội dung cần sửa để người đại diện theo dõi và gửi lại hồ sơ.')
                ->schema([Textarea::make('reason')->label('Thông tin cần bổ sung')->placeholder('Nêu rõ thông tin hoặc tài liệu doanh nghiệp cần bổ sung…')->required()->maxLength(2000)->rows(4)])
                ->action(fn (Business $record, array $data) => app(BusinessApprovalService::class)->requestChanges($record, auth('admin')->user(), $data['reason']))
                ->successNotificationTitle('Đã trả hồ sơ về người đại diện'),
        ];
    }
}
