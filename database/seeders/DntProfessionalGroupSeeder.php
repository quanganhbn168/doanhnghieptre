<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DntProfessionalGroupSeeder extends Seeder
{
    // Previous sample vocabulary. Retain rows and all business links for review.
    private const LEGACY_SAMPLE_SLUGS = [
        'nong-lam-ngu-nghiep', 'cong-nghiep-che-bien-che-tao', 'xay-dung',
        'thuong-mai-ban-buon-ban-le', 'van-tai-kho-bai', 'luu-tru-an-uong',
        'thong-tin-truyen-thong', 'tai-chinh-ngan-hang-bao-hiem', 'kinh-doanh-bat-dong-san',
        'khoa-hoc-cong-nghe', 'tu-van-phap-ly-ke-toan', 'dich-vu-hanh-chinh-ho-tro',
        'giao-duc-dao-tao', 'y-te-cham-soc-suc-khoe', 'van-hoa-the-thao-giai-tri',
        'dich-vu-ca-nhan-cong-dong', 'nang-luong-moi-truong', 'khac',
    ];

    public function run(): void
    {
        $source = json_decode(file_get_contents(database_path('data/dnt-professional-groups.json')), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($source): void {
            foreach ($source['groups'] as $group) {
                // A repeat deployment must preserve subsequent CMS edits and visibility choices.
                Industry::query()->firstOrCreate(['source_url' => $source['source_url'].'?industry='.$group['slug']], [
                    'slug' => $group['slug'],
                    'name' => $group['name'],
                    'description' => $group['description'],
                    'sort_order' => $group['order'] * 10,
                    'is_active' => true,
                    'is_member_group' => true,
                    'show_on_home' => true,
                ]);
            }

            Industry::query()->whereIn('slug', self::LEGACY_SAMPLE_SLUGS)->where('is_member_group', true)
                ->update(['is_member_group' => false, 'show_on_home' => false]);
        });
    }
}
