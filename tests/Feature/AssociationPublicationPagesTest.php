<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssociationPublicationPagesTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name = 'Hoạt động chi hội'): PostCategory
    {
        $category = PostCategory::query()->create(['name' => ['vi' => $name], 'is_active' => true]);
        $category->slugs()->updateOrCreate(['locale' => 'vi'], ['slug' => Str::slug($name)]);

        return $category;
    }

    private function article(PostCategory $category, string $title, array $attributes = []): Post
    {
        return app(PostService::class)->create(array_replace([
            'post_category_id' => $category->id,
            'name' => ['vi' => $title],
            'slug' => Str::slug($title),
            'summary' => ['vi' => 'Nội dung giới thiệu bài viết'],
            'content' => ['vi' => '<h2>Hoạt động kết nối</h2><p>Nội dung chương trình.</p>'],
            'is_active' => true,
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    private function event(array $attributes = []): Event
    {
        return Event::query()->create(array_replace([
            'title' => 'Gặp gỡ doanh nghiệp',
            'slug' => (string) Str::ulid(),
            'status' => 'published',
            'visibility' => 'public',
            'starts_at' => now()->addDay(),
        ], $attributes));
    }

    public function test_category_routes_filter_by_localized_slug_and_breadcrumbs_return_to_the_category(): void
    {
        $category = $this->category();
        $post = $this->article($category, 'Gặp mặt chi hội');
        $this->article($this->category('Tin doanh nghiệp'), 'Tin ở chuyên mục khác');

        $this->get(route('news.show', $category->slug))->assertOk()
            ->assertViewHas('newsItems', fn ($items) => $items->total() === 1 && $items->first()['title'] === 'Gặp mặt chi hội')
            ->assertViewHas('breadcrumbs', fn ($items) => $items[1]['url'] === route('news.index') && $items[2]['label'] === 'Hoạt động chi hội');
        $this->get(route('content.show', ['domain' => $category->slug, 'slug' => $post->slug]))->assertOk()
            ->assertViewHas('breadcrumbs', fn ($items) => $items[2]['url'] === route('news.show', $category->slug))
            ->assertSee('href="#hoat-dong-ket-noi"', false)
            ->assertSee('id="hoat-dong-ket-noi"', false)
            ->assertSee(route('comments.store'), false);

        $category->update(['is_active' => false]);
        $this->get(route('news.show', $category->slug))->assertNotFound();
        $this->get(route('news.show', $post->slug))->assertOk()
            ->assertViewHas('article', fn ($article) => $article['category_url'] === null);
    }

    public function test_news_search_pagination_and_category_counts_exclude_unpublished_content(): void
    {
        $category = $this->category();
        for ($i = 0; $i < 11; $i++) {
            $this->article($category, 'Chuyển đổi số '.$i);
        }
        $this->article($category, 'Tin không liên quan');
        $this->article($category, 'Chuyển đổi số hẹn giờ', ['published_at' => now()->addDay()]);
        $this->article($category, 'Chuyển đổi số bản nháp', ['is_active' => false]);
        $this->article($category, 'Chuyển đổi số đã xóa')->delete();
        $this->get(route('news.show', ['slug' => $category->slug, 'q' => 'Chuyển đổi số']))->assertOk()
            ->assertViewHas('newsItems', fn ($items) => $items->total() === 11 && $items->count() === 10 && str_contains($items->nextPageUrl(), 'q='))
            ->assertViewHas('categories', fn ($items) => $items->first()->posts_count === 12);
        $this->get(route('news.show', ['slug' => $category->slug, 'q' => 'Chuyển đổi số', 'page' => 2]))->assertOk()
            ->assertViewHas('newsItems', fn ($items) => $items->count() === 1);
        $this->get(route('news.index', ['q' => 'không có kết quả']))->assertOk()->assertSee('Không tìm thấy bài viết phù hợp');
    }

    public function test_event_periods_include_ongoing_programs_and_sort_in_chronological_order(): void
    {
        $this->freezeTime();
        $later = $this->event(['starts_at' => now()->addWeek()]);
        $next = $this->event();
        $ongoing = $this->event(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $past = $this->event(['starts_at' => now()->subDay()]);
        $older = $this->event(['starts_at' => now()->subWeek(), 'ends_at' => now()->subDays(6)]);
        foreach ([['status' => 'draft'], ['status' => 'cancelled'], ['visibility' => 'private'], ['published_at' => now()->addHour()], ['deleted_at' => now()]] as $attributes) {
            $this->event($attributes);
        }
        $this->get(route('events.index'))->assertOk()
            ->assertViewHas('events', fn ($items) => $items->getCollection()->pluck('event.id')->all() === [$ongoing->id, $next->id, $later->id])
            ->assertViewHas('upcomingCount', 3)->assertViewHas('pastCount', 2);
        $this->get(route('events.index', ['period' => 'past']))->assertOk()
            ->assertViewHas('events', fn ($items) => $items->getCollection()->pluck('event.id')->all() === [$past->id, $older->id]);
    }

    public function test_event_search_and_pagination_preserve_the_selected_period(): void
    {
        for ($i = 0; $i < 11; $i++) {
            $this->event(['title' => 'Tọa đàm '.$i, 'venue_name' => 'Hội trường Bắc Ninh', 'starts_at' => now()->subDays($i + 1)]);
        }
        $this->event(['title' => 'Chương trình tại Hà Nội', 'starts_at' => now()->subDay()]);
        $this->get(route('events.index', ['period' => 'past', 'q' => 'Bắc Ninh']))->assertOk()
            ->assertViewHas('events', fn ($items) => $items->total() === 11 && $items->count() === 10 && str_contains($items->nextPageUrl(), 'period=past') && str_contains($items->nextPageUrl(), 'q='))
            ->assertViewHas('upcomingCount', 0)->assertViewHas('pastCount', 11);
        $this->get(route('events.index', ['period' => 'past', 'q' => 'Bắc Ninh', 'page' => 2]))->assertOk()
            ->assertViewHas('events', fn ($items) => $items->count() === 1);
    }

    public function test_registration_links_and_details_agree_when_capacity_or_registration_windows_change(): void
    {
        $event = $this->event(['capacity' => 2]);
        $registrationUrl = route('events.show', $event->slug).'#dang-ky';
        $this->get(route('events.index'))->assertOk()->assertSee($registrationUrl, false);
        $this->get(route('events.show', $event->slug))->assertOk()
            ->assertViewHas('registrationIsOpen', true)->assertSee(route('events.register', $event->slug), false)
            ->assertViewHas('breadcrumbs', fn ($items) => $items[1]['url'] === route('events.index') && $items[2]['label'] === $event->title);
        $registration = $event->registrations()->create(['full_name' => 'Người tham dự', 'guest_count' => 1, 'status' => 'registered']);
        $this->get(route('events.index'))->assertOk()->assertDontSee($registrationUrl, false);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Sự kiện đã đủ số lượng đăng ký.')->assertViewHas('registrationIsOpen', false);
        $registration->update(['status' => 'cancelled']);
        $this->get(route('events.index'))->assertOk()->assertSee($registrationUrl, false);
        $event->update(['registration_opens_at' => now()->addHour()]);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Mở đăng ký lúc')->assertViewHas('registrationIsOpen', false);
        $event->update(['registration_opens_at' => null, 'registration_closes_at' => now()->subMinute()]);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Đã hết thời gian đăng ký')->assertViewHas('registrationIsOpen', false);
        $event->update(['starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Chương trình đang diễn ra')->assertViewHas('registrationIsOpen', false);
        $event->update(['ends_at' => now()->subMinute()]);
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Sự kiện đã kết thúc.')->assertViewHas('registrationIsOpen', false);
    }

    public function test_event_breadcrumb_structured_data_safely_encodes_title_markup(): void
    {
        $event = $this->event(['title' => 'Chương trình </script><script>alert(1)</script>']);
        $this->get(route('events.show', $event->slug))->assertOk()
            ->assertSee($event->title)
            ->assertDontSee('</script><script>alert(1)</script>', false)
            ->assertSee('\\u003C/script\\u003E', false);
    }
}
