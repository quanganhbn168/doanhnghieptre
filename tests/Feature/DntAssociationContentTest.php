<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessChapter;
use App\Models\Industry;
use App\Models\Intro;
use App\Models\User;
use App\Services\SiteChromeCache;
use Database\Seeders\DntFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DntAssociationContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public_media');
    }

    public function test_foundation_data_contains_nine_active_chapters_and_eighteen_professional_groups(): void
    {
        $this->seed(DntFoundationSeeder::class);

        $this->assertSame(9, BusinessChapter::query()->where('is_active', true)->count());
        $this->assertSame(18, Industry::query()->memberGroups()->where('is_active', true)->count());
        $this->assertTrue(Industry::query()->memberGroups()->where('slug', 'khac')->exists());
        $this->assertSame('approved', User::query()->where('email', 'hoi-vien.demo@dnt-seed.example')->value('approval_status'));
        $this->assertSame(0, Business::query()->where('status', 'approved')->whereNull('membership_code')->count());
    }

    public function test_about_page_renders_the_managed_organization_intro(): void
    {
        $this->seed(DntFoundationSeeder::class);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Giới thiệu')
            ->assertSee('Cơ cấu, tổ chức của Hội')
            ->assertDontSee('09 Chi hội trực thuộc');

        $this->get(route('about.show', ['slug' => 'co-cau-to-chuc-hoi']))
            ->assertOk()
            ->assertSee('Cơ cấu, tổ chức của Hội')
            ->assertSee('09 Chi hội trực thuộc')
            ->assertSee('Chi hội Yên Phong');
    }

    public function test_about_navigation_opens_a_page_for_each_active_intro(): void
    {
        $this->seed(DntFoundationSeeder::class);
        app(SiteChromeCache::class)->forget();

        $this->get(route('about'))
            ->assertOk()
            ->assertSee(route('about.show', ['slug' => 'gioi-thieu-hoi']), false)
            ->assertSee(route('about.show', ['slug' => 'co-cau-to-chuc-hoi']), false)
            ->assertDontSee('#gioi-thieu-hoi', false);

        $organizationIntro = Intro::query()->where('slug', 'co-cau-to-chuc-hoi')->firstOrFail();

        $this->get(route('about.show', ['slug' => $organizationIntro->slug]))
            ->assertOk()
            ->assertSee($organizationIntro->title)
            ->assertSee('09 Chi hội trực thuộc');
    }

    public function test_dnt_templates_do_not_include_eyebrow_markup(): void
    {
        foreach ([
            resource_path('views/frontend/association/about.blade.php'),
            resource_path('views/frontend/association/index.blade.php'),
            resource_path('views/frontend/home.blade.php'),
            public_path('assets/css/style.css'),
            public_path('assets/css/home.css'),
            public_path('assets/css/dnt.css'),
            public_path('assets/css/popup.css'),
        ] as $path) {
            $this->assertStringNotContainsString('eyebrow', file_get_contents($path), $path);
        }

        $this->assertStringNotContainsString('$tradePost->type_label', file_get_contents(resource_path('views/frontend/home.blade.php')));
        $this->assertStringNotContainsString('$item->type_label', file_get_contents(resource_path('views/frontend/association/index.blade.php')));
    }

    public function test_business_application_accepts_a_domain_without_a_protocol_and_an_out_of_province_address(): void
    {
        $this->assertFileExists(public_path('downloads/don-gia-nhap-hoi-082026.docx'));

        $user = User::factory()->create(['phone' => '0966234989']);
        $group = Industry::query()->create([
            'name' => 'Nhóm kiểm thử',
            'slug' => 'nhom-kiem-thu',
            'sort_order' => 10,
            'is_active' => true,
            'is_member_group' => true,
        ]);

        $this->actingAs($user)
            ->get(route('membership.create'))
            ->assertOk()
            ->assertSee('Đăng ký hội viên')
            ->assertSee('Nhóm nghề nghiệp')
            ->assertSee(asset('downloads/don-gia-nhap-hoi-082026.docx'), false)
            ->assertSee('Tải bản đơn đã ký, đóng dấu');

        $this->actingAs($user)
            ->post(route('account.businesses.store'), [
                'name' => 'Công ty Kiểm thử',
                'representative_name' => 'Người đại diện kiểm thử',
                'tax_code' => 'DNT-TEST-001',
                'business_type' => 'limited',
                'business_size' => 'small',
                'industry_ids' => [$group->id],
                'phone' => '0966234989',
                'email' => 'contact@example.test',
                'website' => 'vinhgiang.com.vn',
                'address' => 'Số 1 phố Kiểm thử',
                'province' => 'Hà Nội',
                'district' => 'Cầu Giấy',
                'summary' => 'Hồ sơ kiểm thử cho doanh nghiệp ngoài tỉnh Bắc Ninh.',
                'membership_application' => UploadedFile::fake()->create('don-gia-nhap-hoi-da-ky.pdf', 256, 'application/pdf'),
                'confirm_information' => '1',
            ])
            ->assertRedirect(route('account.dashboard'));

        $this->assertDatabaseHas('businesses', [
            'name' => 'Công ty Kiểm thử',
            'website' => 'vinhgiang.com.vn',
            'province' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'status' => 'pending',
        ]);

        $business = Business::query()->where('tax_code', 'DNT-TEST-001')->firstOrFail();

        $this->assertTrue($business->hasMedia('signed_membership_application'));
        $this->actingAs($user)
            ->get(route('business.membership-application.download', $business))
            ->assertOk()
            ->assertDownload('don-gia-nhap-hoi-da-ky-'.$business->id.'.pdf');

        $admin = User::factory()->create();
        Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole('super_admin');
        $this->actingAs($admin, 'admin')
            ->get(route('business.membership-application.download', $business))
            ->assertOk()
            ->assertDownload('don-gia-nhap-hoi-da-ky-'.$business->id.'.pdf');
    }

    public function test_association_content_resources_and_professional_group_picker_are_available_to_the_admin(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')->get('/admin/intros')->assertOk()->assertSee('Tiêu đề');
        $this->actingAs($admin, 'admin')->get('/admin/intros/create')->assertOk()->assertSee('Tiêu đề')->assertDontSee('Loại nội dung');
        $this->actingAs($admin, 'admin')->get('/admin/professional-groups')->assertOk()->assertSee('Nhóm nghề nghiệp');
        $this->actingAs($admin, 'admin')->get('/admin/business-chapters')->assertOk()->assertSee('Chi hội');
        $this->actingAs($admin, 'admin')->get('/admin/account-approvals')->assertOk();
        $this->actingAs($admin, 'admin')->get('/admin/businesses/create')->assertOk()->assertSee('Nhóm nghề nghiệp');
    }
}
