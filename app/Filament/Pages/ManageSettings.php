<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\SiteAsset;
use App\Services\SettingService;
use App\Settings\AboutSettings;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\TrackingSettings;
use App\Settings\UploadSettings;
use App\Settings\WebsiteSettings;
use BackedEnum;
use DateTimeZone;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class ManageSettings extends Page
{
    protected static ?string $slug = 'settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Cài đặt website';

    protected static ?string $title = 'Cài đặt website';

    protected static string|UnitEnum|null $navigationGroup = 'Cài đặt website';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $website = app(WebsiteSettings::class);
        $homepage = app(HomepageSettings::class);
        $company = app(CompanySettings::class);
        $about = app(AboutSettings::class);
        $contact = app(ContactSettings::class);
        $seo = app(SeoSettings::class);
        $tracking = app(TrackingSettings::class);
        $upload = app(UploadSettings::class);

        $this->form->fill([
            'site_status' => $website->site_status,
            'multilingual_enabled' => $website->multilingual_enabled,
            'timezone' => $website->timezone,
            'site_name' => $website->site_name,
            'site_description' => $website->site_description,
            'copyright' => $website->copyright,
            'logo' => $this->currentMediaPath('logo'),
            'logo_footer' => $this->currentMediaPath('logo_footer'),
            'footer_background' => $this->currentMediaPath('footer_background'),
            'auth_background' => $this->currentMediaPath('auth_background'),
            'favicon' => $this->currentMediaPath('favicon'),
            'watermark' => $this->currentMediaPath('watermark'),
            'header_menu_id' => $website->header_menu_id,
            'mega_menu_id' => $website->mega_menu_id,
            'footer_menu_1_id' => $website->footer_menu_1_id,
            'footer_menu_2_id' => $website->footer_menu_2_id,
            'media_allowed_extensions' => $upload->media_allowed_extensions,
            'media_max_size' => $upload->media_max_size,
            'media_webp_conversion' => $upload->media_webp_conversion,
            'media_quality' => $upload->media_quality,
            'homepage_banner_type' => $homepage->homepage_banner_type,
            'homepage_sections' => $homepage->homepage_sections,
            'homepage_section_titles' => $homepage->homepage_section_titles,
            'homepage_stats' => $homepage->homepage_stats,
            'homepage_member_benefit_title' => $homepage->homepage_member_benefit_title,
            'homepage_member_benefits' => $homepage->homepage_member_benefits,
            'homepage_about_title' => $homepage->homepage_about_title,
            'homepage_about_text' => $homepage->homepage_about_text,
            'homepage_about_supporting_text' => $homepage->homepage_about_supporting_text,
            'about_image' => $this->currentMediaPath('about_image'),
            'homepage_intro_title' => $homepage->homepage_intro_title,
            'homepage_intro_text' => $homepage->homepage_intro_text,
            'homepage_reasons' => $homepage->homepage_reasons,
            'homepage_process' => $homepage->homepage_process,
            'homepage_capacity' => $homepage->homepage_capacity,
            'homepage_consultation_title' => $homepage->homepage_consultation_title,
            'homepage_consultation_text' => $homepage->homepage_consultation_text,
            'company_name' => $company->company_name,
            'tax_code' => $company->tax_code,
            'about_story' => $about->about_story,
            'about_history' => $about->about_history,
            'about_mission' => $about->about_mission,
            'about_vision' => $about->about_vision,
            'about_core_values' => $about->about_core_values,
            'about_page_label' => $about->about_page_label,
            'about_page_title' => $about->about_page_title,
            'about_page_intro' => $about->about_page_intro,
            'address' => $contact->address,
            'phones' => $this->contactPhonesForForm($contact),
            'branches' => $contact->branches,
            'email' => $contact->email,
            'map_embed' => $contact->map_embed,
            'working_hours' => $contact->working_hours,
            'facebook' => $contact->facebook,
            'instagram' => $contact->instagram,
            'youtube' => $contact->youtube,
            'tiktok' => $contact->tiktok,
            'zalo' => $contact->zalo,
            'seo_title' => $seo->seo_title,
            'seo_description' => $seo->seo_description,
            'seo_keywords' => $seo->seo_keywords,
            'seo_image' => $this->currentMediaPath('seo_image'),
            'head_code' => $tracking->head_code,
            'body_open_code' => $tracking->body_open_code,
            'body_close_code' => $tracking->body_close_code,
            'google_analytics_code' => $tracking->google_analytics_code,
            'meta_pixel_code' => $tracking->meta_pixel_code,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('website-settings')
                    ->tabs([
                        Tab::make('Website')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->schema($this->websiteSchema()),
                        Tab::make('Trang chủ')
                            ->icon(Heroicon::OutlinedHome)
                            ->schema($this->homepageSchema()),
                        Tab::make('Doanh nghiệp')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema($this->companySchema()),
                        Tab::make('Công ty / Giới thiệu')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema($this->aboutSchema()),
                        Tab::make('Liên hệ')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema($this->contactSchema()),
                        Tab::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema($this->seoSchema()),
                        Tab::make('Tracking')
                            ->icon(Heroicon::OutlinedChartBar)
                            ->schema($this->trackingSchema()),
                    ])
                    ->persistTabInQueryString('tab')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky($this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    /** @return array<int, Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Lưu cài đặt')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $service = app(SettingService::class);

        $service->updateCompany($data, app(CompanySettings::class));
        $service->updateWebsite($data, app(WebsiteSettings::class));
        $service->updateContact($data, app(ContactSettings::class));
        $service->updateSeo($data, app(SeoSettings::class));
        $service->updateTracking($data, app(TrackingSettings::class));
        $service->updateHomepage($data, app(HomepageSettings::class));
        $service->updateAbout($data, app(AboutSettings::class));
        $service->updateUpload($data, app(UploadSettings::class));

        Notification::make()
            ->title('Đã lưu cài đặt website')
            ->success()
            ->send();
    }

    /** @return array<int, Section> */
    private function websiteSchema(): array
    {
        return [
            Section::make('Trạng thái và nhận diện')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->description('Thông tin chung được dùng trong title, footer và metadata mặc định của website.')
                ->schema([
                    Toggle::make('site_status')
                        ->label('Website đang hoạt động')
                        ->default(true),
                    Toggle::make('multilingual_enabled')
                        ->label('Bật đa ngôn ngữ')
                        ->helperText('Frontend hiện ưu tiên nội dung tiếng Việt.'),
                    Select::make('timezone')
                        ->label('Múi giờ')
                        ->options($this->timezoneOptions())
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('site_name.vi')
                        ->label('Tên website')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('site_description.vi')
                        ->label('Mô tả website')
                        ->rows(3),
                    Textarea::make('copyright.vi')
                        ->label('Bản quyền footer')
                        ->rows(2),
                ])
                ->columns(2),
            Section::make('Nhận diện website')
                ->icon(Heroicon::OutlinedPhoto)
                ->schema([
                    $this->identityUpload('logo', 'Logo website', [
                        'image/png', 'image/jpeg', 'image/webp', 'image/svg+xml',
                    ]),
                    $this->identityUpload('logo_footer', 'Logo footer', [
                        'image/png', 'image/jpeg', 'image/webp', 'image/svg+xml',
                    ]),
                    $this->identityUpload('footer_background', 'Ảnh nền footer', [
                        'image/jpeg', 'image/png', 'image/webp',
                    ]),
                    $this->identityUpload('auth_background', 'Ảnh nền đăng nhập / đăng ký', [
                        'image/jpeg', 'image/png', 'image/webp',
                    ]),
                    $this->identityUpload('favicon', 'Favicon', [
                        'image/png', 'image/jpeg', 'image/webp', 'image/svg+xml',
                        'image/x-icon', 'image/vnd.microsoft.icon',
                    ]),
                ])
                ->columns(5),
            Section::make('Menu frontend')
                ->icon(Heroicon::OutlinedBars3)
                ->description('Để trống thì frontend dùng menu fallback theo giao diện hiện tại.')
                ->schema([
                    Select::make('header_menu_id')
                        ->label('Menu header')
                        ->options(fn (): array => $this->menuOptions())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('mega_menu_id')
                        ->label('Menu mega dịch vụ')
                        ->options(fn (): array => $this->menuOptions())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('footer_menu_1_id')
                        ->label('Menu footer 1')
                        ->options(fn (): array => $this->menuOptions())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('footer_menu_2_id')
                        ->label('Menu footer 2')
                        ->options(fn (): array => $this->menuOptions())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ])
                ->columns(2),
            Section::make('Quy định upload')
                ->icon(Heroicon::OutlinedPhoto)
                ->description('Cấu hình upload dùng chung cho thư viện và các form quản trị.')
                ->schema([
                    TextInput::make('media_allowed_extensions')
                        ->label('Định dạng cho phép')
                        ->helperText('Ví dụ: jpg,jpeg,png,webp,pdf.'),
                    TextInput::make('media_max_size')
                        ->label('Dung lượng tối đa (KB)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Toggle::make('media_webp_conversion')
                        ->label('Tự chuyển ảnh sang WebP'),
                    TextInput::make('media_quality')
                        ->label('Chất lượng ảnh')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->required(),
                ])
                ->columns(2),
            Section::make('Watermark')
                ->icon(Heroicon::OutlinedPhoto)
                ->schema([
                    $this->identityUpload('watermark', 'Ảnh watermark'),
                ])
                ->columns(1),
        ];
    }

    /** @return array<int, Section> */
    private function homepageSchema(): array
    {
        return [
            Section::make('Các khối trang chủ')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->description('Hero quản lý tại mục Slider. Các card showcase đang cố định theo giao diện hiện tại; nội dung dưới đây dùng cho các khối đã nối Settings.')
                ->schema([
                    Select::make('homepage_banner_type')
                        ->label('Kiểu banner')
                        ->options(['slider' => 'Slider'])
                        ->disabled()
                        ->dehydrated(),
                    CheckboxList::make('homepage_sections')
                        ->label('Các khối được bật')
                        ->options([
                            'intro' => 'Giới thiệu ngắn',
                            'services' => 'Dịch vụ',
                            'projects' => 'Dự án',
                            'featured_case' => 'Case study nổi bật',
                            'reasons' => 'Lý do chọn THT Media',
                            'process' => 'Quy trình',
                            'clients' => 'Khách hàng và đối tác',
                            'capacity' => 'Năng lực triển khai',
                            'testimonials' => 'Đánh giá khách hàng',
                            'posts' => 'Tin tức',
                            'consultation' => 'Tư vấn',
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Nội dung trang chủ')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->schema([
                    TextInput::make('homepage_consultation_title.vi')->label('Tiêu đề khu tư vấn'),
                    Textarea::make('homepage_consultation_text.vi')->label('Mô tả khu tư vấn')->rows(4),
                    Textarea::make('homepage_process.vi')->label('Quy trình hợp tác')->rows(6)->helperText('Mỗi dòng là một bước.'),
                    Textarea::make('homepage_capacity.vi')->label('Năng lực triển khai')->rows(6)->helperText('Mỗi dòng là một năng lực.'),
                ])
                ->columns(2),
            Section::make('Về chúng tôi trên trang chủ')
                ->icon(Heroicon::OutlinedInformationCircle)
                ->description('Nội dung và hình ảnh của khối Về chúng tôi ở trang chủ. Ảnh này cũng được dùng cho trang Giới thiệu.')
                ->schema([
                    $this->identityUpload('about_image', 'Ảnh phần Về chúng tôi'),
                    TextInput::make('homepage_about_title.vi')
                        ->label('Tiêu đề')
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('homepage_about_text.vi')
                        ->label('Nội dung chính')
                        ->rows(4),
                    Textarea::make('homepage_about_supporting_text.vi')
                        ->label('Nội dung bổ trợ')
                        ->rows(4),
                ])
                ->columns(2),
            Section::make('Các con số nổi bật')
                ->icon(Heroicon::OutlinedChartBar)
                ->description('Các con số hiển thị trong dải năng lực nổi bật ngay dưới slide.')
                ->schema([
                    Repeater::make('homepage_stats')
                        ->label('Danh sách con số')
                        ->schema([
                            TextInput::make('value')
                                ->label('Giá trị')
                                ->numeric()
                                ->required(),
                            TextInput::make('suffix')
                                ->label('Hậu tố')
                                ->placeholder('+ hoặc %')
                                ->maxLength(5),
                            TextInput::make('label')
                                ->label('Nhãn hiển thị')
                                ->required(),
                            Select::make('icon')
                                ->label('Biểu tượng')
                                ->options([
                                    'fa-solid fa-calendar-check' => 'Lịch / kinh nghiệm',
                                    'fa-solid fa-users' => 'Khách hàng',
                                    'fa-solid fa-film' => 'Dự án / video',
                                    'fa-solid fa-handshake' => 'Đồng hành',
                                    'fa-solid fa-chart-line' => 'Tăng trưởng',
                                ])
                                ->required(),
                        ])
                        ->columns(4)
                        ->columnSpanFull()
                        ->addActionLabel('Thêm con số')
                        ->reorderable(),
                ])
                ->columns(1),
            Section::make('Lợi ích dành cho hội viên')
                ->icon(Heroicon::OutlinedSparkles)
                ->description('Sáu thẻ quyền lợi xuất hiện ngay dưới phần giới thiệu trên trang chủ.')
                ->schema([
                    TextInput::make('homepage_member_benefit_title.vi')
                        ->label('Tiêu đề khối')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Repeater::make('homepage_member_benefits')
                        ->label('Danh sách quyền lợi')
                        ->schema([
                            TextInput::make('title')
                                ->label('Tiêu đề')
                                ->required()
                                ->maxLength(120),
                            Textarea::make('description')
                                ->label('Mô tả')
                                ->required()
                                ->rows(3),
                            Select::make('icon')
                                ->label('Biểu tượng')
                                ->options([
                                    'fa-solid fa-people-group' => 'Mạng lưới doanh nhân',
                                    'fa-solid fa-tower-broadcast' => 'Truyền thông thương hiệu',
                                    'fa-solid fa-graduation-cap' => 'Đào tạo',
                                    'fa-solid fa-calendar-days' => 'Sự kiện',
                                    'fa-solid fa-chart-line' => 'Giao thương',
                                    'fa-solid fa-hand-holding-heart' => 'Hỗ trợ doanh nghiệp',
                                    'fa-solid fa-handshake' => 'Hợp tác',
                                    'fa-solid fa-circle-check' => 'Quyền lợi khác',
                                ])
                                ->required(),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->maxItems(6)
                        ->defaultItems(0)
                        ->addActionLabel('Thêm quyền lợi')
                        ->reorderable()
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make('Vì sao chọn chúng tôi')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->description('Mỗi dòng là một lý do sẽ hiển thị thành một thẻ trên trang chủ.')
                ->schema([
                    Textarea::make('homepage_reasons.vi')
                        ->label('Các lý do')
                        ->rows(6)
                        ->helperText('Mỗi dòng là một ý.')
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make('Tiêu đề các khối')
                ->icon(Heroicon::OutlinedLanguage)
                ->schema($this->homepageTitleFields())
                ->columns(2),
        ];
    }

    /** @return array<int, Section> */
    private function companySchema(): array
    {
        return [
            Section::make('Thông tin doanh nghiệp')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->description('Thông tin cơ bản dùng trong nhận diện và nội dung chung của THT Media VN.')
                ->schema([
                    TextInput::make('company_name')
                        ->label('Tên doanh nghiệp')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('tax_code')
                        ->label('Mã số thuế')
                        ->maxLength(100),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, Section> */
    private function aboutSchema(): array
    {
        return [
            Section::make('Tên và phần mở đầu')
                ->icon(Heroicon::OutlinedDocumentText)
                ->description('Thiết lập cách gọi và nội dung mở đầu của trang Giới thiệu / Về chúng tôi.')
                ->schema([
                    TextInput::make('about_page_label.vi')
                        ->label('Nhãn trang')
                        ->required(),
                    TextInput::make('about_page_title.vi')
                        ->label('Tiêu đề trang')
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('about_page_intro.vi')
                        ->label('Mô tả mở đầu')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Nội dung giới thiệu')
                ->icon(Heroicon::OutlinedInformationCircle)
                ->description('Câu chuyện, lịch sử, sứ mệnh, tầm nhìn và giá trị cốt lõi của doanh nghiệp.')
                ->schema([
                    RichEditor::make('about_story.vi')
                        ->label('Câu chuyện doanh nghiệp')
                        ->columnSpanFull(),
                    Textarea::make('about_history.vi')
                        ->label('Lịch sử / cột mốc')
                        ->rows(4)
                        ->columnSpanFull(),
                    Textarea::make('about_mission.vi')
                        ->label('Sứ mệnh')
                        ->rows(4),
                    Textarea::make('about_vision.vi')
                        ->label('Tầm nhìn')
                        ->rows(4),
                    RichEditor::make('about_core_values.vi')
                        ->label('Giá trị cốt lõi')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, Section> */
    private function contactSchema(): array
    {
        return [
            Section::make('Thông tin liên hệ')
                ->icon(Heroicon::OutlinedPhone)
                ->schema([
                    TextInput::make('address')
                        ->label('Địa chỉ trụ sở chính')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Repeater::make('phones')
                        ->label('Các số điện thoại')
                        ->schema([
                            TextInput::make('label')
                                ->label('Nhãn')
                                ->placeholder('Hotline, Kinh doanh...')
                                ->maxLength(100),
                            TextInput::make('number')
                                ->label('Số điện thoại')
                                ->tel()
                                ->required()
                                ->maxLength(50),
                            Toggle::make('is_primary')
                                ->label('Số chính')
                                ->default(false)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Thêm số điện thoại')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => filled($state['label'] ?? null)
                            ? ($state['label'].' — '.($state['number'] ?? ''))
                            : ($state['number'] ?? 'Số điện thoại mới'))
                        ->columnSpanFull(),
                    TextInput::make('email')->label('Email')->email()->maxLength(255),
                    Textarea::make('working_hours')->label('Giờ làm việc')->rows(2),
                    Textarea::make('map_embed')->label('Mã nhúng bản đồ')->rows(4)->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Chi nhánh')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->description('Trụ sở chính dùng địa chỉ ở phần trên. Thêm các cơ sở khác tại đây; chi nhánh tắt sẽ không hiển thị ngoài frontend.')
                ->schema([
                    Repeater::make('branches')
                        ->label('Danh sách chi nhánh')
                        ->schema([
                            TextInput::make('name')
                                ->label('Tên chi nhánh')
                                ->required()
                                ->maxLength(150),
                            TextInput::make('address')
                                ->label('Địa chỉ')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Toggle::make('is_active')
                                ->label('Hiển thị chi nhánh')
                                ->default(true)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Thêm chi nhánh')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Chi nhánh mới')
                        ->columnSpanFull(),
                ])
                ->columns(1),
            Section::make('Mạng xã hội')
                ->icon(Heroicon::OutlinedShare)
                ->schema([
                    TextInput::make('facebook')->label('Facebook')->url()->maxLength(2048),
                    TextInput::make('instagram')->label('Instagram')->url()->maxLength(2048),
                    TextInput::make('youtube')->label('YouTube')->url()->maxLength(2048),
                    TextInput::make('tiktok')->label('TikTok')->url()->maxLength(2048),
                    TextInput::make('zalo')->label('Zalo')->url()->maxLength(2048),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, Section> */
    private function seoSchema(): array
    {
        return [
            Section::make('Metadata mặc định')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->description('Dùng cho các trang chưa có SEO riêng trong resource.')
                ->schema([
                    TextInput::make('seo_title.vi')->label('SEO title')->maxLength(255),
                    TextInput::make('seo_keywords.vi')->label('SEO keywords')->maxLength(500),
                    Textarea::make('seo_description.vi')->label('SEO description')->rows(4)->columnSpanFull(),
                    FileUpload::make('seo_image')
                        ->label('Ảnh chia sẻ mặc định')
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public_media')
                        ->storeFiles(false)
                        ->default(fn (): ?string => $this->currentMediaPath('seo_image'))
                        ->dehydrateStateUsing(fn (mixed $state): mixed => $state instanceof TemporaryUploadedFile ? $state : null)
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    /** @return array<int, Section> */
    private function trackingSchema(): array
    {
        return [
            Section::make('Mã theo dõi')
                ->icon(Heroicon::OutlinedChartBar)
                ->description('Các mã đo lường và quảng cáo được chèn vào phần head của frontend. Đây không phải cấu hình SEO.')
                ->schema([
                    Textarea::make('head_code')
                        ->label('Mã trong head')
                        ->rows(6)
                        ->helperText('Chèn ngay trước thẻ đóng head. Dùng cho mã cần nằm trong phần head.')
                        ->columnSpanFull(),
                    Textarea::make('body_open_code')
                        ->label('Mã ngay sau body mở')
                        ->rows(6)
                        ->helperText('Chèn ngay sau thẻ mở body. Vị trí này phù hợp với mã noscript của Google Tag Manager.')
                        ->columnSpanFull(),
                    Textarea::make('body_close_code')
                        ->label('Mã trước body đóng')
                        ->rows(6)
                        ->helperText('Chèn ngay trước thẻ đóng body. Dùng cho các script cần chạy gần cuối trang.')
                        ->columnSpanFull(),
                    Textarea::make('google_analytics_code')
                        ->label('Mã Google Analytics / tracking')
                        ->rows(6)
                        ->helperText('Dán đoạn mã Google Analytics hoặc mã tracking khác do nền tảng cung cấp.')
                        ->columnSpanFull(),
                    Textarea::make('meta_pixel_code')
                        ->label('Mã Meta Pixel')
                        ->rows(6)
                        ->helperText('Dán đoạn mã Meta Pixel do Meta cung cấp. Mã sẽ được chèn vào phần head frontend.')
                        ->columnSpanFull(),
                ])
                ->columns(1),
        ];
    }

    /** @return array<int, TextInput> */
    private function homepageTitleFields(): array
    {
        return [
            TextInput::make('homepage_section_titles.intro.vi')->label('Tiêu đề giới thiệu'),
            TextInput::make('homepage_section_titles.services.vi')->label('Tiêu đề dịch vụ'),
            TextInput::make('homepage_section_titles.projects.vi')->label('Tiêu đề dự án'),
            TextInput::make('homepage_section_titles.featured_case.vi')->label('Tiêu đề case study'),
            TextInput::make('homepage_section_titles.reasons.vi')->label('Tiêu đề lý do chọn'),
            TextInput::make('homepage_section_titles.process.vi')->label('Tiêu đề quy trình'),
            TextInput::make('homepage_section_titles.clients.vi')->label('Tiêu đề khách hàng'),
            TextInput::make('homepage_section_titles.capacity.vi')->label('Tiêu đề năng lực'),
            TextInput::make('homepage_section_titles.testimonials.vi')->label('Tiêu đề đánh giá'),
            TextInput::make('homepage_section_titles.posts.vi')->label('Tiêu đề tin tức'),
            TextInput::make('homepage_section_titles.consultation.vi')->label('Tiêu đề tư vấn'),
        ];
    }

    /** @return array<int|string, string> */
    private function menuOptions(): array
    {
        return Menu::query()
            ->get()
            ->sortBy(fn (Menu $menu): string => (string) $menu->getTranslation('name', 'vi'))
            ->mapWithKeys(fn (Menu $menu): array => [
                $menu->getKey() => $menu->getTranslation('name', 'vi').' ('.($menu->location ?: 'không gán vị trí').')',
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function timezoneOptions(): array
    {
        $timezones = DateTimeZone::listIdentifiers();

        return array_combine($timezones, $timezones);
    }

    /** @param array<int, string> $acceptedFileTypes */
    private function identityUpload(string $collection, string $label, array $acceptedFileTypes = ['image/png', 'image/jpeg', 'image/webp']): FileUpload
    {
        return FileUpload::make($collection)
            ->label($label)
            ->image()
            ->acceptedFileTypes($acceptedFileTypes)
            ->disk('public_media')
            ->storeFiles(false)
            ->default(fn (): ?string => $this->currentMediaPath($collection))
            ->dehydrateStateUsing(fn (mixed $state): mixed => $state instanceof TemporaryUploadedFile ? $state : null)
            ->maxSize($collection === 'favicon' ? 2048 : 10240);
    }

    private function currentMediaPath(string $collection): ?string
    {
        $media = SiteAsset::current()->getFirstMedia($collection);

        return $media?->getPathRelativeToRoot();
    }

    /** @return array<int, array{label: string, number: string, is_primary: bool}> */
    private function contactPhonesForForm(ContactSettings $contact): array
    {
        if ($contact->phones !== []) {
            return $contact->phones;
        }

        if (filled($contact->phone)) {
            return [[
                'label' => 'Hotline chính',
                'number' => $contact->phone,
                'is_primary' => true,
            ]];
        }

        return [];
    }
}
