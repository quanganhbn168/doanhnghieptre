<?php

namespace App\Services;

use App\Models\SiteAsset;
use App\Settings\AboutSettings;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\TrackingSettings;
use App\Settings\UploadSettings;
use App\Settings\WebsiteSettings;

class SettingService
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly WebsiteSettingsService $websiteSettings,
        private readonly SiteChromeCache $siteChromeCache,
    ) {}

    public function updateCompany(array $data, CompanySettings $settings): void
    {
        $settings->company_name = $data['company_name'] ?? null;
        $settings->tax_code = $data['tax_code'] ?? null;
        $settings->save();

        $this->websiteSettings->refresh();
        $this->siteChromeCache->forget();
    }

    public function updateWebsite(array $data, WebsiteSettings $settings): void
    {
        $settings->site_status = isset($data['site_status']);
        $settings->multilingual_enabled = isset($data['multilingual_enabled']);
        $settings->timezone = $data['timezone'] ?? 'Asia/Ho_Chi_Minh';
        $settings->copyright = $data['copyright'] ?? [];
        $settings->site_name = $data['site_name'] ?? [];
        $settings->site_description = $data['site_description'] ?? [];

        foreach (['header_menu_id', 'mega_menu_id', 'footer_menu_1_id', 'footer_menu_2_id'] as $field) {
            $settings->{$field} = filled($data[$field] ?? null) ? (int) $data[$field] : null;
        }

        $settings->save();
        cache()->forget('active_languages');
        $this->websiteSettings->refresh();
        config(['app.timezone' => $settings->timezone]);
        config(['app.name' => $settings->site_name['vi'] ?? config('app.name')]);
        date_default_timezone_set($settings->timezone);
        $this->syncMedia($data, ['logo', 'logo_footer', 'footer_background', 'auth_background', 'favicon', 'watermark']);
        $this->siteChromeCache->forget();
    }

    public function updateContact(array $data, ContactSettings $settings): void
    {
        $settings->address = $data['address'] ?? null;
        $settings->phones = $this->normalizeContactPhones($data['phones'] ?? $settings->phones);
        $settings->phone = collect($settings->phones)->firstWhere('is_primary', true)['number']
            ?? ($settings->phones[0]['number'] ?? null);
        $settings->branches = $this->normalizeContactBranches($data['branches'] ?? $settings->branches);
        $settings->email = $data['email'] ?? null;
        $settings->map_embed = $data['map_embed'] ?? null;
        $settings->working_hours = $data['working_hours'] ?? null;

        $settings->facebook = $data['facebook'] ?? null;
        $settings->instagram = $data['instagram'] ?? null;
        $settings->youtube = $data['youtube'] ?? null;
        $settings->tiktok = $data['tiktok'] ?? null;
        $settings->zalo = $data['zalo'] ?? null;

        $settings->save();
        $this->websiteSettings->refresh();
        $this->siteChromeCache->forget();
    }

    /** @return array<int, array{label: string, number: string, is_primary: bool}> */
    private function normalizeContactPhones(mixed $phones): array
    {
        $normalized = collect(is_array($phones) ? $phones : [])
            ->filter(fn (mixed $phone): bool => is_array($phone) && filled($phone['number'] ?? null))
            ->map(fn (array $phone): array => [
                'label' => trim((string) ($phone['label'] ?? '')),
                'number' => trim((string) $phone['number']),
                'is_primary' => (bool) ($phone['is_primary'] ?? false),
            ])
            ->values()
            ->all();

        if ($normalized === []) {
            return [];
        }

        $primaryIndex = collect($normalized)->search(fn (array $phone): bool => $phone['is_primary']);
        $primaryIndex = $primaryIndex === false ? 0 : $primaryIndex;

        return collect($normalized)
            ->map(fn (array $phone, int $index): array => [
                ...$phone,
                'is_primary' => $index === $primaryIndex,
            ])
            ->all();
    }

    /** @return array<int, array{name: string, address: string, is_active: bool}> */
    private function normalizeContactBranches(mixed $branches): array
    {
        return collect(is_array($branches) ? $branches : [])
            ->filter(fn (mixed $branch): bool => is_array($branch) && filled($branch['name'] ?? null) && filled($branch['address'] ?? null))
            ->map(fn (array $branch): array => [
                'name' => trim((string) $branch['name']),
                'address' => trim((string) $branch['address']),
                'is_active' => (bool) ($branch['is_active'] ?? true),
            ])
            ->values()
            ->all();
    }

    public function updateSeo(array $data, SeoSettings $settings): void
    {
        $settings->seo_title = $data['seo_title'] ?? [];
        $settings->seo_description = $data['seo_description'] ?? [];
        $settings->seo_keywords = $data['seo_keywords'] ?? [];

        $settings->save();
        $this->websiteSettings->refresh();
        $this->syncMedia($data, ['seo_image']);
        $this->siteChromeCache->forget();
    }

    public function updateTracking(array $data, TrackingSettings $settings): void
    {
        $settings->head_code = $data['head_code'] ?? null;
        $settings->body_open_code = $data['body_open_code'] ?? null;
        $settings->body_close_code = $data['body_close_code'] ?? null;
        $settings->google_analytics_code = $data['google_analytics_code'] ?? null;
        $settings->meta_pixel_code = $data['meta_pixel_code'] ?? null;

        $settings->save();
    }

    public function updateHomepage(array $data, HomepageSettings $settings): void
    {
        $settings->homepage_banner_type = 'slider';
        $settings->homepage_sections = $data['homepage_sections'] ?? [];
        $settings->homepage_section_titles = $data['homepage_section_titles'] ?? [];
        $settings->homepage_stats = $data['homepage_stats'] ?? [];
        $settings->homepage_member_benefit_title = $data['homepage_member_benefit_title'] ?? [];
        $settings->homepage_member_benefits = $this->normalizeHomepageMemberBenefits($data['homepage_member_benefits'] ?? []);
        $settings->homepage_about_title = $data['homepage_about_title'] ?? [];
        $settings->homepage_about_text = $data['homepage_about_text'] ?? [];
        $settings->homepage_about_supporting_text = $data['homepage_about_supporting_text'] ?? [];
        $settings->homepage_intro_title = $data['homepage_intro_title'] ?? [];
        $settings->homepage_intro_text = $data['homepage_intro_text'] ?? [];
        $settings->homepage_reasons = $data['homepage_reasons'] ?? [];
        $settings->homepage_process = $data['homepage_process'] ?? [];
        $settings->homepage_capacity = $data['homepage_capacity'] ?? [];
        $settings->homepage_consultation_title = $data['homepage_consultation_title'] ?? [];
        $settings->homepage_consultation_text = $data['homepage_consultation_text'] ?? [];

        $settings->save();
        $this->syncMedia($data, ['about_image']);
    }

    /** @return array<int, array{icon: string, title: string, description: string}> */
    private function normalizeHomepageMemberBenefits(mixed $benefits): array
    {
        return collect(is_array($benefits) ? $benefits : [])
            ->filter(fn (mixed $benefit): bool => is_array($benefit) && filled($benefit['title'] ?? null))
            ->map(fn (array $benefit): array => [
                'icon' => trim((string) ($benefit['icon'] ?? 'fa-solid fa-circle-check')),
                'title' => trim((string) $benefit['title']),
                'description' => trim((string) ($benefit['description'] ?? '')),
            ])
            ->values()
            ->all();
    }

    public function updateAbout(array $data, AboutSettings $settings): void
    {
        $settings->about_page_label = $data['about_page_label'] ?? [];
        $settings->about_page_title = $data['about_page_title'] ?? [];
        $settings->about_page_intro = $data['about_page_intro'] ?? [];
        $settings->about_story = $data['about_story'] ?? [];
        $settings->about_history = $data['about_history'] ?? [];
        $settings->about_mission = $data['about_mission'] ?? [];
        $settings->about_vision = $data['about_vision'] ?? [];
        $settings->about_core_values = $data['about_core_values'] ?? [];

        $settings->save();
    }

    public function updateUpload(array $data, UploadSettings $settings): void
    {
        $settings->media_allowed_extensions = $data['media_allowed_extensions'] ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,webm,mov';
        $settings->media_max_size = (int) ($data['media_max_size'] ?? 10);
        $settings->media_webp_conversion = isset($data['media_webp_conversion']);
        $settings->media_quality = (int) ($data['media_quality'] ?? 100);

        $settings->save();
        $this->syncMedia($data, ['default_promotion_banner', 'default_post_banner']);
        $this->siteChromeCache->forget();
    }

    /** Đồng bộ các ảnh cấu hình vào collection single-file của Spatie Media Library. */
    private function syncMedia(array $data, array $collections): void
    {
        $assets = SiteAsset::current();
        foreach ($collections as $collection) {
            $this->mediaService->syncSingle(
                $assets,
                $collection,
                $data[$collection] ?? null,
                (bool) ($data[$collection.'_remove'] ?? false),
            );
        }
    }
}
