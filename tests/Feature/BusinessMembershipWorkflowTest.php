<?php

namespace Tests\Feature;

use App\Filament\Association\Resources\Memberships\Pages\ListMemberships;
use App\Filament\Association\Resources\Memberships\Pages\ViewMembership;
use App\Filament\Association\Resources\Staff\Pages\CreateStaff;
use App\Filament\Association\Resources\Staff\Pages\EditStaff;
use App\Filament\Member\Resources\MyBusinesses\Pages\EditMyBusiness;
use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Industry;
use App\Models\User;
use App\Notifications\MembershipAccountInvitation;
use App\Services\BusinessApprovalService;
use App\Services\BusinessProfileReviewService;
use Database\Seeders\AssociationRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BusinessMembershipWorkflowTest extends TestCase
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

    public function test_guest_submits_without_creating_an_account_and_tracks_a_private_application(): void
    {
        $this->get('/register')->assertRedirect('/dang-ky-hoi-vien');
        $this->get(route('membership.create'))->assertOk()->assertDontSee('name="password"', false);
        $payload = $this->payload();
        unset($payload['password'], $payload['password_confirmation']);
        $response = $this->post(route('membership.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $business = Business::query()->sole();
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
        $this->assertNull($business->submitted_by_user_id);
        $this->assertSame('pending', $business->status);
        $this->assertSame($payload['login_email'], $business->application_email);
        $this->assertTrue($business->hasMedia('signed_membership_application'));
        $this->get($response->headers->get('Location'))->assertOk()->assertSee($business->application_code)->assertSee('Chờ Văn phòng kiểm tra')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->get(route('membership.track', $business))->assertForbidden();
        $this->get(URL::temporarySignedRoute('membership.track', now()->subMinute(), ['business' => $business->id]))->assertForbidden();
        $this->get(URL::temporarySignedRoute('membership.track.document', now()->addMinute(), ['business' => $business->id]))->assertOk();
    }

    public function test_invalid_and_duplicate_applications_do_not_create_accounts_or_duplicate_businesses(): void
    {
        $payload = $this->payload();
        unset($payload['membership_application']);
        $this->post(route('membership.store'), $payload)->assertSessionHasErrors('membership_application');
        $this->assertDatabaseCount('businesses', 0);
        $this->post(route('membership.store'), $this->payload())->assertSessionHasNoErrors();
        $this->post(route('membership.store'), $this->payload())->assertSessionHasErrors('tax_code');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('businesses', 1);
    }

    public function test_multiple_groups_keep_the_primary_group_when_reopening_and_correcting_an_application(): void
    {
        $payload = $this->payload();
        $primary = Industry::query()->create(['name' => 'Khối chính', 'slug' => 'primary-group', 'is_active' => true, 'is_member_group' => true]);
        $payload['industry_ids'] = [$primary->id, $payload['industry_ids'][0]];
        $selected = array_map('strval', $payload['industry_ids']);

        $this->post(route('membership.store'), $payload)->assertSessionHasNoErrors();
        $business = Business::query()->where('tax_code', $payload['tax_code'])->firstOrFail();
        $this->assertCount(2, $business->industries);
        $this->assertSame($primary->id, $business->industries->firstWhere('pivot.is_primary', true)->id);

        $business->update(['status' => 'changes_requested']);
        $editUrl = URL::temporarySignedRoute('membership.track.edit', now()->addDay(), ['business' => $business->id]);
        $this->get($editUrl)->assertOk()->assertViewHas('selectedIndustryIds', $selected);

        // A failed correction must retain the new selection order, not the stored primary.
        $payload['industry_ids'] = array_reverse($payload['industry_ids']);
        $payload['name'] = '';
        unset($payload['membership_application']);
        $this->from($editUrl)->patch(URL::temporarySignedRoute('membership.track.update', now()->addDay(), ['business' => $business->id]), $payload)
            ->assertSessionHasErrors('name')->assertRedirect($editUrl);
        $this->get($editUrl)->assertOk()->assertViewHas('selectedIndustryIds', array_reverse($selected));

        $payload['name'] = $business->name;
        $this->patch(URL::temporarySignedRoute('membership.track.update', now()->addDay(), ['business' => $business->id]), $payload)->assertSessionHasNoErrors();
        $this->assertSame($payload['industry_ids'][0], $business->fresh()->industries->firstWhere('pivot.is_primary', true)->id);
    }

    public function test_membership_requires_one_to_five_groups_and_retains_guest_selections_after_validation(): void
    {
        $payload = $this->payload();
        $groupIds = collect(range(1, 6))->map(fn (int $index) => Industry::query()->create([
            'name' => 'Khối '.$index, 'slug' => 'group-'.$index, 'is_active' => true, 'is_member_group' => true,
        ])->id)->reverse()->values()->all();

        foreach ([[], $groupIds] as $invalidIds) {
            $this->post(route('membership.store'), [...$payload, 'industry_ids' => $invalidIds])
                ->assertSessionHasErrors('industry_ids');
        }
        $this->assertDatabaseCount('businesses', 0);
        $this->assertDatabaseCount('users', 0);

        $payload['industry_ids'] = array_slice($groupIds, 0, 5);
        $this->from(route('membership.create'))->post(route('membership.store'), [...$payload, 'name' => ''])
            ->assertSessionHasErrors('name')->assertRedirect(route('membership.create'));
        $this->get(route('membership.create'))->assertOk()
            ->assertViewHas('selectedIndustryIds', array_map('strval', $payload['industry_ids']));

        $this->post(route('membership.store'), $payload)->assertSessionHasNoErrors();
        $business = Business::query()->where('tax_code', $payload['tax_code'])->firstOrFail();
        $this->assertCount(5, $business->industries);
        $this->assertSame($payload['industry_ids'][0], $business->industries->firstWhere('pivot.is_primary', true)->id);
    }

    public function test_only_final_ratification_publishes_membership_and_provisions_the_account_once(): void
    {
        $this->post(route('membership.store'), $this->payload())->assertSessionHasNoErrors();
        $business = Business::query()->sole();
        $office = $this->staff('association_manager');
        $chapter = $this->staff('chapter_manager');
        $chapter->managedChapters()->attach($business->business_chapter_id);
        $head = $this->staff('membership_head');
        $service = app(BusinessApprovalService::class);
        $service->approveForChapter($business, $office, $business->business_chapter_id);
        $service->receive($business, $chapter);
        $this->assertSame('board_pending', $business->fresh()->status);
        $this->assertNull($business->fresh()->submitted_by_user_id);
        $this->assertNull($business->fresh()->membership_code);
        $this->get(route('directory.index'))->assertDontSee($business->name);
        $service->ratify($business, $head);
        $business->refresh();
        $this->assertSame('approved', $business->status);
        $this->assertSame($head->id, $business->approved_by);
        $this->assertSame($chapter->id, $business->chapter_reviewed_by);
        $this->assertTrue($business->submittedBy->hasApprovedBusiness());
        Notification::assertSentTo($business->submittedBy, MembershipAccountInvitation::class);
        Notification::assertSentToTimes($business->submittedBy, MembershipAccountInvitation::class, 1);
        $this->get(route('directory.index'))->assertSee($business->name);
        $this->assertCount(4, $business->statusHistories);
        $this->expectException(ValidationException::class);
        $service->ratify($business, $head);
    }

    public function test_chapter_cannot_approve_for_association_or_receive_another_chapters_business(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        try {
            app(BusinessApprovalService::class)->approveForChapter($business, $chapterUser, $business->business_chapter_id);
            $this->fail('Chapter bypassed association review.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        app(BusinessApprovalService::class)->approveForChapter($business, $association, $business->business_chapter_id);
        $other = $this->staff('chapter_manager');
        $other->managedChapters()->attach($this->chapter()->id);
        $this->expectException(HttpException::class);
        app(BusinessApprovalService::class)->receive($business, $other);
    }

    public function test_review_requires_the_signed_document_and_does_not_skip_steps(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        try {
            app(BusinessApprovalService::class)->receive($business, $chapterUser);
            $this->fail('Chapter received an unreviewed application.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
        $business->clearMediaCollection('signed_membership_application');
        $this->expectException(ValidationException::class);
        app(BusinessApprovalService::class)->approveForChapter($business, $association, $business->business_chapter_id);
    }

    public function test_chapter_returned_application_restarts_at_association_review(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $service = app(BusinessApprovalService::class);
        $service->approveForChapter($business, $association, $business->business_chapter_id);
        $service->requestChanges($business, $chapterUser, 'Bổ sung địa chỉ trong đơn.');
        $business->refresh();
        $this->assertSame('changes_requested', $business->status);
        $this->assertNull($business->association_approved_at);
        $payload = $this->payload();
        $payload['tax_code'] = $business->tax_code;
        $payload['business_chapter_id'] = $business->business_chapter_id;
        unset($payload['membership_application']);
        $this->actingAs($business->submittedBy)->get(route('account.dashboard'))->assertSee('Bổ sung địa chỉ trong đơn.');
        $this->patch(URL::temporarySignedRoute('membership.track.update', now()->addDay(), ['business' => $business->id]), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('pending', $business->fresh()->status);
        $this->expectException(ValidationException::class);
        $service->receive($business, $chapterUser);
    }

    public function test_panel_scopes_records_documents_actions_and_direct_urls(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        app(BusinessApprovalService::class)->approveForChapter($business, $association, $business->business_chapter_id);
        $foreign = Business::query()->create(['name' => 'Doanh nghiệp chi hội khác', 'slug' => 'foreign', 'status' => 'chapter_pending', 'business_chapter_id' => $this->chapter()->id]);
        $this->actingAs($chapterUser, 'admin')->get('/hoi')->assertOk();
        $this->get('/hoi/hoi-vien')->assertOk()->assertSee($business->name)->assertDontSee($foreign->name);
        $this->get('/hoi/hoi-vien/'.$business->id)->assertOk()->assertSee('Chi hội đăng ký');
        $this->get('/hoi/hoi-vien/'.$foreign->id)->assertNotFound();
        $this->get(route('business.membership-application.download', $business))->assertOk();
        $this->get(route('business.membership-application.download', $foreign))->assertForbidden();
        $this->get('/hoi/can-bo')->assertForbidden();
        $this->get('/hoi/chi-hoi/create')->assertForbidden();
        $this->get('/admin')->assertForbidden();
        $this->get('/admin/businesses')->assertForbidden();

        Filament::setCurrentPanel(Filament::getPanel('association'));
        Livewire::test(ListMemberships::class)->assertCanSeeTableRecords([$business])->assertCanNotSeeTableRecords([$foreign]);
        Livewire::test(ViewMembership::class, ['record' => $business->id])->assertActionHidden('approve_association')->assertActionVisible('receive_chapter');
        $this->actingAs($association, 'admin')->get('/hoi/hoi-vien/'.$foreign->id)->assertOk();
        $this->get('/hoi/chi-hoi/create')->assertOk();
        $this->get('/hoi/can-bo/create')->assertOk();
    }

    public function test_filament_actions_follow_three_roles_and_refresh_each_stage_immediately(): void
    {
        [$business, $office, $chapter] = $this->workflow();
        $head = $this->staff('membership_head');
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->actingAs($office, 'admin');
        Livewire::test(ViewMembership::class, ['record' => $business->id])
            ->callAction('approve_association', ['business_chapter_id' => $business->business_chapter_id])->assertHasNoActionErrors()
            ->assertSet('record.status', 'chapter_pending')->assertActionHidden('approve_association')->assertActionHidden('ratify_membership');
        $this->actingAs($chapter, 'admin');
        Livewire::test(ViewMembership::class, ['record' => $business->id])->callAction('receive_chapter')->assertHasNoActionErrors()
            ->assertSet('record.status', 'board_pending')->assertActionHidden('receive_chapter')->assertActionHidden('ratify_membership');
        $this->actingAs($head, 'admin');
        Livewire::test(ViewMembership::class, ['record' => $business->id])->assertActionVisible('ratify_membership')
            ->callAction('ratify_membership')->assertHasNoActionErrors()->assertSet('record.status', 'approved')->assertActionHidden('ratify_membership');
    }

    public function test_panel_explains_missing_documents_and_refreshes_requested_changes_immediately(): void
    {
        [$business, $association] = $this->workflow();
        $business->clearMediaCollection('signed_membership_application');
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->actingAs($association, 'admin');

        Livewire::test(ViewMembership::class, ['record' => $business->id])
            ->assertActionDisabled('approve_association')->assertActionHidden('download_application')
            ->assertSee('Hồ sơ đang thiếu đơn đã ký, đóng dấu.')
            ->callAction('request_changes', ['reason' => 'Bổ sung đơn đã ký và đóng dấu.'])->assertHasNoActionErrors()
            ->assertSet('record.status', 'changes_requested')->assertActionHidden('approve_association')->assertActionHidden('request_changes')
            ->assertSee('Nội dung cần bổ sung')->assertSee('Bổ sung đơn đã ký và đóng dấu.');
        $this->assertSame('changes_requested', $business->fresh()->status);
    }

    public function test_staff_form_assigns_scoped_roles_and_preserves_password_on_edit(): void
    {
        $association = $this->staff('association_manager');
        $chapter = $this->chapter();
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->actingAs($association, 'admin');
        Livewire::test(CreateStaff::class)->fillForm([
            'name' => 'Cán bộ kiểm thử', 'email' => 'can-bo@example.test', 'password' => 'test-password-123',
            'is_active' => true, 'staff_roles' => ['chapter_manager'], 'managedChapters' => [$chapter->id],
        ])->call('create')->assertHasNoFormErrors();
        $staff = User::query()->where('email', 'can-bo@example.test')->firstOrFail();
        $this->assertTrue($staff->canReceiveChapter());
        $this->assertFalse($staff->canReviewAssociation());
        $this->assertFalse($staff->canAccessPanel(Filament::getPanel('admin')));
        $password = $staff->password;
        Livewire::test(EditStaff::class, ['record' => $staff->id])->fillForm(['name' => 'Tên đã sửa', 'password' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertSame($password, $staff->fresh()->password);
        $this->assertSame('Tên đã sửa', $staff->fresh()->name);
        $this->get('/hoi/can-bo/'.$association->id.'/edit')->assertForbidden();
    }

    public function test_guests_unrelated_users_and_disabled_staff_cannot_download_private_documents(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $url = route('business.membership-application.download', $business);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(), 'admin')->get($url)->assertForbidden();
        $association->update(['is_active' => false]);
        $this->actingAs($association, 'admin')->get($url)->assertForbidden();
        $this->get('/hoi')->assertForbidden();
    }

    public function test_legacy_pending_applicants_gain_tracking_access_without_unblocking_staff_or_rejected_users(): void
    {
        $applicant = User::factory()->create(['approval_status' => 'pending', 'is_active' => false]);
        $rejected = User::factory()->create(['approval_status' => 'rejected', 'is_active' => false]);
        $staff = $this->staff('chapter_manager');
        $staff->update(['approval_status' => 'pending', 'is_active' => false]);
        $migration = require database_path('migrations/2026_09_05_000100_add_direct_access_for_pending_applicants.php');
        $migration->up();
        $this->assertTrue($applicant->fresh()->hasApprovedAccount());
        $this->assertFalse($applicant->fresh()->hasApprovedBusiness());
        $this->assertFalse($rejected->fresh()->hasApprovedAccount());
        $this->assertFalse($staff->fresh()->hasApprovedAccount());
    }

    public function test_guest_cannot_inject_membership_status_account_or_staff_permissions(): void
    {
        $this->post(route('membership.store'), [
            ...$this->payload(), 'status' => 'approved', 'membership_code' => 'INJECTED',
            'roles' => ['membership_head'], 'approval_status' => 'approved', 'submitted_by_user_id' => 9999,
        ])->assertSessionHasNoErrors();
        $business = Business::query()->sole();
        $this->assertSame('pending', $business->status);
        $this->assertNull($business->membership_code);
        $this->assertNull($business->submitted_by_user_id);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_two_businesses_have_distinct_memberships_for_one_representative(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $second = Business::query()->create([
            'name' => 'Doanh nghiệp thứ hai', 'slug' => 'second-business', 'status' => 'pending',
            'submitted_by_user_id' => $business->submitted_by_user_id, 'business_chapter_id' => $business->business_chapter_id,
        ]);
        $second->industries()->attach($this->payload()['industry_ids']);
        $second->addMedia(UploadedFile::fake()->create('don-2.pdf', 20, 'application/pdf'))->toMediaCollection('signed_membership_application');
        foreach ([$business, $second] as $record) {
            app(BusinessApprovalService::class)->approveForChapter($record, $association, $record->business_chapter_id);
            app(BusinessApprovalService::class)->receive($record, $chapterUser);
            app(BusinessApprovalService::class)->ratify($record, $this->staff('membership_head'));
        }
        $this->assertNotSame($business->fresh()->membership_code, $second->fresh()->membership_code);
        $this->assertDatabaseCount('members', 1);
        $this->assertSame(2, Business::query()->representedBy($business->submittedBy)->where('status', 'approved')->count());
    }

    public function test_profile_updates_preserve_the_published_membership_until_reviewed(): void
    {
        [$business, $office, $chapter] = $this->workflow();
        $payload = $this->payload();
        $business->update(Arr::only($payload, ['business_type', 'business_size', 'phone', 'email', 'address', 'province', 'summary']));
        app(BusinessApprovalService::class)->approveForChapter($business, $office, $business->business_chapter_id);
        app(BusinessApprovalService::class)->receive($business, $chapter);
        app(BusinessApprovalService::class)->ratify($business, $this->staff('membership_head'));
        $business->refresh();
        $code = $business->membership_code;
        $original = $business->summary;
        $industries = $business->industries->modelKeys();
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs($business->submittedBy, 'web');
        Livewire::test(EditMyBusiness::class, ['record' => $business->id])
            ->fillForm(['summary' => 'Nội dung cập nhật cần xét duyệt.'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('approved', $business->fresh()->status);
        $this->assertSame($code, $business->fresh()->membership_code);
        $this->assertSame($original, $business->fresh()->summary);
        $this->assertSame($industries, $business->fresh()->industries->modelKeys());
        $this->get(route('directory.index'))->assertSee($original)->assertDontSee('Nội dung cập nhật cần xét duyệt.');
        app(BusinessProfileReviewService::class)->review($business->fresh(), $office, true);
        $this->assertSame('Nội dung cập nhật cần xét duyệt.', $business->fresh()->summary);
        $this->assertNull($business->fresh()->pending_profile);
        $this->assertSame('approved', $business->fresh()->status);
    }

    public function test_staff_forms_reject_elevated_roles_and_require_chapter_assignments(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->actingAs($this->staff('association_manager'), 'admin');
        Livewire::test(CreateStaff::class)->fillForm([
            'name' => 'Cán bộ', 'email' => 'invalid@example.test', 'password' => 'password123', 'is_active' => true,
            'staff_roles' => ['super_admin'],
        ])->call('create')->assertHasFormErrors(['staff_roles.0']);
        Livewire::test(CreateStaff::class)->fillForm([
            'name' => 'Cán bộ', 'email' => 'invalid@example.test', 'password' => 'password123', 'is_active' => true,
            'staff_roles' => ['chapter_manager'], 'managedChapters' => [],
        ])->call('create')->assertHasFormErrors(['managedChapters']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.test']);
    }

    private function payload(): array
    {
        $industry = Industry::query()->firstOrCreate(['slug' => 'test-group'], ['name' => 'Nhóm kiểm thử', 'is_active' => true, 'is_member_group' => true]);

        return [
            'name' => 'Công ty hồ sơ trực tiếp', 'tax_code' => 'DNT-DIRECT-001', 'business_type' => 'limited', 'business_size' => 'small',
            'business_chapter_id' => $this->chapter()->id, 'representative_name' => 'Đại diện kiểm thử', 'industry_ids' => [$industry->id], 'phone' => '0900000000', 'email' => 'company@example.test',
            'login_email' => 'representative@example.test', 'password' => 'membership-test-123', 'password_confirmation' => 'membership-test-123',
            'address' => 'Địa chỉ kiểm thử', 'province' => 'Bắc Ninh', 'summary' => 'Hồ sơ doanh nghiệp kiểm thử.',
            'membership_application' => UploadedFile::fake()->create('don-da-ky.pdf', 20, 'application/pdf'), 'confirm_information' => '1',
        ];
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function chapter(): BusinessChapter
    {
        $slug = 'chapter-'.Str::random(8);

        return BusinessChapter::query()->create(['name' => 'Chi hội '.$slug, 'slug' => $slug, 'is_active' => true]);
    }

    private function workflow(): array
    {
        $association = $this->staff('association_manager');
        $chapterUser = $this->staff('chapter_manager');
        $chapter = $this->chapter();
        $chapterUser->managedChapters()->attach($chapter->id);
        $business = Business::query()->create([
            'name' => 'Doanh nghiệp kiểm thử quy trình', 'slug' => 'workflow-business', 'tax_code' => 'WORKFLOW-001', 'status' => 'pending',
            'submitted_by_user_id' => User::factory()->create()->id, 'representative_name' => 'Người đại diện', 'business_chapter_id' => $chapter->id,
        ]);
        $business->industries()->attach($this->payload()['industry_ids']);
        $business->addMedia(UploadedFile::fake()->create('don-da-ky.pdf', 20, 'application/pdf'))->toMediaCollection('signed_membership_application');

        return [$business, $association, $chapterUser];
    }
}
