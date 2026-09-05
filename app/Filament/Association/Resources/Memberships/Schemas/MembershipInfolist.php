<?php

namespace App\Filament\Association\Resources\Memberships\Schemas;

use App\Models\Business;
use App\Models\Industry;
use App\Support\BusinessPresentation;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MembershipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(['default' => 1, 'xl' => 3])->components([
            Section::make('Tình trạng hồ sơ')
                ->description(fn (Business $record): string => self::statusDescription($record))
                ->schema([
                    TextEntry::make('status')->label('Trạng thái hiện tại')->badge()
                        ->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)
                        ->color(fn (string $state) => BusinessPresentation::statusColor($state)),
                    TextEntry::make('membership_code')->label('Mã hội viên')->copyable()->copyMessage('Đã sao chép mã hội viên')->placeholder('Chưa cấp mã'),
                    TextEntry::make('chapter.name')->label('Chi hội tiếp nhận')->placeholder('Chờ Hội phân công'),
                    TextEntry::make('created_at')->label('Ngày tạo hồ sơ')->dateTime('d/m/Y H:i'),
                    TextEntry::make('association_approved_at')->label('Hội duyệt')->dateTime('d/m/Y H:i')->placeholder('Chưa duyệt'),
                    TextEntry::make('approved_at')->label('Ngày kết nạp')->dateTime('d/m/Y')->placeholder('Chưa tiếp nhận'),
                ])->columns(['default' => 2, 'md' => 3])->columnSpanFull(),
            Tabs::make('Chi tiết hồ sơ')->id('membership-details')->persistTabInQueryString('tab')->tabs([
                Tab::make('Doanh nghiệp')->icon('heroicon-o-building-office-2')->schema([
                    ImageEntry::make('business_logo')->label('Logo doanh nghiệp')
                        ->state(fn (Business $record) => $record->getFirstMediaUrl('logo'))
                        ->visible(fn (Business $record) => $record->hasMedia('logo'))->imageHeight(80)->columnSpanFull(),
                    TextEntry::make('legal_name')->label('Tên pháp lý')->placeholder('Chưa cập nhật')->columnSpanFull(),
                    TextEntry::make('tax_code')->label('Mã số thuế')->copyable()->copyMessage('Đã sao chép mã số thuế')->placeholder('Chưa cập nhật'),
                    TextEntry::make('business_type')->label('Loại hình doanh nghiệp')->placeholder('Chưa cập nhật')
                        ->formatStateUsing(fn (string $state) => BusinessPresentation::TYPES[$state] ?? $state),
                    TextEntry::make('category.name')->label('Nhóm doanh nghiệp')->placeholder('Chưa cập nhật'),
                    TextEntry::make('business_size')->label('Quy mô')->placeholder('Chưa cập nhật')
                        ->formatStateUsing(fn (string $state) => BusinessPresentation::SIZES[$state] ?? $state),
                    TextEntry::make('current_industries')->label('Khối ngành nghề')->listWithLineBreaks()->columnSpanFull()
                        ->state(fn (Business $record) => $record->industries->where('is_member_group', true)->where('is_active', true)->sortByDesc('pivot.is_primary')->pluck('name')->all())
                        ->placeholder('Chưa chọn theo danh mục khối ngành nghề hiện tại'),
                    TextEntry::make('legacy_industries')->label('Phân loại trước đây')->color('gray')->listWithLineBreaks()->columnSpanFull()
                        ->state(fn (Business $record) => $record->industries->filter(fn (Industry $industry) => ! $industry->is_member_group || ! $industry->is_active)->pluck('name')->all())
                        ->visible(fn (Business $record) => $record->industries->contains(fn (Industry $industry) => ! $industry->is_member_group || ! $industry->is_active)),
                    TextEntry::make('summary')->label('Giới thiệu doanh nghiệp')->placeholder('Chưa có nội dung giới thiệu')->columnSpanFull(),
                ])->columns(['default' => 1, 'md' => 2]),
                Tab::make('Liên hệ')->icon('heroicon-o-identification')->schema([
                    Section::make('Người đại diện')->compact()->schema([
                        TextEntry::make('representative_name')->label('Họ và tên')->state(fn (Business $record) => $record->representativeDisplayName())->placeholder('Chưa cập nhật'),
                        TextEntry::make('representative_job_title')->label('Chức danh')->placeholder('Chưa cập nhật'),
                        TextEntry::make('submittedBy.email')->label('Email theo dõi hồ sơ')->copyable()->copyMessage('Đã sao chép email')->placeholder('Chưa có tài khoản theo dõi')->columnSpanFull(),
                    ])->columns(['default' => 1, 'md' => 2]),
                    Section::make('Liên hệ doanh nghiệp')->compact()->schema([
                        TextEntry::make('phone')->label('Điện thoại')->icon('heroicon-o-phone')->placeholder('Chưa cập nhật')
                            ->url(fn (Business $record) => BusinessPresentation::phoneUrl($record->phone)),
                        TextEntry::make('email')->label('Email doanh nghiệp')->icon('heroicon-o-envelope')->placeholder('Chưa cập nhật')
                            ->url(fn (Business $record) => BusinessPresentation::emailUrl($record->email)),
                        TextEntry::make('website')->label('Website')->icon('heroicon-o-globe-alt')->placeholder('Chưa cập nhật')
                            ->url(fn (Business $record) => BusinessPresentation::websiteUrl($record->website), shouldOpenInNewTab: true)->columnSpanFull(),
                        TextEntry::make('location')->label('Khu vực')->state(fn (Business $record) => BusinessPresentation::location($record->district, $record->province))->placeholder('Chưa cập nhật'),
                        TextEntry::make('address')->label('Địa chỉ')->placeholder('Chưa cập nhật')->columnSpanFull(),
                    ])->columns(['default' => 1, 'md' => 2]),
                ]),
                Tab::make('Lịch sử xử lý')->icon('heroicon-o-clock')
                    ->badge(fn (Business $record) => $record->statusHistories->count())->schema([
                        TextEntry::make('empty_history')->label('Lịch sử xử lý')->hiddenLabel()->state('Chưa có lịch sử xử lý.')
                            ->visible(fn (Business $record) => $record->statusHistories->isEmpty()),
                        RepeatableEntry::make('statusHistories')->label('Các lần xử lý hồ sơ')->hiddenLabel()->schema([
                            TextEntry::make('to_status')->label('Trạng thái')->badge()
                                ->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)
                                ->color(fn (string $state) => BusinessPresentation::statusColor($state)),
                            TextEntry::make('changed_at')->label('Thời gian')->dateTime('d/m/Y H:i'),
                            TextEntry::make('changedBy.name')->label('Người xử lý')->placeholder('Hệ thống'),
                            TextEntry::make('reason')->label('Nội dung xử lý')->placeholder('Không có ghi chú')->columnSpanFull(),
                        ])->columns(['default' => 1, 'md' => 3])->visible(fn (Business $record) => $record->statusHistories->isNotEmpty()),
                    ]),
            ])->columnSpan(['default' => 1, 'xl' => 2]),
            Group::make([
                Section::make('Đơn gia nhập Hội')->icon('heroicon-o-document-text')->schema([
                    TextEntry::make('application_status')->label('Tình trạng đơn')->hiddenLabel()->badge()
                        ->state(fn (Business $record) => $record->hasMedia('signed_membership_application') ? 'Đã có đơn gia nhập' : 'Chưa có đơn gia nhập')
                        ->color(fn (Business $record) => $record->hasMedia('signed_membership_application') ? 'success' : 'warning'),
                    TextEntry::make('application')->label('Bản đơn đã ký, đóng dấu')
                        ->state(fn (Business $record) => $record->getFirstMedia('signed_membership_application')?->file_name)
                        ->url(fn (Business $record) => $record->hasMedia('signed_membership_application') ? route('business.membership-application.download', $record) : null, shouldOpenInNewTab: true)
                        ->placeholder('Doanh nghiệp cần gửi đơn đã ký, đóng dấu trước khi được xét duyệt.'),
                    TextEntry::make('application_size')->label('Dung lượng')
                        ->state(fn (Business $record) => $record->getFirstMedia('signed_membership_application')?->human_readable_size)
                        ->visible(fn (Business $record) => $record->hasMedia('signed_membership_application')),
                    TextEntry::make('application_received_at')->label('Tải lên lúc')->dateTime('d/m/Y H:i')
                        ->state(fn (Business $record) => $record->getFirstMedia('signed_membership_application')?->created_at)
                        ->visible(fn (Business $record) => $record->hasMedia('signed_membership_application')),
                ]),
                Section::make('Nội dung cần bổ sung')->icon('heroicon-o-chat-bubble-left-ellipsis')->schema([
                    TextEntry::make('latest_feedback')->label('Yêu cầu bổ sung')->hiddenLabel()
                        ->state(fn (Business $record) => $record->statusHistories->firstWhere('to_status', 'rejected')?->reason)
                        ->placeholder('Chưa có ghi chú bổ sung.'),
                ])->visible(fn (Business $record) => $record->status === 'rejected'),
            ])->columnSpan(1),
        ]);
    }

    private static function statusDescription(Business $record): string
    {
        return match ($record->status) {
            'pending' => $record->hasMedia('signed_membership_application')
                ? 'Kiểm tra thông tin và đơn gia nhập trước khi duyệt, chuyển hồ sơ đến Chi hội tiếp nhận.'
                : 'Hồ sơ đang thiếu đơn đã ký, đóng dấu. Yêu cầu doanh nghiệp bổ sung trước khi duyệt.',
            'chapter_pending' => 'Hội đã duyệt hồ sơ. Chi hội được phân công kiểm tra và xác nhận tiếp nhận doanh nghiệp.',
            'approved' => 'Doanh nghiệp đã được kết nạp và có mặt trong danh bạ hội viên.',
            'rejected' => 'Đang chờ doanh nghiệp bổ sung. Hồ sơ sẽ quay lại bước Hội duyệt sau khi được gửi lại.',
            default => 'Doanh nghiệp chưa gửi hồ sơ. Chưa thực hiện xét duyệt ở bước này.',
        };
    }
}
