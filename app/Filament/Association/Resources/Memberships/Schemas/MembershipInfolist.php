<?php

namespace App\Filament\Association\Resources\Memberships\Schemas;

use App\Models\Business;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MembershipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hồ sơ hội viên doanh nghiệp')->schema([
                TextEntry::make('name')->label('Tên giao dịch'),
                TextEntry::make('legal_name')->label('Tên pháp lý')->placeholder('—'),
                TextEntry::make('tax_code')->label('Mã số thuế'),
                TextEntry::make('membership_code')->label('Mã hội viên')->placeholder('Cấp sau khi Chi hội tiếp nhận'),
                TextEntry::make('status')->label('Trạng thái')->badge()->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state),
                TextEntry::make('chapter.name')->label('Chi hội')->placeholder('Chờ Hội phân công'),
                TextEntry::make('association_approved_at')->label('Hội duyệt lúc')->dateTime('d/m/Y H:i')->placeholder('Chưa duyệt'),
                TextEntry::make('approved_at')->label('Ngày kết nạp')->dateTime('d/m/Y H:i')->placeholder('Chưa tiếp nhận'),
                TextEntry::make('business_type')->label('Loại hình')->formatStateUsing(fn (?string $state) => ['limited' => 'Công ty TNHH', 'joint_stock' => 'Công ty cổ phần', 'private' => 'Doanh nghiệp tư nhân', 'household' => 'Hộ kinh doanh', 'cooperative' => 'Hợp tác xã', 'other' => 'Khác'][$state] ?? $state),
                TextEntry::make('business_size')->label('Quy mô')->formatStateUsing(fn (?string $state) => ['small' => 'Nhỏ', 'medium' => 'Vừa', 'large' => 'Lớn'][$state] ?? $state),
                TextEntry::make('industries.name')->label('Nhóm nghề nghiệp')->listWithLineBreaks()->columnSpanFull(),
                TextEntry::make('summary')->label('Giới thiệu')->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Người đại diện & liên hệ')->schema([
                TextEntry::make('representative_name')->label('Người đại diện')->state(fn (Business $record) => $record->representativeDisplayName()),
                TextEntry::make('representative_job_title')->label('Chức danh')->placeholder('—'),
                TextEntry::make('phone')->label('Điện thoại'),
                TextEntry::make('email')->label('Email doanh nghiệp'),
                TextEntry::make('website')->label('Website')->placeholder('—'),
                TextEntry::make('submittedBy.email')->label('Email theo dõi hồ sơ')->placeholder('—'),
                TextEntry::make('province')->label('Tỉnh / thành phố'),
                TextEntry::make('district')->label('Khu vực')->placeholder('—'),
                TextEntry::make('address')->label('Địa chỉ')->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Đơn gia nhập Hội')->schema([
                TextEntry::make('application')->label('Bản đơn đã ký, đóng dấu')
                    ->state(fn (Business $record) => $record->getFirstMedia('signed_membership_application')?->file_name ?? 'Chưa có đơn')
                    ->url(fn (Business $record) => $record->hasMedia('signed_membership_application') ? route('business.membership-application.download', $record) : null, shouldOpenInNewTab: true),
            ])->columnSpanFull(),
            Section::make('Lịch sử xử lý')->schema([
                RepeatableEntry::make('statusHistories')->label('Các lần xử lý')->hiddenLabel()->schema([
                    TextEntry::make('to_status')->label('Trạng thái')->formatStateUsing(fn (string $state) => Business::STATUS_LABELS[$state] ?? $state)->badge(),
                    TextEntry::make('changedBy.name')->label('Người xử lý')->placeholder('Hệ thống'),
                    TextEntry::make('changed_at')->label('Thời gian')->dateTime('d/m/Y H:i'),
                    TextEntry::make('reason')->label('Nội dung')->columnSpanFull(),
                ])->columns(3),
            ])->columnSpanFull(),
        ]);
    }
}
