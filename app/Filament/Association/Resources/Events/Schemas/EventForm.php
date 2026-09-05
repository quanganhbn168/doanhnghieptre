<?php

namespace App\Filament\Association\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin sự kiện')->columnSpanFull()->columns(2)->schema([
                TextInput::make('title')->label('Tên sự kiện')->required()->maxLength(255)->columnSpanFull()->live(onBlur: true)->afterStateUpdated(function ($state, $get, $set) {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),
                TextInput::make('slug')->label('Đường dẫn')->required()->maxLength(255)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->validationMessages(['regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.'])->unique(ignoreRecord: true)->columnSpanFull(),
                Textarea::make('summary')->label('Giới thiệu ngắn')->rows(3)->maxLength(2000)->columnSpanFull(),
                RichEditor::make('content')->label('Nội dung chương trình')->fileAttachments(false)->columnSpanFull(),
                TextInput::make('venue_name')->label('Địa điểm')->maxLength(255),
                TextInput::make('venue_address')->label('Địa chỉ')->maxLength(500),
                DateTimePicker::make('starts_at')->label('Bắt đầu')->seconds(false)->required(),
                DateTimePicker::make('ends_at')->label('Kết thúc')->seconds(false)->after('starts_at'),
            ]),
            Section::make('Đăng ký tham dự')->columnSpanFull()->columns(2)->schema([
                DateTimePicker::make('registration_opens_at')->label('Mở đăng ký')->seconds(false)->before('starts_at'),
                DateTimePicker::make('registration_closes_at')->label('Đóng đăng ký')->seconds(false)->after('registration_opens_at')->beforeOrEqual('starts_at'),
                TextInput::make('capacity')->label('Số người tối đa')->integer()->minValue(1)->maxValue(1000000)->helperText('Bao gồm người đăng ký và người đi cùng. Để trống nếu không giới hạn.'),
            ]),
            Section::make('Xuất bản')->columnSpanFull()->columns(3)->schema([
                Select::make('status')->label('Trạng thái')->options(Event::STATUS_OPTIONS)->default('draft')->required(),
                Select::make('visibility')->label('Phạm vi')->options(['public' => 'Công khai', 'private' => 'Nội bộ ban tổ chức'])->default('public')->required(),
                DateTimePicker::make('published_at')->label('Ngày xuất bản')->seconds(false)->helperText('Để trống để hiện ngay khi chọn Đã xuất bản.'),
            ]),
        ]);
    }
}
