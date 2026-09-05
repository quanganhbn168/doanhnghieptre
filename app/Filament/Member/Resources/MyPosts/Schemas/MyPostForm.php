<?php

namespace App\Filament\Member\Resources\MyPosts\Schemas;

use App\Models\Business;
use App\Models\PostCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MyPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin bài viết')->description('Giới thiệu sản phẩm, dịch vụ hoặc tin tức doanh nghiệp. Bài mới và bài chỉnh sửa đều chờ Văn phòng hoặc Ban Truyền thông duyệt.')->schema([
                Select::make('business_id')->label('Doanh nghiệp')->required()->searchable()->options(fn () => Business::query()->representedBy(auth('web')->user())->where('status', 'approved')->pluck('name', 'id')),
                Select::make('post_category_id')->label('Chuyên mục đề xuất')->required()->searchable()->options(fn () => PostCategory::query()->where('is_active', true)->get()->mapWithKeys(fn ($category) => [$category->id => $category->getTranslation('name', 'vi')])),
                TextInput::make('title')->label('Tiêu đề')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('summary_text')->label('Giới thiệu ngắn')->required()->maxLength(1000)->rows(3)->columnSpanFull(),
                FileUpload::make('image')->label('Ảnh đại diện')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->storeFiles(false)->helperText('Tối đa 5 MB. Để trống khi chỉnh sửa để giữ ảnh hiện tại.')->columnSpanFull(),
                RichEditor::make('body')->label('Nội dung')->required()->maxLength(50000)->toolbarButtons(['bold', 'italic', 'underline', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'undo', 'redo'])->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }
}
