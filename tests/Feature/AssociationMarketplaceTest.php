<?php

namespace Tests\Feature;

use App\Filament\Association\Resources\Events\Pages\CreateEvent;
use App\Filament\Association\Resources\Events\Pages\EditEvent;
use App\Filament\Association\Resources\Events\RelationManagers\RegistrationsRelationManager;
use App\Filament\Association\Resources\Intros\Pages\CreateIntro;
use App\Filament\Association\Resources\Posts\Pages\CreatePost;
use App\Filament\Association\Resources\Posts\Pages\EditPost;
use App\Filament\Association\Resources\TradePosts\Pages\ViewTradePost;
use App\Filament\Member\Resources\MyTradePosts\Pages\CreateMyTradePost;
use App\Filament\Member\Resources\MyTradePosts\Pages\EditMyTradePost;
use App\Filament\Member\Resources\MyTradePosts\Pages\ListMyTradePosts;
use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\TradePost;
use App\Models\User;
use App\Services\PostService;
use App\Services\TradePostService;
use Database\Seeders\AssociationRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AssociationMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public_media');
        $this->seed(AssociationRoleSeeder::class);
    }

    private function staff(): User
    {
        $user = User::factory()->create();
        $user->assignRole('association_manager');

        return $user;
    }

    private function business(User $user): Business
    {
        return Business::query()->create(['name' => 'Doanh nghiệp '.Str::random(8), 'slug' => Str::uuid(), 'status' => 'approved', 'submitted_by_user_id' => $user->id]);
    }

    private function data(Business $business): array
    {
        return ['business_id' => $business->id, 'type' => 'buy', 'title' => 'Cần dịch vụ vận chuyển kiểm thử', 'summary' => 'Tìm đối tác vận chuyển hàng hóa', 'content' => 'Nội dung chi tiết', 'contact_name' => 'Người liên hệ', 'contact_phone' => '0900000000'];
    }

    private function panel(User $user, string $panel = 'association'): void
    {
        $this->actingAs($user, $panel === 'member' ? 'web' : 'admin');
        Filament::setCurrentPanel(Filament::getPanel($panel));
        Filament::bootCurrentPanel();
    }

    private function event(array $overrides = []): Event
    {
        return Event::query()->create(array_replace(['title' => 'Sự kiện kiểm thử', 'slug' => 'su-kien-'.Str::lower(Str::random(8)), 'starts_at' => now()->addDays(3), 'status' => 'published', 'visibility' => 'public', 'content' => '<p>Chương trình kết nối</p>', 'capacity' => 3], $overrides));
    }

    public function test_member_creates_business_listing_and_association_approves_before_publication(): void
    {
        $user = User::factory()->create();
        $business = $this->business($user);
        $this->panel($user, 'member');
        $this->get('/thanh-vien/my-trade-posts/create')->assertOk();
        Livewire::test(CreateMyTradePost::class)->fillForm($this->data($business))->call('create')->assertHasNoFormErrors();
        $post = TradePost::query()->sole();
        $this->assertSame('pending', $post->status);
        $this->assertNotNull($post->member_id);
        $this->get(route('trade.show', $post->slug))->assertNotFound();
        $this->get(route('trade.index'))->assertOk()->assertDontSee($post->title);
        $this->panel($this->staff());
        Livewire::test(ViewTradePost::class, ['record' => $post->id])->callAction('approve')->assertHasNoActionErrors();
        $this->assertSame('approved', $post->fresh()->status);
        $this->get(route('trade.show', $post->slug))->assertOk()->assertSee($post->title)->assertSee('tel:0900000000', false);
        $this->get(route('trade.index', ['type' => 'buy']))->assertSee($post->title);
        $this->get(route('trade.index', ['type' => 'sell']))->assertDontSee($post->title);
    }

    public function test_member_cannot_post_for_or_edit_another_business(): void
    {
        $user = User::factory()->create();
        $own = $this->business($user);
        $otherUser = User::factory()->create();
        $other = $this->business($otherUser);
        $post = app(TradePostService::class)->submit($otherUser, $this->data($other));
        $this->panel($user, 'member');
        Livewire::test(CreateMyTradePost::class)->fillForm($this->data($other))->call('create')->assertHasFormErrors(['business_id']);
        $this->assertDatabaseCount('trade_posts', 1);
        $this->get('/thanh-vien/my-trade-posts/'.$post->id.'/edit')->assertNotFound();
        Livewire::test(ListMyTradePosts::class)->assertCanNotSeeTableRecords([$post]);
        $this->expectException(ValidationException::class);
        app(TradePostService::class)->submit($user, $this->data($other));
    }

    public function test_approved_edits_resubmit_and_owner_can_close(): void
    {
        $user = User::factory()->create();
        $business = $this->business($user);
        $service = app(TradePostService::class);
        $post = $service->submit($user, $this->data($business));
        $service->moderate($post, $this->staff(), 'approve');
        $this->panel($user, 'member');
        Livewire::test(EditMyTradePost::class, ['record' => $post->id])->fillForm(['title' => 'Dịch vụ cập nhật'])->call('save')->assertHasNoFormErrors();
        $post->refresh();
        $this->assertSame('pending', $post->status);
        $this->assertNull($post->approved_at);
        $this->get(route('trade.show', $post->slug))->assertNotFound();
        Livewire::test(ListMyTradePosts::class)->callTableAction('close', $post)->assertHasNoTableActionErrors();
        $this->assertSame('closed', $post->fresh()->status);
    }

    public function test_rejection_reason_is_visible_to_member_and_resubmission_clears_it(): void
    {
        $user = User::factory()->create();
        $business = $this->business($user);
        $post = app(TradePostService::class)->submit($user, $this->data($business));
        $this->panel($this->staff());
        Livewire::test(ViewTradePost::class, ['record' => $post->id])->callAction('reject', ['review_note' => 'Bổ sung khu vực cung cấp'])->assertHasNoActionErrors();
        $this->panel($user, 'member');
        Livewire::test(ListMyTradePosts::class)->assertSee('Bổ sung khu vực cung cấp');
        Livewire::test(EditMyTradePost::class, ['record' => $post->id])->fillForm(['location_label' => 'Bắc Ninh'])->call('save')->assertHasNoFormErrors();
        $this->assertNull($post->fresh()->review_note);
        $this->assertSame('pending', $post->fresh()->status);
    }

    public function test_expired_deleted_and_unapproved_business_listings_are_not_public(): void
    {
        $user = User::factory()->create();
        $business = $this->business($user);
        $post = app(TradePostService::class)->submit($user, $this->data($business));
        $post->update(['status' => 'approved', 'expires_at' => now()->subMinute()]);
        $this->get(route('trade.show', $post->slug))->assertNotFound();
        $this->get(route('trade.index'))->assertDontSee($post->title);
        $post->update(['expires_at' => null]);
        $business->update(['status' => 'pending']);
        $this->get(route('trade.show', $post->slug))->assertNotFound();
        $this->get(route('trade.index'))->assertDontSee($post->title);
        $business->update(['status' => 'approved']);
        $post->delete();
        $this->get(route('trade.show', $post->slug))->assertNotFound();
        $this->get(route('trade.index'))->assertDontSee($post->title);
    }

    public function test_association_content_and_media_routes_deny_chapter_staff(): void
    {
        $this->panel($this->staff());
        foreach (['tin-tuc', 'tin-tuc/create', 'chuyen-muc', 'thong-tin-hoi', 'thong-tin-hoi/create', 'su-kien', 'su-kien/create', 'cho-doanh-nghiep', 'media'] as $path) {
            $this->get('/hoi/'.$path)->assertOk();
        }
        $chapterUser = User::factory()->create();
        $chapterUser->assignRole('chapter_manager');
        $chapter = BusinessChapter::query()->create(['name' => 'Chi hội', 'slug' => 'test', 'is_active' => true]);
        $chapterUser->managedChapters()->attach($chapter);
        $this->panel($chapterUser);
        foreach (['tin-tuc', 'tin-tuc/create', 'chuyen-muc', 'thong-tin-hoi', 'su-kien', 'su-kien/create', 'cho-doanh-nghiep', 'media'] as $path) {
            $this->get('/hoi/'.$path)->assertForbidden();
        }
        $this->get('/hoi')->assertOk();
    }

    public function test_chapter_navigation_only_shows_its_membership_work(): void
    {
        $user = User::factory()->create();
        $user->assignRole('chapter_manager');
        $chapter = BusinessChapter::query()->create(['name' => 'Chi hội', 'slug' => 'chapter-nav', 'is_active' => true]);
        $user->managedChapters()->attach($chapter);
        $this->panel($user);
        $this->get('/hoi')->assertOk()->assertDontSee('Duyệt tin giao thương')->assertDontSee('Sự kiện &amp; đăng ký');
    }

    public function test_association_creates_publishes_and_hides_existing_cms_news_and_intro(): void
    {
        $this->panel($this->staff());
        $category = PostCategory::query()->create(['name' => ['vi' => 'Tin Hội'], 'is_active' => true]);
        Livewire::test(CreatePost::class)->fillForm(['name' => ['vi' => 'Tin Hội kiểm thử'], 'slug' => 'tin-hoi-kiem-thu', 'post_category_id' => $category->id, 'content' => ['vi' => '<p>Nội dung từ panel Hội</p>'], 'is_active' => true, 'published_at' => now()->subMinute()->toDateTimeString()])->call('create')->assertHasNoFormErrors();
        $post = Post::query()->sole();
        $this->get(route('news.show', $post->getSlug('vi')))->assertOk()->assertSee('Nội dung từ panel Hội');
        Livewire::test(EditPost::class, ['record' => $post->id])->fillForm(['is_active' => false])->call('save')->assertHasNoFormErrors();
        $this->get(route('news.show', $post->getSlug('vi')))->assertNotFound();
        Livewire::test(CreateIntro::class)->fillForm(['title' => 'Thông tin tổ chức kiểm thử', 'slug' => 'to-chuc-kiem-thu', 'content' => '<p>Thông tin do Hội quản lý</p>', 'sort_order' => 10, 'is_active' => true])->call('create')->assertHasNoFormErrors();
        $this->get(route('about.show', 'to-chuc-kiem-thu'))->assertOk()->assertSee('Thông tin do Hội quản lý');
    }

    public function test_scheduled_news_is_hidden_until_publication(): void
    {
        $category = PostCategory::query()->create(['name' => ['vi' => 'Tin Hội'], 'is_active' => true]);
        $post = app(PostService::class)->create(['name' => ['vi' => 'Tin tương lai'], 'slug' => 'tin-tuong-lai', 'post_category_id' => $category->id, 'is_active' => true, 'published_at' => now()->addDay()]);
        $this->get(route('news.index'))->assertDontSee('Tin tương lai');
        $this->get(route('news.show', $post->getSlug('vi')))->assertNotFound();
        $this->travel(2)->days();
        $this->get(route('news.show', $post->getSlug('vi')))->assertOk();
    }

    public function test_event_resource_creates_public_event_and_validates_dates(): void
    {
        $this->panel($this->staff());
        Livewire::test(CreateEvent::class)->fillForm(['title' => 'Gặp mặt doanh nghiệp', 'slug' => 'gap-mat-doanh-nghiep', 'starts_at' => now()->addDays(5)->toDateTimeString(), 'ends_at' => now()->addDays(4)->toDateTimeString()])->call('create')->assertHasFormErrors(['ends_at']);
        Livewire::test(CreateEvent::class)->fillForm(['title' => 'Gặp mặt doanh nghiệp', 'slug' => 'gap-mat-doanh-nghiep', 'starts_at' => now()->addDays(5)->toDateTimeString(), 'status' => 'published', 'visibility' => 'public', 'capacity' => 20])->call('create')->assertHasNoFormErrors();
        $event = Event::query()->sole();
        $this->get(route('events.show', $event->slug))->assertOk()->assertSee('Gửi đăng ký');
        $this->get('/hoi/su-kien/'.$event->id.'/edit')->assertOk()->assertSee('Người đăng ký tham dự');
    }

    public function test_event_registration_capacity_and_association_attendance_actions(): void
    {
        $event = $this->event();
        $payload = ['full_name' => 'Khách thử', 'phone' => '0900000000', 'guest_count' => 2];
        $this->post(route('events.register', $event->slug), $payload)->assertSessionHasNoErrors()->assertRedirect(route('events.show', $event->slug));
        $this->post(route('events.register', $event->slug), array_replace($payload, ['guest_count' => 0]))->assertSessionHasErrors('event');
        $this->assertDatabaseCount('event_registrations', 1);
        $registration = EventRegistration::query()->sole();
        $this->panel($this->staff());
        Livewire::test(RegistrationsRelationManager::class, ['ownerRecord' => $event, 'pageClass' => EditEvent::class])->assertCanSeeTableRecords([$registration])->callTableAction('check_in', $registration)->assertHasNoTableActionErrors();
        $this->assertSame('attended', $registration->fresh()->attendance_status);
        Livewire::test(RegistrationsRelationManager::class, ['ownerRecord' => $event, 'pageClass' => EditEvent::class])->callTableAction('cancel', $registration)->assertHasNoTableActionErrors();
        $this->assertSame('cancelled', $registration->fresh()->status);
        $this->post(route('events.register', $event->slug), $payload)->assertSessionHasNoErrors();
    }

    public function test_capacity_cannot_be_reduced_below_registered_people(): void
    {
        $event = $this->event();
        EventRegistration::query()->create(['event_id' => $event->id, 'full_name' => 'Khách', 'guest_count' => 2, 'status' => 'registered']);
        $this->panel($this->staff());
        Livewire::test(EditEvent::class, ['record' => $event->id])->fillForm(['capacity' => 2])->call('save')->assertHasFormErrors(['capacity']);
        $this->assertSame(3, $event->fresh()->capacity);
    }

    public function test_marketplace_search_and_pagination_keep_filters(): void
    {
        $user = User::factory()->create();
        $business = $this->business($user);
        $post = app(TradePostService::class)->submit($user, $this->data($business));
        $post->update(['status' => 'approved']);
        for ($i = 0; $i < 24; $i++) {
            $copy = $post->replicate();
            $copy->slug = 'tin-phan-trang-'.$i;
            $copy->save();
        }
        $response = $this->get(route('trade.index', ['type' => 'buy', 'q' => 'vận chuyển']));
        $response->assertOk()->assertViewHas('items', fn ($items) => $items->total() === 25 && $items->count() === 24);
        $this->get(route('trade.index', ['type' => 'buy', 'q' => 'vận chuyển', 'page' => 2]))->assertOk()->assertViewHas('items', fn ($items) => $items->count() === 1);
        $this->get(route('trade.index', ['q' => 'không có kết quả']))->assertOk()->assertDontSee($post->title);
    }

    public function test_event_can_set_closing_date_without_an_opening_date(): void
    {
        $this->panel($this->staff());
        Livewire::test(CreateEvent::class)->fillForm(['title' => 'Hạn đăng ký', 'slug' => 'han-dang-ky', 'starts_at' => now()->addDays(5)->toDateTimeString(), 'registration_closes_at' => now()->addDays(4)->toDateTimeString()])->call('create')->assertHasNoFormErrors();
    }

    public function test_events_enforce_publication_deletion_and_registration_windows(): void
    {
        foreach ([['status' => 'draft'], ['visibility' => 'private'], ['published_at' => now()->addDay()], ['deleted_at' => now()]] as $attributes) {
            $event = $this->event($attributes);
            $this->get(route('events.show', $event->slug))->assertNotFound();
            $this->post(route('events.register', $event->slug), ['full_name' => 'Khách thử', 'phone' => '0900000000'])->assertNotFound();
        }
        $event = $this->event(['starts_at' => now()->subHour()]);
        $this->post(route('events.register', $event->slug), ['full_name' => 'Khách thử', 'phone' => '0900000000'])->assertSessionHasErrors('event');
        $this->assertDatabaseCount('event_registrations', 0);
    }
}
