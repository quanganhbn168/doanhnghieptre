<?php

namespace App\Filament\Association\Actions;

use App\Models\Business;
use App\Models\BusinessChapter;
use App\Services\BusinessApprovalService;
use App\Services\BusinessProfileReviewService;
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
            Action::make('approve_association')->label('Xác nhận hợp lệ')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn (Business $record): bool => $record->status === 'pending' && auth('admin')->user()->canReviewAssociation())
                ->disabled(fn (Business $record): bool => ! $record->hasMedia('signed_membership_application'))
                ->tooltip(fn (Business $record): ?string => $record->hasMedia('signed_membership_application') ? null : 'Cần có đơn gia nhập Hội đã ký, đóng dấu trước khi duyệt')
                ->modalHeading('Văn phòng kiểm tra hồ sơ')
                ->modalDescription('Chuyển hồ sơ hợp lệ đến Chi hội thẩm định. Hồ sơ còn cần Trưởng ban Hội viên chuẩn y.')
                ->modalSubmitActionLabel('Xác nhận hợp lệ')
                ->schema([
                    Select::make('business_chapter_id')->label('Chi hội thẩm định')->required()->searchable()
                        ->options(fn () => BusinessChapter::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                        ->default(fn (Business $record) => $record->business_chapter_id),
                ])
                ->action(fn (Business $record, array $data) => app(BusinessApprovalService::class)->approveForChapter($record, auth('admin')->user(), (int) $data['business_chapter_id']))
                ->successNotificationTitle('Đã chuyển hồ sơ đến Chi hội'),
            Action::make('receive_chapter')->label('Đề xuất chuẩn y')->icon('heroicon-o-user-group')->color('success')
                ->visible(fn (Business $record): bool => $record->status === 'chapter_pending' && auth('admin')->user()->canReceiveChapter() && auth('admin')->user()->managedChapters()->where('is_active', true)->whereKey($record->business_chapter_id)->exists())
                ->disabled(fn (Business $record): bool => ! $record->hasMedia('signed_membership_application'))
                ->requiresConfirmation()->modalDescription('Xác nhận Chi hội đã thẩm định và đề xuất kết nạp. Hồ sơ sẽ chuyển đến Trưởng ban Hội viên để chuẩn y.')
                ->modalHeading('Chi hội đề xuất kết nạp')->modalSubmitActionLabel('Gửi đề xuất chuẩn y')
                ->action(fn (Business $record) => app(BusinessApprovalService::class)->receive($record, auth('admin')->user()))
                ->successNotificationTitle('Đã chuyển Trưởng ban Hội viên'),
            Action::make('ratify_membership')->label('Chuẩn y kết nạp')->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn (Business $record): bool => $record->status === 'board_pending' && auth('admin')->user()->canRatifyMembership())
                ->disabled(fn (Business $record): bool => ! $record->hasMedia('signed_membership_application'))
                ->requiresConfirmation()->modalHeading('Trưởng ban Hội viên chuẩn y kết nạp')
                ->modalDescription('Doanh nghiệp sẽ được cấp mã hội viên, xuất bản lên danh bạ và cấp tài khoản cho người đại diện.')
                ->modalSubmitActionLabel('Chuẩn y kết nạp')
                ->action(fn (Business $record) => app(BusinessApprovalService::class)->ratify($record, auth('admin')->user()))
                ->successNotificationTitle('Đã chuẩn y doanh nghiệp hội viên'),
            Action::make('request_changes')->label('Yêu cầu bổ sung')->icon('heroicon-o-arrow-path')->color('gray')->outlined()
                ->visible(fn (Business $record): bool => app(BusinessApprovalService::class)->canReviewCurrentStage($record, auth('admin')->user()))
                ->modalHeading('Yêu cầu doanh nghiệp bổ sung hồ sơ')->modalSubmitActionLabel('Gửi yêu cầu bổ sung')
                ->modalDescription('Ghi rõ nội dung cần sửa để người đại diện theo dõi và gửi lại hồ sơ.')
                ->schema([Textarea::make('reason')->label('Thông tin cần bổ sung')->placeholder('Nêu rõ thông tin hoặc tài liệu doanh nghiệp cần bổ sung…')->required()->maxLength(2000)->rows(4)])
                ->action(fn (Business $record, array $data) => app(BusinessApprovalService::class)->requestChanges($record, auth('admin')->user(), $data['reason']))
                ->successNotificationTitle('Đã trả hồ sơ về người đại diện'),
            Action::make('reject_membership')->label('Từ chối kết nạp')->icon('heroicon-o-x-circle')->color('danger')->outlined()
                ->visible(fn (Business $record): bool => app(BusinessApprovalService::class)->canReviewCurrentStage($record, auth('admin')->user()))
                ->modalHeading('Từ chối hồ sơ gia nhập Hội')->modalSubmitActionLabel('Xác nhận từ chối')
                ->modalDescription('Hồ sơ sẽ kết thúc và không cấp tài khoản hội viên. Nếu chỉ cần sửa thông tin, dùng Yêu cầu bổ sung.')
                ->schema([Textarea::make('reason')->label('Lý do từ chối')->required()->maxLength(2000)->rows(4)])
                ->action(fn (Business $record, array $data) => app(BusinessApprovalService::class)->reject($record, auth('admin')->user(), $data['reason']))
                ->successNotificationTitle('Đã từ chối hồ sơ'),
            Action::make('approve_profile')->label('Duyệt bản cập nhật')->color('success')->requiresConfirmation()
                ->visible(fn (Business $record) => $record->status === 'approved' && filled($record->pending_profile) && blank($record->profile_review_note) && auth('admin')->user()->canReviewAssociation())
                ->action(fn (Business $record) => app(BusinessProfileReviewService::class)->review($record, auth('admin')->user(), true)),
            Action::make('request_profile_changes')->label('Yêu cầu sửa bản cập nhật')->color('gray')->schema([Textarea::make('reason')->label('Nội dung cần sửa')->required()->maxLength(2000)])
                ->visible(fn (Business $record) => $record->status === 'approved' && filled($record->pending_profile) && blank($record->profile_review_note) && auth('admin')->user()->canReviewAssociation())
                ->action(fn (Business $record, array $data) => app(BusinessProfileReviewService::class)->review($record, auth('admin')->user(), false, $data['reason'])),
        ];
    }
}
