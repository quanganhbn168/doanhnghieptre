<?php

namespace Tests\Feature;

use App\Filament\Association\Resources\MemberPosts\Pages\ViewMemberPost;
use App\Filament\Association\Widgets\ContentStats;
use App\Filament\Member\Pages\EditProfile;
use App\Filament\Member\Resources\MyPosts\Pages\CreateMyPost;
use App\Filament\Member\Resources\MyPosts\Pages\EditMyPost;
use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Industry;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Notifications\MembershipAccountInvitation;
use App\Notifications\MembershipApplicationUpdated;
use App\Services\BusinessApprovalService;
use App\Services\BusinessProfileReviewService;
use App\Services\MemberPostService;
use Database\Seeders\AssociationRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ThreeStageMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public_media');
        $this->seed(AssociationRoleSeeder::class);
    }

    public function test_office_and_chapter_cannot_ratify_and_head_cannot_skip_previous_stages(): void
    {
        [$business, $office, $chapter, $head] = $this->workflow();
        foreach ([$office, $chapter] as $actor) {
            try {
                app(BusinessApprovalService::class)->ratify($business, $actor);
                $this->fail('Wrong role ratified membership.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
        $this->expectException(ValidationException::class);
        app(BusinessApprovalService::class)->ratify($business, $head);
    }

    public function test_head_can_return_for_correction_but_final_rejection_cannot_be_resubmitted(): void
    {
        [$business, $office, $chapter, $head] = $this->workflow();
        $service = app(BusinessApprovalService::class);
        $service->approveForChapter($business, $office, $business->business_chapter_id);
        $service->receive($business, $chapter);
        $service->requestChanges($business, $head, 'Bổ sung xác nhận của doanh nghiệp.');
        $business->refresh();
        $this->assertSame('changes_requested', $business->status);
        $this->assertNull($business->chapter_reviewed_at);
        $edit = URL::temporarySignedRoute('membership.track.edit', now()->addDay(), ['business' => $business->id]);
        $this->get($edit)->assertOk()->assertDontSee('name="password"', false);
        $business->update(['status' => 'pending']);
        $service->reject($business, $office, 'Hồ sơ không đủ điều kiện kết nạp.');
        $this->assertSame('rejected', $business->fresh()->status);
        $this->get($edit)->assertForbidden();
        $this->get(URL::temporarySignedRoute('membership.track', now()->addDay(), ['business' => $business->id]))
            ->assertOk()->assertSee('Hồ sơ không đủ điều kiện kết nạp.')->assertDontSee('Bổ sung và gửi lại hồ sơ');
        $this->assertNull($business->fresh()->submitted_by_user_id);
    }

    public function test_existing_account_is_reused_without_resetting_its_password_or_issuing_another_account(): void
    {
        [$business, $office, $chapter, $head] = $this->workflow();
        $owner = User::factory()->create(['email' => $business->application_email, 'password' => 'original-password-123']);
        $service = app(BusinessApprovalService::class);
        $service->approveForChapter($business, $office, $business->business_chapter_id);
        $service->receive($business, $chapter);
        $service->ratify($business, $head);
        $this->assertSame($owner->id, $business->fresh()->submitted_by_user_id);
        $this->assertTrue(Hash::check('original-password-123', $owner->fresh()->password));
        Notification::assertNotSentTo($owner, MembershipAccountInvitation::class);
    }

    public function test_activation_uses_the_existing_one_time_password_reset_flow(): void
    {
        $user = User::factory()->create();
        $mail = (new MembershipAccountInvitation)->toMail($user);
        parse_str(parse_url($mail->actionUrl, PHP_URL_QUERY), $query);
        $token = basename(parse_url($mail->actionUrl, PHP_URL_PATH));
        $data = ['email' => $query['email'], 'token' => $token, 'password' => 'new-member-password-123', 'password_confirmation' => 'new-member-password-123'];
        $this->post('/reset-password', $data)->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_member_can_update_personal_profile_without_changing_membership_or_password(): void
    {
        $user = User::factory()->create(['password' => 'existing-password-123']);
        $business = Business::query()->create(['name' => 'Doanh nghiệp hồ sơ cá nhân', 'slug' => 'personal-profile', 'status' => 'approved', 'submitted_by_user_id' => $user->id]);
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs($user, 'web')->get('/thanh-vien/profile')->assertOk();
        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Người đại diện đã cập nhật', 'phone' => '0901234567'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Người đại diện đã cập nhật', $user->fresh()->name);
        $this->assertSame('0901234567', $user->fresh()->phone);
        $this->assertTrue(Hash::check('existing-password-123', $user->fresh()->password));
        $this->assertSame('approved', $business->fresh()->status);
    }

    public function test_tracking_link_recovery_does_not_expose_whether_a_record_exists(): void
    {
        [$business] = $this->workflow();
        $this->from(route('membership.lookup'))->post(route('membership.lookup.send'), ['application_code' => $business->application_code, 'email' => $business->application_email])
            ->assertSessionHas('success')->assertRedirect(route('membership.lookup'));
        Notification::assertSentOnDemand(MembershipApplicationUpdated::class);
        $message = session('success');
        Notification::fake();
        $this->post(route('membership.lookup.send'), ['application_code' => 'DOES-NOT-EXIST', 'email' => 'nobody@example.test'])->assertSessionHas('success', $message);
        Notification::assertNothingSent();
    }

    public function test_profile_corrections_must_be_resubmitted_and_stale_revisions_cannot_be_published(): void
    {
        [$business, $office] = $this->workflow();
        $owner = User::factory()->create();
        $business->forceFill(['status' => 'approved', 'submitted_by_user_id' => $owner->id])->save();
        $data = [
            'name' => 'Tên doanh nghiệp đề nghị sửa', 'tax_code' => 'PROFILE-REVISION-001',
            'business_type' => 'limited', 'business_size' => 'small',
            'representative_name' => 'Người đại diện', 'phone' => '0901234567', 'email' => 'business@example.test',
            'address' => 'Bắc Ninh', 'province' => 'Bắc Ninh', 'summary' => 'Giới thiệu mới.',
            'industry_ids' => $business->industries->modelKeys(),
        ];
        $service = app(BusinessProfileReviewService::class);
        $service->submit($business, $owner, $data);
        $firstRevision = $business->fresh();
        $service->review($firstRevision, $office, false, 'Kiểm tra lại tên pháp lý.');
        try {
            $service->review($business->fresh(), $office, true);
            $this->fail('A returned revision was published before resubmission.');
        } catch (ValidationException) {
            $this->assertNotSame($data['name'], $business->fresh()->name);
        }
        $data['name'] = 'Tên doanh nghiệp đã bổ sung';
        $service->submit($business, $owner, $data);
        $this->assertNull($business->fresh()->profile_review_note);
        try {
            $service->review($firstRevision, $office, true);
            $this->fail('A stale revision was approved.');
        } catch (ValidationException) {
            $this->assertNotSame($data['name'], $business->fresh()->name);
        }
        $service->review($business->fresh(), $office, true);
        $this->assertSame($data['name'], $business->fresh()->name);
        $this->assertSame('approved', $business->fresh()->status);
    }

    public function test_communications_staff_have_content_access_without_membership_documents_or_staff_permissions(): void
    {
        [$business] = $this->workflow();
        $communications = $this->staff('communications_manager');
        $this->actingAs($communications, 'admin')->get('/hoi')->assertOk();
        Livewire::test(ContentStats::class)->assertSee('Bài hội viên chờ duyệt');
        $this->get('/hoi/bai-viet-hoi-vien')->assertOk();
        $this->get('/hoi/tin-tuc')->assertOk();
        $this->get('/hoi/cho-doanh-nghiep')->assertOk();
        foreach (['/hoi/hoi-vien', '/hoi/can-bo', '/hoi/khoi-nganh-nghe', '/admin'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get(route('business.membership-application.download', $business))->assertForbidden();
    }

    public function test_member_posts_require_review_and_edits_return_to_pending_without_leaking_unsafe_html(): void
    {
        $owner = User::factory()->create();
        $business = Business::query()->create(['name' => 'Doanh nghiệp có bài viết', 'slug' => 'member-post-business', 'status' => 'approved', 'submitted_by_user_id' => $owner->id]);
        $category = PostCategory::query()->create(['name' => ['vi' => 'Tin doanh nghiệp'], 'is_active' => true]);
        $communications = $this->staff('communications_manager');
        $data = [
            'business_id' => $business->id, 'post_category_id' => $category->id,
            'title' => 'Dịch vụ của doanh nghiệp', 'summary_text' => 'Giới thiệu dịch vụ hội viên.',
            'body' => '<h2>Giải pháp</h2><p>Nội dung giới thiệu.</p><script>alert(1)</script>',
            'image' => UploadedFile::fake()->image('dich-vu.png'),
        ];
        $this->actingAs($owner, 'web');
        Filament::setCurrentPanel(Filament::getPanel('member'));
        Livewire::test(CreateMyPost::class)->fillForm($data)->call('create')->assertHasNoFormErrors();
        $post = Post::query()->sole();
        $this->assertSame('pending', $post->review_status);
        $this->assertFalse($post->is_active);
        $this->assertTrue($post->hasMedia('post_image'));
        $this->assertStringNotContainsString('<script', $post->getTranslation('content', 'vi'));
        $this->get(route('news.index'))->assertDontSee($data['title']);
        $url = route('news.show', $post->slug);
        $this->get($url)->assertNotFound();
        $this->actingAs($communications, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('association'));
        Livewire::test(ViewMemberPost::class, ['record' => $post->id])->callAction('reject', ['review_note' => 'Bổ sung thông tin sản phẩm.'])->assertHasNoActionErrors();
        $this->assertSame('rejected', $post->fresh()->review_status);
        $this->actingAs($owner, 'web');
        Filament::setCurrentPanel(Filament::getPanel('member'));
        Livewire::test(EditMyPost::class, ['record' => $post->id])->fillForm(['body' => '<p>Thông tin sản phẩm đã bổ sung.</p>'])->call('save')->assertHasNoFormErrors();
        $this->actingAs($communications, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('association'));
        Livewire::test(ViewMemberPost::class, ['record' => $post->id])->callAction('approve')->assertHasNoActionErrors()->assertActionHidden('approve');
        $this->get($url)->assertOk()->assertSee('Thông tin sản phẩm đã bổ sung.');
        unset($data['image']);
        app(MemberPostService::class)->submit($owner, $data, $post);
        $this->get($url)->assertNotFound();
    }

    public function test_member_cannot_edit_another_businesses_post_or_publish_directly(): void
    {
        $owner = User::factory()->create();
        $business = Business::query()->create(['name' => 'Công ty chủ sở hữu', 'slug' => 'content-owner', 'status' => 'approved', 'submitted_by_user_id' => $owner->id]);
        $category = PostCategory::query()->create(['name' => ['vi' => 'Tin doanh nghiệp'], 'is_active' => true]);
        $data = ['business_id' => $business->id, 'post_category_id' => $category->id, 'title' => 'Bài viết cần duyệt', 'summary_text' => 'Mô tả.', 'body' => '<p>Nội dung.</p>', 'is_active' => true, 'review_status' => 'approved'];
        $post = app(MemberPostService::class)->submit($owner, $data);
        $this->assertSame('pending', $post->review_status);
        $outsider = User::factory()->create();
        Business::query()->create(['name' => 'Công ty khác', 'slug' => 'content-other', 'status' => 'approved', 'submitted_by_user_id' => $outsider->id]);
        $this->actingAs($outsider, 'web')->get('/thanh-vien/bai-viet/'.$post->id.'/edit')->assertNotFound();
        $this->expectException(HttpException::class);
        app(MemberPostService::class)->moderate($post, $owner, 'approve');
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function workflow(): array
    {
        $office = $this->staff('association_manager');
        $chapterUser = $this->staff('chapter_manager');
        $head = $this->staff('membership_head');
        $chapter = BusinessChapter::query()->create(['name' => 'Chi hội kiểm thử', 'slug' => 'test-chapter', 'is_active' => true]);
        $chapterUser->managedChapters()->attach($chapter);
        $business = Business::query()->create(['name' => 'Hồ sơ ba cấp', 'slug' => 'three-stage', 'status' => 'pending', 'business_chapter_id' => $chapter->id, 'representative_name' => 'Đại diện kiểm thử']);
        $business->forceFill(['application_email' => 'applicant@example.test', 'application_code' => 'HS-TEST-001'])->save();
        $business->industries()->attach(Industry::query()->create(['name' => 'Khối kiểm thử', 'slug' => 'test-group', 'is_member_group' => true, 'is_active' => true]));
        $business->addMedia(UploadedFile::fake()->create('don.pdf', 20, 'application/pdf'))->toMediaCollection('signed_membership_application');

        return [$business, $office, $chapterUser, $head];
    }
}
