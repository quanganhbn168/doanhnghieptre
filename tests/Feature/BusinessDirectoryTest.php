<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_chips_remove_one_filter_preserving_the_rest_and_reset_pagination(): void
    {
        $industry = Industry::query()->create(['name' => 'Khối dịch vụ', 'slug' => 'dich-vu', 'is_active' => true, 'is_member_group' => true]);
        $chapter = BusinessChapter::query()->create(['name' => 'Chi hội Từ Sơn', 'slug' => 'tu-son', 'is_active' => true]);
        $filters = ['q' => 'Dịch vụ', 'industry' => $industry->slug, 'size' => 'small', 'chapter' => $chapter->slug];

        $response = $this->get(route('directory.index', [...$filters, 'q' => '  Dịch vụ  ', 'page' => 3, 'unknown' => 'ignored']))
            ->assertOk()->assertViewHas('filters', $filters)->assertSee('Xóa tất cả bộ lọc');
        $chips = $response->viewData('activeFilters');
        $this->assertSame(['Từ khóa: Dịch vụ', 'Khối dịch vụ', 'Quy mô nhỏ', 'Chi hội Từ Sơn'], array_column($chips, 'label'));
        foreach (array_keys($filters) as $index => $key) {
            parse_str(parse_url($chips[$index]['url'], PHP_URL_QUERY) ?? '', $remaining);
            $this->assertSame(array_diff_key($filters, [$key => true]), $remaining);
        }
        $this->get(route('directory.index', ['q' => '  ', 'industry' => '', 'chapter' => '']))
            ->assertOk()->assertViewHas('filters', [])->assertViewHas('activeFilters', []);
    }

    public function test_paginated_results_keep_filters_and_exclude_applications_still_under_review(): void
    {
        foreach (range(1, 13) as $index) {
            Business::query()->create(['name' => sprintf('Doanh nghiệp dịch vụ %02d', $index), 'slug' => 'business-'.$index, 'status' => 'approved', 'business_size' => 'small']);
        }
        Business::query()->create(['name' => 'Doanh nghiệp dịch vụ chưa duyệt', 'slug' => 'pending', 'status' => 'chapter_pending', 'business_size' => 'small']);
        Business::query()->create(['name' => 'Doanh nghiệp dịch vụ quy mô lớn', 'slug' => 'large', 'status' => 'approved', 'business_size' => 'large']);

        $response = $this->get(route('directory.index', ['q' => 'dịch vụ', 'size' => 'small', 'unknown' => 'ignored']))
            ->assertOk()->assertDontSee('Doanh nghiệp dịch vụ chưa duyệt')->assertDontSee('Doanh nghiệp dịch vụ quy mô lớn')
            ->assertSee('Trang sau')->assertDontSee('pagination.next');
        $paginator = $response->viewData('businesses');
        $this->assertSame(13, $paginator->total());
        parse_str(parse_url($paginator->nextPageUrl(), PHP_URL_QUERY), $query);
        $this->assertSame(['q' => 'dịch vụ', 'size' => 'small', 'page' => '2'], $query);
        $this->get($paginator->nextPageUrl())->assertOk()->assertSee('Doanh nghiệp dịch vụ 13')
            ->assertViewHas('businesses', fn ($items) => $items->count() === 1);
    }

    public function test_contact_links_are_usable_and_location_is_not_duplicated_or_invented(): void
    {
        Business::query()->create([
            'name' => 'Doanh nghiệp có liên hệ', 'slug' => 'contact', 'status' => 'approved',
            'district' => 'Bắc Ninh', 'province' => 'bắc ninh', 'phone' => '+84 (222) 123 4567',
            'email' => 'hello@example.test', 'website' => 'example.test/gioi-thieu',
        ]);
        Business::query()->create([
            'name' => 'Doanh nghiệp chưa có liên hệ', 'slug' => 'no-contact', 'status' => 'approved',
            'website' => 'javascript:alert(1)',
        ]);
        $response = $this->get(route('directory.index'))->assertOk()
            ->assertSee('href="tel:+842221234567"', false)->assertSee('href="mailto:hello@example.test"', false)
            ->assertSee('href="https://example.test/gioi-thieu"', false)->assertDontSee('javascript:alert(1)', false);
        $items = $response->viewData('businesses')->getCollection()->keyBy('slug');
        $this->assertSame('Bắc Ninh', $items['contact']->location_label);
        $this->assertNull($items['no-contact']->location_label);
        $this->assertNull($items['no-contact']->website_url);
    }

    public function test_empty_search_has_a_clear_way_back_to_the_directory(): void
    {
        $this->get(route('directory.index', ['q' => 'Không tìm thấy doanh nghiệp']))->assertOk()
            ->assertSee('Chưa tìm thấy doanh nghiệp phù hợp')->assertSee('Xóa tất cả bộ lọc')
            ->assertViewHas('businesses', fn ($items) => $items->total() === 0);
    }
}
