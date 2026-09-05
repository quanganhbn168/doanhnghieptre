<?php

namespace Tests\Feature;

use App\Filament\Association\Resources\ProfessionalGroups\Pages\CreateProfessionalGroup;
use App\Filament\Association\Resources\ProfessionalGroups\Pages\EditProfessionalGroup;
use App\Filament\Association\Resources\ProfessionalGroups\ProfessionalGroupResource;
use App\Filament\Resources\Businesses\Pages\EditBusiness;
use App\Models\Business;
use App\Models\Industry;
use App\Models\User;
use App\Services\AssociationHomeService;
use Database\Seeders\AssociationRoleSeeder;
use Database\Seeders\DntProfessionalGroupSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfessionalGroupImportTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $status = 'approved'): Business
    {
        return Business::query()->create(['name' => 'Doanh nghiệp '.Str::random(8), 'slug' => Str::uuid(), 'status' => $status]);
    }

    public function test_source_import_preserves_names_order_description_and_is_repeatable_after_cms_edits(): void
    {
        $this->seed(DntProfessionalGroupSeeder::class);
        $source = json_decode(file_get_contents(database_path('data/dnt-professional-groups.json')), true);
        $groups = Industry::query()->memberGroups()->orderBy('sort_order')->get();
        $this->assertCount(17, $groups);
        $this->assertSame(array_column($source['groups'], 'name'), $groups->pluck('name')->all());
        $this->assertSame(array_column($source['groups'], 'slug'), $groups->pluck('slug')->all());
        $this->assertSame('Khách sạn, chung cư, khu thương mại dịch vụ, nhà ở xã hội và khu đô thị', $groups->firstWhere('slug', 'khoi-bat-dong-san')->description);
        $group = $groups->first();
        $group->update(['name' => 'Tên do Hội điều chỉnh', 'slug' => 'ten-do-hoi-dieu-chinh', 'description' => 'Mô tả đã biên tập', 'sort_order' => 300, 'show_on_home' => false, 'is_active' => false]);
        $this->seed(DntProfessionalGroupSeeder::class);
        $this->assertDatabaseCount('industries', 17);
        $this->assertSame('Tên do Hội điều chỉnh', $group->fresh()->name);
        $this->assertSame(300, $group->fresh()->sort_order);
        $this->assertFalse($group->fresh()->show_on_home);
        $this->assertFalse($group->fresh()->is_active);
    }

    public function test_retired_sample_groups_keep_business_assignments_and_unrelated_groups_are_untouched(): void
    {
        $legacy = Industry::query()->create(['name' => 'Xây dựng', 'slug' => 'xay-dung', 'is_member_group' => true]);
        $custom = Industry::query()->create(['name' => 'Khối riêng của Hội', 'slug' => 'khoi-rieng', 'is_member_group' => true]);
        $business = $this->business();
        $business->industries()->attach($legacy, ['is_primary' => true]);
        $this->seed(DntProfessionalGroupSeeder::class);
        $this->assertFalse($legacy->fresh()->is_member_group);
        $this->assertTrue($custom->fresh()->is_member_group);
        $this->assertDatabaseHas('business_industries', ['business_id' => $business->id, 'industry_id' => $legacy->id, 'is_primary' => true]);
        $this->assertSame([$legacy->id], $business->industries()->pluck('industries.id')->all());
    }

    public function test_homepage_uses_managed_groups_order_visibility_and_actual_approved_business_counts(): void
    {
        $this->seed(DntProfessionalGroupSeeder::class);
        $groups = Industry::query()->orderBy('sort_order')->get();
        $this->business()->industries()->attach($groups[0]);
        $this->business('chapter_pending')->industries()->attach($groups[0]);
        $fields = app(AssociationHomeService::class)->activityFields();
        $this->assertCount(17, $fields);
        $this->assertSame(1, $fields[0]->business_count);
        $this->assertSame(0, $fields[1]->business_count);
        $this->assertNull($fields[0]->image_url);
        $groups[0]->update(['sort_order' => 200]);
        $groups[1]->update(['show_on_home' => false]);
        $groups[2]->update(['is_active' => false]);
        $fields = app(AssociationHomeService::class)->activityFields();
        $this->assertCount(15, $fields);
        $this->assertSame($groups[3]->slug, $fields->first()->slug);
        $this->assertSame($groups[0]->slug, $fields->last()->slug);
        $this->get(route('home'))->assertOk()->assertSee(route('directory.index', ['industry' => $groups[0]->slug]), false);
    }

    public function test_directory_and_membership_use_the_same_active_catalogue_and_filter_businesses(): void
    {
        $this->seed(DntProfessionalGroupSeeder::class);
        $group = Industry::query()->where('slug', 'khoi-chuyen-doi-so-ai')->firstOrFail();
        Industry::query()->create(['name' => 'Phân loại mẫu cũ', 'slug' => 'phan-loai-mau-cu', 'is_member_group' => false]);
        $business = $this->business();
        $business->industries()->attach($group);
        $other = $this->business();
        $this->get(route('membership.create'))->assertOk()->assertSee('Khối chuyển đổi số và trí tuệ nhân tạo')->assertDontSee('Phân loại mẫu cũ')
            ->assertViewHas('industries', fn ($items) => $items->count() === 17);
        $this->get(route('directory.index', ['industry' => $group->slug]))->assertOk()->assertSee($business->name)->assertDontSee($other->name)
            ->assertViewHas('industries', fn ($items) => $items->count() === 17);
    }

    public function test_association_manages_shared_group_resource_and_chapter_staff_cannot_access_it(): void
    {
        $this->seed([AssociationRoleSeeder::class, DntProfessionalGroupSeeder::class]);
        $staff = User::factory()->create();
        $staff->assignRole('association_manager');
        $this->actingAs($staff, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('association'));
        $this->get('/hoi/khoi-nganh-nghe')->assertOk()->assertSee('Khối ngành nghề');
        $group = Industry::query()->firstOrFail();
        Livewire::test(EditProfessionalGroup::class, ['record' => $group->id])->fillForm(['name' => 'Khối đã cập nhật', 'show_on_home' => false, 'sort_order' => 200])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Khối đã cập nhật', $group->fresh()->name);
        $this->assertFalse($group->fresh()->show_on_home);
        Livewire::test(CreateProfessionalGroup::class)->fillForm(['name' => 'Khối do Hội bổ sung', 'slug' => 'khoi-bo-sung', 'sort_order' => 210])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('industries', ['slug' => 'khoi-bo-sung', 'is_member_group' => true]);
        $this->business()->industries()->attach($group);
        $this->assertFalse(ProfessionalGroupResource::canDelete($group));

        $chapter = User::factory()->create();
        $chapter->assignRole('chapter_manager');
        $this->actingAs($chapter, 'admin')->get('/hoi/khoi-nganh-nghe')->assertForbidden();
        $this->get('/hoi/khoi-nganh-nghe/'.$group->id.'/edit')->assertForbidden();
    }

    public function test_business_editor_rejects_retired_groups_and_accepts_current_groups(): void
    {
        $this->seed(DntProfessionalGroupSeeder::class);
        $group = Industry::query()->memberGroups()->firstOrFail();
        $legacy = Industry::query()->create(['name' => 'Phân loại cũ để đối chiếu', 'slug' => 'phan-loai-cu', 'is_member_group' => false]);
        $business = $this->business();
        $business->industries()->attach($legacy);
        $admin = User::factory()->create();
        Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole('super_admin');
        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(EditBusiness::class, ['record' => $business->id])
            ->assertSee('Phân loại cũ để đối chiếu')
            ->fillForm(['industries' => [$legacy->id]])->call('save')->assertHasFormErrors(['industries']);
        $this->assertDatabaseHas('business_industries', ['business_id' => $business->id, 'industry_id' => $legacy->id]);
        Livewire::test(EditBusiness::class, ['record' => $business->id])
            ->fillForm(['industries' => [$group->id]])->call('save')->assertHasNoFormErrors();
        $this->assertSame([$group->id], $business->fresh()->industries()->pluck('industries.id')->all());
    }
}
