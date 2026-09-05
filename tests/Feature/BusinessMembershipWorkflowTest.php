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
use App\Services\BusinessApprovalService;
use Database\Seeders\AssociationRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('local');
        Storage::fake('public_media');
        $this->seed(AssociationRoleSeeder::class);
    }

    public function test_guest_submits_one_form_and_can_track_without_account_approval(): void
    {
        $this->get('/register')->assertRedirect('/dang-ky-hoi-vien');
        $this->post('/register', ['email' => 'legacy@example.test'])->assertRedirect('/dang-ky-hoi-vien');
        $this->assertDatabaseCount('users', 0);
        $this->get(route('membership.create'))->assertOk()->assertSee('Đăng ký hội viên')->assertSee('Đơn gia nhập Hội');
        $payload = $this->payload();
        $this->post(route('membership.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('account.dashboard'));
        $user = User::query()->where('email', $payload['login_email'])->firstOrFail();
        $business = Business::query()->where('tax_code', $payload['tax_code'])->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasApprovedAccount());
        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertSame($user->id, $business->submitted_by_user_id);
        $this->assertSame('pending', $business->status);
        $this->assertNull($business->membership_code);
        $this->assertTrue($business->hasMedia('signed_membership_application'));
        $this->assertFalse($user->hasApprovedBusiness());
        $this->get(route('account.dashboard'))->assertOk()->assertSee('Chờ Hội duyệt');
        $this->get('/thanh-vien')->assertForbidden();
        $this->get(route('business.membership-application.download', $business))->assertOk();
    }

    public function test_invalid_or_duplicate_application_does_not_create_an_account_or_business(): void
    {
        $payload = $this->payload();
        unset($payload['membership_application']);
        $this->post(route('membership.store'), $payload)->assertSessionHasErrors('membership_application');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('businesses', 0);
        $existing = User::factory()->create(['email' => $payload['login_email']]);
        $this->post(route('membership.store'), $this->payload())->assertSessionHasErrors('login_email');
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existing->name, $existing->fresh()->name);
        $this->assertDatabaseCount('businesses', 0);
    }

    public function test_business_is_only_a_member_after_association_approval_and_assigned_chapter_receipt(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $service = app(BusinessApprovalService::class);
        $service->approveForChapter($business, $association, $business->business_chapter_id);
        $business->refresh();
        $this->assertSame('chapter_pending', $business->status);
        $this->assertNotNull($business->association_approved_at);
        $this->assertNull($business->approved_at);
        $this->assertNull($business->membership_code);
        $this->assertFalse($business->submittedBy->hasApprovedBusiness());
        $this->get(route('directory.index'))->assertDontSee($business->name);
        $service->receive($business, $chapterUser);
        $business->refresh();
        $this->assertSame('approved', $business->status);
        $this->assertSame($association->id, $business->association_approved_by);
        $this->assertSame($chapterUser->id, $business->approved_by);
        $this->assertSame('DNTBN-DN-'.str_pad((string) $business->id, 6, '0', STR_PAD_LEFT), $business->membership_code);
        $this->assertTrue($business->submittedBy->hasApprovedBusiness());
        $this->assertCount(2, $business->statusHistories);
        $this->actingAs($business->submittedBy)->get('/thanh-vien')->assertOk();
        $this->get(route('directory.index', ['q' => $business->name]))->assertSee($business->name);
        $this->expectException(ValidationException::class);
        $service->receive($business, $chapterUser);
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
        $this->assertSame('rejected', $business->status);
        $this->assertNull($business->association_approved_at);
        $payload = $this->payload();
        $payload['tax_code'] = $business->tax_code;
        $payload['business_chapter_id'] = $business->business_chapter_id;
        unset($payload['membership_application']);
        $this->actingAs($business->submittedBy)->get(route('account.dashboard'))->assertSee('Bổ sung địa chỉ trong đơn.');
        $this->patch(route('account.businesses.update', $business), $payload)->assertSessionHasNoErrors()->assertRedirect(route('account.dashboard'));
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
        $this->get('/hoi/hoi-vien/'.$business->id)->assertOk()->assertSee('Chi hội tiếp nhận');
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

    public function test_filament_actions_complete_the_two_stage_workflow(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->actingAs($association, 'admin');
        Livewire::test(ViewMembership::class, ['record' => $business->id])
            ->callAction('approve_association', ['business_chapter_id' => $business->business_chapter_id])->assertHasNoActionErrors();
        $this->assertSame('chapter_pending', $business->fresh()->status);
        $this->actingAs($chapterUser, 'admin');
        Livewire::test(ViewMembership::class, ['record' => $business->id])->callAction('receive_chapter')->assertHasNoActionErrors();
        $this->assertSame('approved', $business->fresh()->status);
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

    public function test_guest_cannot_inject_membership_status_or_staff_permissions(): void
    {
        $this->post(route('membership.store'), [
            ...$this->payload(), 'status' => 'approved', 'membership_code' => 'INJECTED',
            'roles' => ['association_manager'], 'approval_status' => 'approved', 'submitted_by_user_id' => 9999,
        ])->assertSessionHasNoErrors();
        $business = Business::query()->firstOrFail();
        $this->assertSame('pending', $business->status);
        $this->assertNull($business->membership_code);
        $this->assertFalse($business->submittedBy->canReviewAssociation());
        $this->assertFalse($business->submittedBy->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_two_businesses_have_distinct_memberships_for_one_representative(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $second = Business::query()->create([
            'name' => 'Doanh nghiệp thứ hai', 'slug' => 'second-business', 'status' => 'pending',
            'submitted_by_user_id' => $business->submitted_by_user_id, 'business_chapter_id' => $business->business_chapter_id,
        ]);
        $second->addMedia(UploadedFile::fake()->create('don-2.pdf', 20, 'application/pdf'))->toMediaCollection('signed_membership_application');
        foreach ([$business, $second] as $record) {
            app(BusinessApprovalService::class)->approveForChapter($record, $association, $record->business_chapter_id);
            app(BusinessApprovalService::class)->receive($record, $chapterUser);
        }
        $this->assertNotSame($business->fresh()->membership_code, $second->fresh()->membership_code);
        $this->assertDatabaseCount('members', 1);
        $this->assertSame(2, Business::query()->representedBy($business->submittedBy)->where('status', 'approved')->count());
    }

    public function test_changing_an_approved_profile_returns_to_review_and_preserves_its_membership_code(): void
    {
        [$business, $association, $chapterUser] = $this->workflow();
        $payload = $this->payload();
        $business->update(Arr::only($payload, ['business_type', 'business_size', 'phone', 'email', 'address', 'province', 'summary']));
        $business->industries()->sync($payload['industry_ids']);
        app(BusinessApprovalService::class)->approveForChapter($business, $association, $business->business_chapter_id);
        app(BusinessApprovalService::class)->receive($business, $chapterUser);
        $code = $business->fresh()->membership_code;
        Filament::setCurrentPanel(Filament::getPanel('member'));
        $this->actingAs($business->submittedBy, 'web');
        Livewire::test(EditMyBusiness::class, ['record' => $business->id])
            ->fillForm(['summary' => 'Nội dung cập nhật cần xét duyệt.'])->call('save')->assertHasNoFormErrors()->assertRedirect(route('account.dashboard'));
        $this->assertSame('pending', $business->fresh()->status);
        $this->assertSame($code, $business->fresh()->membership_code);
        $this->assertNull($business->fresh()->association_approved_at);
        $this->get(route('account.dashboard'))->assertOk();
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
            'representative_name' => 'Đại diện kiểm thử', 'industry_ids' => [$industry->id], 'phone' => '0900000000', 'email' => 'company@example.test',
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
        $business->addMedia(UploadedFile::fake()->create('don-da-ky.pdf', 20, 'application/pdf'))->toMediaCollection('signed_membership_application');

        return [$business, $association, $chapterUser];
    }
}
