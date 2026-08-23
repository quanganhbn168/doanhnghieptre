<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomepageSettings extends Settings
{
    public ?string $homepage_banner_type; // slider, video, static_image

    public array $homepage_sections;

    public array $homepage_section_titles;

    public array $homepage_stats;

    public array $homepage_member_benefit_title;

    public array $homepage_member_benefits;

    public array $homepage_about_title;

    public array $homepage_about_text;

    public array $homepage_about_supporting_text;

    public array $homepage_intro_title;

    public array $homepage_intro_text;

    public array $homepage_reasons;

    public array $homepage_process;

    public array $homepage_capacity;

    public array $homepage_consultation_title;

    public array $homepage_consultation_text;

    public static function group(): string
    {
        return 'homepage';
    }
}
