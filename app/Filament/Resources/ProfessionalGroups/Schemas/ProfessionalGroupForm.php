<?php

namespace App\Filament\Resources\ProfessionalGroups\Schemas;

use App\Models\Industry;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProfessionalGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Khối ngành nghề')->description('Danh mục dùng chung trên trang chủ, danh bạ và hồ sơ hội viên.')->columnSpanFull()->columns(2)->schema([
                TextInput::make('name')->label('Tên khối')->required()->maxLength(255)->columnSpanFull()->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    }),
                TextInput::make('slug')->label('Đường dẫn')->required()->maxLength(255)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true),
                TextInput::make('sort_order')->label('Thứ tự')->numeric()->minValue(0)->maxValue(65535)->default(fn (): int => ((int) Industry::query()->memberGroups()->max('sort_order')) + 10)->required(),
                Textarea::make('description')->label('Mô tả')->rows(3)->maxLength(2000)->columnSpanFull(),
                Toggle::make('is_active')->label('Đang sử dụng')->helperText('Khối đang sử dụng có thể được chọn trong hồ sơ hội viên.')->default(true),
                Toggle::make('show_on_home')->label('Hiển thị trang chủ')->default(true),
            ]),
            Section::make('Ảnh minh họa')->columnSpanFull()->schema([
                CuratorPicker::make('image_id')->label('Ảnh của khối')->relationship('image', 'id')->helperText('Không bắt buộc. Khi chưa có ảnh, trang chủ hiển thị biểu tượng chung.'),
            ]),
            Section::make('Nguồn danh mục')->columnSpanFull()->visible(fn (?Industry $record): bool => filled($record?->source_url))->schema([
                TextInput::make('source_url')->label('Trang nguồn khi nhập dữ liệu')->disabled()->dehydrated(false),
            ]),
        ]);
    }
}
