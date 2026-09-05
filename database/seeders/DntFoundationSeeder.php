<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dữ liệu nền tảng cho cổng thông tin Hội Doanh nhân trẻ Bắc Ninh.
 *
 * Seeder này chỉ thêm hoặc cập nhật dữ liệu theo khoá nghiệp vụ; tuyệt đối
 * không truncate, xoá, hoặc ghi đè dữ liệu không thuộc bộ khởi tạo này.
 */
class DntFoundationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $industryIds = $this->seedIndustries($now);
            $this->call(DntProfessionalGroupSeeder::class);
            $categoryIds = $this->seedBusinessCategories($now);
            $chapterIds = $this->seedBusinessChapters($now);
            $positionIds = $this->seedOrganizationPositions($now);
            $offeringCategoryIds = $this->seedOfferingCategories($now);
            $demoUserIds = $this->seedDemoApplicantUsers($now);
            $memberIds = $this->seedMembers($now, $demoUserIds);

            $this->seedPositionAssignments($memberIds, $positionIds, $now);
            $businessIds = $this->seedBusinesses($categoryIds, $chapterIds, $industryIds, $memberIds, $demoUserIds, $now);
            $this->seedOfferings($businessIds, $memberIds, $offeringCategoryIds, $industryIds, $now);
            $this->seedEvents($now);
            $this->seedTradePosts($businessIds, $memberIds, $industryIds, $now);
            $this->seedNews();
            $this->seedIntros($now);
        });
    }

    /**
     * Tài khoản minh hoạ luồng nộp hồ sơ. Chỉ tài khoản có hồ sơ đã duyệt mới
     * được tạo hội viên, đúng với quy trình vận hành thực tế của Hội.
     *
     * @return array<string, int>
     */
    private function seedDemoApplicantUsers(Carbon $now): array
    {
        $password = Hash::make((string) env('DNT_DEMO_PASSWORD', 'password'));
        $users = [
            'approved' => ['name' => 'Trần Đức Minh', 'email' => 'hoi-vien.demo@dnt-seed.example', 'phone' => '0200 000 1013'],
            'pending' => ['name' => 'Nguyễn Thu Trang', 'email' => 'cho-duyet.demo@dnt-seed.example', 'phone' => '0200 000 1014'],
            'rejected' => ['name' => 'Lê Thành Nam', 'email' => 'bo-sung.demo@dnt-seed.example', 'phone' => '0200 000 1015'],
            'draft' => ['name' => 'Phạm Khánh Vy', 'email' => 'ban-nhap.demo@dnt-seed.example', 'phone' => '0200 000 1016'],
        ];

        $ids = [];
        foreach ($users as $key => $user) {
            $ids[$key] = $this->upsert('users', ['email' => $user['email']], [
                ...$user,
                'password' => $password,
                'email_verified_at' => $now,
                'is_active' => true,
                'approval_status' => 'approved',
            ], $now);
        }

        return $ids;
    }

    /** @return array<string, int> */
    private function seedIndustries(Carbon $now): array
    {
        $roots = [
            ['slug' => 'san-xuat-cong-nghiep', 'name' => 'Sản xuất & công nghiệp', 'description' => 'Sản xuất, công nghiệp hỗ trợ và chế biến.'],
            ['slug' => 'cong-nghe-so', 'name' => 'Công nghệ số', 'description' => 'Phần mềm, công nghệ thông tin và tự động hoá.'],
            ['slug' => 'thuong-mai-dich-vu', 'name' => 'Thương mại & dịch vụ', 'description' => 'Phân phối, dịch vụ chuyên môn và dịch vụ tiêu dùng.'],
            ['slug' => 'xay-dung-ha-tang', 'name' => 'Xây dựng & hạ tầng', 'description' => 'Xây dựng, vật liệu và hạ tầng kỹ thuật.'],
            ['slug' => 'logistics-xuat-nhap-khau', 'name' => 'Logistics & xuất nhập khẩu', 'description' => 'Vận tải, kho bãi và thương mại quốc tế.'],
            ['slug' => 'nong-nghiep-thuc-pham', 'name' => 'Nông nghiệp & thực phẩm', 'description' => 'Nông nghiệp công nghệ cao, chế biến và thực phẩm.'],
            ['slug' => 'tai-chinh-chuyen-mon', 'name' => 'Tài chính & dịch vụ chuyên môn', 'description' => 'Tài chính, tư vấn và dịch vụ hỗ trợ doanh nghiệp.'],
        ];

        $ids = [];
        foreach ($roots as $sortOrder => $industry) {
            $ids[$industry['slug']] = $this->upsert('industries', ['slug' => $industry['slug']], [
                ...$industry,
                'parent_id' => null,
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now);
        }

        $children = [
            ['parent' => 'san-xuat-cong-nghiep', 'slug' => 'co-khi-chinh-xac', 'name' => 'Cơ khí chính xác', 'description' => 'Gia công cơ khí, khuôn mẫu và thiết bị công nghiệp.'],
            ['parent' => 'san-xuat-cong-nghiep', 'slug' => 'dien-tu-cong-nghiep', 'name' => 'Điện tử công nghiệp', 'description' => 'Thiết bị điện tử, linh kiện và hệ thống điện.'],
            ['parent' => 'san-xuat-cong-nghiep', 'slug' => 'cong-nghiep-ho-tro', 'name' => 'Công nghiệp hỗ trợ', 'description' => 'Linh kiện, phụ tùng và vật tư cho chuỗi cung ứng.'],
            ['parent' => 'san-xuat-cong-nghiep', 'slug' => 'det-may-da-giay', 'name' => 'Dệt may & da giày', 'description' => 'Sản xuất dệt may, phụ liệu và da giày.'],
            ['parent' => 'cong-nghe-so', 'slug' => 'phan-mem-chuyen-doi-so', 'name' => 'Phần mềm & chuyển đổi số', 'description' => 'Phần mềm quản trị, nền tảng số và tư vấn chuyển đổi số.'],
            ['parent' => 'cong-nghe-so', 'slug' => 'tu-dong-hoa-iot', 'name' => 'Tự động hoá & IoT', 'description' => 'Tự động hoá sản xuất, IoT và thiết bị thông minh.'],
            ['parent' => 'cong-nghe-so', 'slug' => 'thuong-mai-dien-tu', 'name' => 'Thương mại điện tử', 'description' => 'Nền tảng bán hàng, thanh toán và tiếp thị số.'],
            ['parent' => 'thuong-mai-dich-vu', 'slug' => 'phan-phoi-ban-le', 'name' => 'Phân phối & bán lẻ', 'description' => 'Phân phối hàng hoá, đại lý và bán lẻ.'],
            ['parent' => 'thuong-mai-dich-vu', 'slug' => 'giao-duc-dao-tao', 'name' => 'Giáo dục & đào tạo', 'description' => 'Đào tạo kỹ năng, công nghệ và phát triển nhân lực.'],
            ['parent' => 'xay-dung-ha-tang', 'slug' => 'xay-dung-dan-dung-cong-nghiep', 'name' => 'Xây dựng dân dụng & công nghiệp', 'description' => 'Thi công công trình dân dụng, công nghiệp và hạ tầng.'],
            ['parent' => 'xay-dung-ha-tang', 'slug' => 'vat-lieu-xay-dung', 'name' => 'Vật liệu xây dựng', 'description' => 'Vật liệu mới, nội thất và hoàn thiện công trình.'],
            ['parent' => 'logistics-xuat-nhap-khau', 'slug' => 'van-tai-kho-bai', 'name' => 'Vận tải & kho bãi', 'description' => 'Vận tải hàng hoá, kho bãi và dịch vụ hoàn tất đơn hàng.'],
            ['parent' => 'logistics-xuat-nhap-khau', 'slug' => 'xuat-nhap-khau', 'name' => 'Xuất nhập khẩu', 'description' => 'Kết nối thị trường, thủ tục và thương mại quốc tế.'],
            ['parent' => 'nong-nghiep-thuc-pham', 'slug' => 'nong-nghiep-cong-nghe-cao', 'name' => 'Nông nghiệp công nghệ cao', 'description' => 'Nông nghiệp sạch, công nghệ cao và chuỗi giá trị nông sản.'],
            ['parent' => 'nong-nghiep-thuc-pham', 'slug' => 'thuc-pham-do-uong', 'name' => 'Thực phẩm & đồ uống', 'description' => 'Chế biến thực phẩm, đồ uống và đặc sản địa phương.'],
            ['parent' => 'tai-chinh-chuyen-mon', 'slug' => 'tu-van-doanh-nghiep', 'name' => 'Tư vấn doanh nghiệp', 'description' => 'Tư vấn pháp lý, tài chính, nhân sự và quản trị.'],
        ];

        foreach ($children as $sortOrder => $industry) {
            $ids[$industry['slug']] = $this->upsert('industries', ['slug' => $industry['slug']], [
                'parent_id' => $ids[$industry['parent']],
                'name' => $industry['name'],
                'description' => $industry['description'],
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now);
        }

        return $ids;
    }

    /** @return array<string, int> */
    private function seedBusinessCategories(Carbon $now): array
    {
        $categories = [
            ['slug' => 'cong-nghiep-san-xuat', 'name' => 'Công nghiệp & sản xuất', 'description' => 'Doanh nghiệp cơ khí, điện tử, công nghiệp hỗ trợ và sản xuất.'],
            ['slug' => 'cong-nghe', 'name' => 'Công nghệ', 'description' => 'Doanh nghiệp phần mềm, tự động hoá và giải pháp số.'],
            ['slug' => 'thuong-mai-dich-vu', 'name' => 'Thương mại & dịch vụ', 'description' => 'Doanh nghiệp phân phối và dịch vụ chuyên môn.'],
            ['slug' => 'xay-dung-vat-lieu', 'name' => 'Xây dựng & vật liệu', 'description' => 'Doanh nghiệp xây dựng, hạ tầng và vật liệu.'],
            ['slug' => 'logistics', 'name' => 'Logistics', 'description' => 'Doanh nghiệp vận tải, kho bãi và xuất nhập khẩu.'],
            ['slug' => 'nong-nghiep-thuc-pham', 'name' => 'Nông nghiệp & thực phẩm', 'description' => 'Doanh nghiệp nông nghiệp sạch và chế biến thực phẩm.'],
            ['slug' => 'giao-duc-dao-tao', 'name' => 'Giáo dục & đào tạo', 'description' => 'Doanh nghiệp đào tạo và phát triển nhân lực.'],
        ];

        return collect($categories)->mapWithKeys(fn (array $category, int $sortOrder): array => [
            $category['slug'] => $this->upsert('business_categories', ['slug' => $category['slug']], [
                ...$category,
                'parent_id' => null,
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now),
        ])->all();
    }

    /** @return array<string, int> */
    private function seedBusinessChapters(Carbon $now): array
    {
        DB::table('business_chapters')
            ->whereIn('slug', [
                'chi-hoi-san-xuat-cong-nghiep',
                'chi-hoi-cong-nghe-doi-moi',
                'chi-hoi-thuong-mai-dich-vu',
                'chi-hoi-ha-tang-logistics',
            ])
            ->update(['is_active' => false, 'updated_at' => $now]);

        $chapters = [
            ['slug' => 'chi-hoi-bac-giang-yen-dung', 'name' => 'Chi hội Bắc Giang - Yên Dũng', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Bắc Giang - Yên Dũng.'],
            ['slug' => 'chi-hoi-hiep-hoa-viet-yen', 'name' => 'Chi hội Hiệp Hòa - Việt Yên', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Hiệp Hòa - Việt Yên.'],
            ['slug' => 'chi-hoi-yen-the-lang-giang-tan-yen', 'name' => 'Chi hội Yên Thế - Lạng Giang - Tân Yên', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Yên Thế - Lạng Giang - Tân Yên.'],
            ['slug' => 'chi-hoi-luc-ngan-luc-nam-son-dong', 'name' => 'Chi hội Lục Ngạn - Lục Nam - Sơn Động', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Lục Ngạn - Lục Nam - Sơn Động.'],
            ['slug' => 'chi-hoi-kinh-bac', 'name' => 'Chi hội Kinh Bắc', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Kinh Bắc.'],
            ['slug' => 'chi-hoi-nam-duong', 'name' => 'Chi hội Nam Đuống', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Nam Đuống.'],
            ['slug' => 'chi-hoi-tu-son', 'name' => 'Chi hội Từ Sơn', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Từ Sơn.'],
            ['slug' => 'chi-hoi-tien-du', 'name' => 'Chi hội Tiên Du', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Tiên Du.'],
            ['slug' => 'chi-hoi-yen-phong', 'name' => 'Chi hội Yên Phong', 'description' => 'Tổ chức trực thuộc Hội tại khu vực Yên Phong.'],
        ];

        $ids = collect($chapters)->mapWithKeys(fn (array $chapter, int $sortOrder): array => [
            $chapter['slug'] => $this->upsert('business_chapters', ['slug' => $chapter['slug']], [
                ...$chapter,
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now),
        ])->all();

        return [
            ...$ids,
            'chi-hoi-san-xuat-cong-nghiep' => $ids['chi-hoi-bac-giang-yen-dung'],
            'chi-hoi-cong-nghe-doi-moi' => $ids['chi-hoi-kinh-bac'],
            'chi-hoi-thuong-mai-dich-vu' => $ids['chi-hoi-nam-duong'],
            'chi-hoi-ha-tang-logistics' => $ids['chi-hoi-tu-son'],
        ];
    }

    /** @return array<string, int> */
    private function seedOrganizationPositions(Carbon $now): array
    {
        $positions = [
            ['slug' => 'chu-tich', 'name' => 'Chủ tịch', 'group_name' => 'Ban Chấp hành', 'description' => 'Phụ trách định hướng và điều hành hoạt động của Hội.'],
            ['slug' => 'pho-chu-tich', 'name' => 'Phó Chủ tịch', 'group_name' => 'Ban Chấp hành', 'description' => 'Hỗ trợ điều hành các mảng hoạt động trọng tâm.'],
            ['slug' => 'uy-vien-ban-chap-hanh', 'name' => 'Ủy viên Ban Chấp hành', 'group_name' => 'Ban Chấp hành', 'description' => 'Tham gia điều phối hoạt động và kết nối doanh nghiệp.'],
            ['slug' => 'thu-ky', 'name' => 'Thư ký', 'group_name' => 'Văn phòng Hội', 'description' => 'Điều phối thông tin và hoạt động văn phòng.'],
        ];

        return collect($positions)->mapWithKeys(fn (array $position, int $sortOrder): array => [
            $position['slug'] => $this->upsert('organization_positions', ['slug' => $position['slug']], [
                ...$position,
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now),
        ])->all();
    }

    /** @return array<string, int> */
    private function seedOfferingCategories(Carbon $now): array
    {
        $categories = [
            ['slug' => 'san-pham-cong-nghiep', 'name' => 'Sản phẩm công nghiệp', 'description' => 'Thiết bị, linh kiện và sản phẩm phục vụ sản xuất.'],
            ['slug' => 'giai-phap-cong-nghe', 'name' => 'Giải pháp công nghệ', 'description' => 'Phần mềm, nền tảng số và tự động hoá.'],
            ['slug' => 'dich-vu-logistics', 'name' => 'Dịch vụ logistics', 'description' => 'Vận tải, kho bãi và hỗ trợ xuất nhập khẩu.'],
            ['slug' => 'dich-vu-thuong-mai', 'name' => 'Dịch vụ thương mại', 'description' => 'Phân phối, xúc tiến và dịch vụ khách hàng.'],
            ['slug' => 'dich-vu-chuyen-mon', 'name' => 'Dịch vụ chuyên môn', 'description' => 'Tư vấn, đào tạo và phát triển năng lực.'],
        ];

        return collect($categories)->mapWithKeys(fn (array $category, int $sortOrder): array => [
            $category['slug'] => $this->upsert('offering_categories', ['slug' => $category['slug']], [
                ...$category,
                'parent_id' => null,
                'sort_order' => ($sortOrder + 1) * 10,
                'is_active' => true,
            ], $now),
        ])->all();
    }

    /** @return array<string, int> */
    private function seedMembers(Carbon $now, array $demoUserIds): array
    {
        $members = [
            ['member_code' => 'DNTBN-001', 'full_name' => 'Nguyễn Minh An', 'email' => 'minh.an@dnt-seed.example', 'phone' => '0200 000 1001', 'introduction' => 'Đại diện doanh nghiệp công nghệ số.'],
            ['member_code' => 'DNTBN-002', 'full_name' => 'Trần Thu Hà', 'email' => 'thu.ha@dnt-seed.example', 'phone' => '0200 000 1002', 'introduction' => 'Đại diện doanh nghiệp cơ khí chính xác.'],
            ['member_code' => 'DNTBN-003', 'full_name' => 'Vũ Quốc Cường', 'email' => 'quoc.cuong@dnt-seed.example', 'phone' => '0200 000 1003', 'introduction' => 'Đại diện doanh nghiệp điện tử công nghiệp.'],
            ['member_code' => 'DNTBN-004', 'full_name' => 'Phạm Tiến Đạt', 'email' => 'tien.dat@dnt-seed.example', 'phone' => '0200 000 1004', 'introduction' => 'Đại diện doanh nghiệp logistics.'],
            ['member_code' => 'DNTBN-005', 'full_name' => 'Lê Minh Hòa', 'email' => 'minh.hoa@dnt-seed.example', 'phone' => '0200 000 1005', 'introduction' => 'Đại diện doanh nghiệp xây dựng và hạ tầng.'],
            ['member_code' => 'DNTBN-006', 'full_name' => 'Hoàng Quân Anh', 'email' => 'quan.anh@dnt-seed.example', 'phone' => '0200 000 1006', 'introduction' => 'Đại diện doanh nghiệp thực phẩm.'],
            ['member_code' => 'DNTBN-007', 'full_name' => 'Đỗ Ngọc Lan', 'email' => 'ngoc.lan@dnt-seed.example', 'phone' => '0200 000 1007', 'introduction' => 'Đại diện doanh nghiệp vật liệu xanh.'],
            ['member_code' => 'DNTBN-008', 'full_name' => 'Bùi Anh Tuấn', 'email' => 'anh.tuan@dnt-seed.example', 'phone' => '0200 000 1008', 'introduction' => 'Đại diện doanh nghiệp nông nghiệp công nghệ cao.'],
            ['member_code' => 'DNTBN-009', 'full_name' => 'Nguyễn Hải Yến', 'email' => 'hai.yen@dnt-seed.example', 'phone' => '0200 000 1009', 'introduction' => 'Đại diện doanh nghiệp thương mại và phân phối.'],
            ['member_code' => 'DNTBN-010', 'full_name' => 'Đặng Việt Phong', 'email' => 'viet.phong@dnt-seed.example', 'phone' => '0200 000 1010', 'introduction' => 'Đại diện doanh nghiệp nội thất.'],
            ['member_code' => 'DNTBN-011', 'full_name' => 'Ngô Khánh Linh', 'email' => 'khanh.linh@dnt-seed.example', 'phone' => '0200 000 1011', 'introduction' => 'Đại diện doanh nghiệp giáo dục công nghệ.'],
            ['member_code' => 'DNTBN-012', 'full_name' => 'Đinh Gia Bảo', 'email' => 'gia.bao@dnt-seed.example', 'phone' => '0200 000 1012', 'introduction' => 'Đại diện doanh nghiệp tự động hoá.'],
            ['member_code' => 'DNTBN-013', 'user_key' => 'approved', 'full_name' => 'Trần Đức Minh', 'email' => 'hoi-vien.demo@dnt-seed.example', 'phone' => '0200 000 1013', 'introduction' => 'Hội viên đại diện được tạo khi hồ sơ doanh nghiệp minh hoạ đã được Hội duyệt.'],
        ];

        $ids = [];
        foreach ($members as $index => $member) {
            $memberValues = $member;
            unset($memberValues['user_key']);

            $ids[$member['member_code']] = $this->upsert('members', ['member_code' => $member['member_code']], [
                ...$memberValues,
                'user_id' => isset($member['user_key']) ? $demoUserIds[$member['user_key']] : null,
                'province' => 'Bắc Ninh',
                'district' => $index % 2 === 0 ? 'Bắc Ninh' : 'Từ Sơn',
                'status' => 'approved',
                'joined_at' => $now->copy()->subMonths(12 - $index),
                'approved_at' => $now->copy()->subMonths(12 - $index),
            ], $now);
        }

        foreach ($ids as $memberCode => $memberId) {
            $this->upsert('member_status_histories', [
                'member_id' => $memberId,
                'to_status' => 'approved',
            ], [
                'from_status' => 'pending',
                'reason' => 'Khởi tạo dữ liệu danh bạ doanh nhân trẻ.',
                'metadata' => json_encode(['source' => 'dnt-foundation-seeder', 'member_code' => $memberCode], JSON_UNESCAPED_UNICODE),
                'changed_at' => $now,
            ], $now, false);
        }

        return $ids;
    }

    /** @param array<string, int> $memberIds @param array<string, int> $positionIds */
    private function seedPositionAssignments(array $memberIds, array $positionIds, Carbon $now): void
    {
        $assignments = [
            ['member_code' => 'DNTBN-001', 'position_slug' => 'chu-tich'],
            ['member_code' => 'DNTBN-002', 'position_slug' => 'pho-chu-tich'],
            ['member_code' => 'DNTBN-003', 'position_slug' => 'pho-chu-tich'],
            ['member_code' => 'DNTBN-004', 'position_slug' => 'uy-vien-ban-chap-hanh'],
            ['member_code' => 'DNTBN-005', 'position_slug' => 'thu-ky'],
        ];

        foreach ($assignments as $assignment) {
            $this->upsert('member_position_assignments', [
                'member_id' => $memberIds[$assignment['member_code']],
                'organization_position_id' => $positionIds[$assignment['position_slug']],
            ], [
                'appointment_reference' => 'DNTBN/2026/QĐ-'.str_pad((string) $memberIds[$assignment['member_code']], 3, '0', STR_PAD_LEFT),
                'appointed_at' => $now->copy()->subMonths(6)->toDateString(),
                'ended_at' => null,
                'is_current' => true,
                'note' => 'Dữ liệu khởi tạo cơ cấu tổ chức.',
            ], $now);
        }
    }

    /** @return array<string, int> @param array<string, int> $categoryIds @param array<string, int> $chapterIds @param array<string, int> $industryIds @param array<string, int> $memberIds @param array<string, int> $demoUserIds */
    private function seedBusinesses(array $categoryIds, array $chapterIds, array $industryIds, array $memberIds, array $demoUserIds, Carbon $now): array
    {
        $businesses = [
            ['slug' => 'cong-nghe-kinh-bac-so', 'tax_code' => 'DNT-SEED-001', 'name' => 'Công ty Cổ phần Công nghệ Kinh Bắc Số', 'category' => 'cong-nghe', 'industry' => 'phan-mem-chuyen-doi-so', 'chapter' => 'chi-hoi-cong-nghe-doi-moi', 'size' => 'medium', 'member_code' => 'DNTBN-001', 'business_type' => 'joint_stock', 'summary' => 'Giải pháp phần mềm quản trị và chuyển đổi số cho doanh nghiệp vừa và nhỏ.'],
            ['slug' => 'co-khi-an-phu-bac-ninh', 'tax_code' => 'DNT-SEED-002', 'name' => 'Công ty TNHH Cơ khí An Phú Bắc Ninh', 'category' => 'cong-nghiep-san-xuat', 'industry' => 'co-khi-chinh-xac', 'chapter' => 'chi-hoi-san-xuat-cong-nghiep', 'size' => 'medium', 'member_code' => 'DNTBN-002', 'business_type' => 'limited', 'summary' => 'Gia công chi tiết cơ khí chính xác và thiết bị phụ trợ sản xuất.'],
            ['slug' => 'dien-tu-viet-thanh', 'tax_code' => 'DNT-SEED-003', 'name' => 'Công ty Cổ phần Điện tử Việt Thành', 'category' => 'cong-nghiep-san-xuat', 'industry' => 'dien-tu-cong-nghiep', 'chapter' => 'chi-hoi-san-xuat-cong-nghiep', 'size' => 'large', 'member_code' => 'DNTBN-003', 'business_type' => 'joint_stock', 'summary' => 'Sản xuất, tích hợp thiết bị điện tử và hệ thống điều khiển công nghiệp.'],
            ['slug' => 'logistics-dong-do-kinh-bac', 'tax_code' => 'DNT-SEED-004', 'name' => 'Công ty TNHH Logistics Đông Đô Kinh Bắc', 'category' => 'logistics', 'industry' => 'van-tai-kho-bai', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'medium', 'member_code' => 'DNTBN-004', 'business_type' => 'limited', 'summary' => 'Dịch vụ vận tải, kho bãi và hoàn tất đơn hàng cho doanh nghiệp trong khu vực.'],
            ['slug' => 'xay-dung-ha-tang-bac-ha', 'tax_code' => 'DNT-SEED-005', 'name' => 'Công ty Cổ phần Xây dựng Hạ tầng Bắc Hà', 'category' => 'xay-dung-vat-lieu', 'industry' => 'xay-dung-dan-dung-cong-nghiep', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'large', 'member_code' => 'DNTBN-005', 'business_type' => 'joint_stock', 'summary' => 'Thi công công trình công nghiệp, dân dụng và hạ tầng kỹ thuật.'],
            ['slug' => 'thuc-pham-sach-vi-kinh-bac', 'tax_code' => 'DNT-SEED-006', 'name' => 'Công ty TNHH Thực phẩm sạch Vị Kinh Bắc', 'category' => 'nong-nghiep-thuc-pham', 'industry' => 'thuc-pham-do-uong', 'chapter' => 'chi-hoi-thuong-mai-dich-vu', 'size' => 'small', 'member_code' => 'DNTBN-006', 'business_type' => 'limited', 'summary' => 'Sản xuất thực phẩm chế biến, quà tặng nông sản và đặc sản địa phương.'],
            ['slug' => 'vat-lieu-xanh-dai-phuc', 'tax_code' => 'DNT-SEED-007', 'name' => 'Công ty Cổ phần Vật liệu xanh Đại Phúc', 'category' => 'xay-dung-vat-lieu', 'industry' => 'vat-lieu-xay-dung', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'medium', 'member_code' => 'DNTBN-007', 'business_type' => 'joint_stock', 'summary' => 'Cung cấp vật liệu hoàn thiện công trình theo tiêu chí bền vững.'],
            ['slug' => 'nong-nghiep-cong-nghe-cao-an-thinh', 'tax_code' => 'DNT-SEED-008', 'name' => 'Công ty TNHH Nông nghiệp công nghệ cao An Thịnh', 'category' => 'nong-nghiep-thuc-pham', 'industry' => 'nong-nghiep-cong-nghe-cao', 'chapter' => 'chi-hoi-thuong-mai-dich-vu', 'size' => 'small', 'member_code' => 'DNTBN-008', 'business_type' => 'limited', 'summary' => 'Canh tác thông minh, sơ chế nông sản và phát triển chuỗi cung ứng sạch.'],
            ['slug' => 'thuong-mai-phan-phoi-minh-phat', 'tax_code' => 'DNT-SEED-009', 'name' => 'Công ty Cổ phần Thương mại & Phân phối Minh Phát', 'category' => 'thuong-mai-dich-vu', 'industry' => 'phan-phoi-ban-le', 'chapter' => 'chi-hoi-thuong-mai-dich-vu', 'size' => 'large', 'member_code' => 'DNTBN-009', 'business_type' => 'joint_stock', 'summary' => 'Phân phối hàng tiêu dùng, kết nối kênh bán lẻ và xúc tiến thương mại.'],
            ['slug' => 'noi-that-khong-gian-viet', 'tax_code' => 'DNT-SEED-010', 'name' => 'Công ty TNHH Nội thất & Không gian Việt', 'category' => 'xay-dung-vat-lieu', 'industry' => 'vat-lieu-xay-dung', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'small', 'member_code' => 'DNTBN-010', 'business_type' => 'limited', 'summary' => 'Thiết kế, thi công nội thất và cung cấp giải pháp không gian làm việc.'],
            ['slug' => 'giao-duc-cong-nghe-nextstep', 'tax_code' => 'DNT-SEED-011', 'name' => 'Công ty Cổ phần Giáo dục công nghệ NextStep', 'category' => 'giao-duc-dao-tao', 'industry' => 'giao-duc-dao-tao', 'chapter' => 'chi-hoi-cong-nghe-doi-moi', 'size' => 'small', 'member_code' => 'DNTBN-011', 'business_type' => 'joint_stock', 'summary' => 'Đào tạo kỹ năng số và nhân lực kỹ thuật cho doanh nghiệp.'],
            ['slug' => 'tu-dong-hoa-kinh-bac', 'tax_code' => 'DNT-SEED-012', 'name' => 'Công ty TNHH Tự động hoá Kinh Bắc', 'category' => 'cong-nghe', 'industry' => 'tu-dong-hoa-iot', 'chapter' => 'chi-hoi-cong-nghe-doi-moi', 'size' => 'medium', 'member_code' => 'DNTBN-012', 'business_type' => 'limited', 'summary' => 'Tích hợp dây chuyền tự động hoá và giải pháp giám sát sản xuất.'],
            ['slug' => 'minh-duc-tech-demo', 'tax_code' => 'DNT-DEMO-013', 'name' => 'Công ty TNHH Minh Đức Tech', 'category' => 'cong-nghe', 'industry' => 'phan-mem-chuyen-doi-so', 'chapter' => 'chi-hoi-cong-nghe-doi-moi', 'size' => 'small', 'member_code' => 'DNTBN-013', 'applicant' => 'approved', 'status' => 'approved', 'business_type' => 'limited', 'summary' => 'Hồ sơ doanh nghiệp đã được duyệt, sẵn sàng hiển thị tại danh bạ và cổng hội viên.', 'job_title' => 'Giám đốc điều hành'],
            ['slug' => 'thu-trang-logistics-demo', 'tax_code' => 'DNT-DEMO-014', 'name' => 'Công ty TNHH Logistics Thu Trang', 'category' => 'logistics', 'industry' => 'van-tai-kho-bai', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'medium', 'applicant' => 'pending', 'status' => 'pending', 'business_type' => 'limited', 'summary' => 'Hồ sơ doanh nghiệp đã nộp, đang chờ Hội kiểm tra thông tin và phê duyệt.', 'job_title' => 'Giám đốc'],
            ['slug' => 'nam-viet-noi-that-demo', 'tax_code' => 'DNT-DEMO-015', 'name' => 'Công ty TNHH Nội thất Nam Việt', 'category' => 'xay-dung-vat-lieu', 'industry' => 'vat-lieu-xay-dung', 'chapter' => 'chi-hoi-ha-tang-logistics', 'size' => 'small', 'applicant' => 'rejected', 'status' => 'rejected', 'business_type' => 'limited', 'summary' => 'Hồ sơ cần bổ sung mô tả năng lực và tài liệu xác thực trước khi gửi lại Hội.', 'job_title' => 'Giám đốc'],
            ['slug' => 'khanh-vy-food-demo', 'tax_code' => 'DNT-DEMO-016', 'name' => 'Công ty TNHH Thực phẩm Khánh Vy', 'category' => 'nong-nghiep-thuc-pham', 'industry' => 'thuc-pham-do-uong', 'chapter' => 'chi-hoi-thuong-mai-dich-vu', 'size' => 'small', 'applicant' => 'draft', 'status' => 'draft', 'business_type' => 'limited', 'summary' => 'Hồ sơ mới khởi tạo dưới dạng bản nháp để minh hoạ quy trình hoàn thiện trước khi nộp.', 'job_title' => 'Giám đốc'],
        ];

        // Dữ liệu danh bạ dùng 1–3 lĩnh vực/doanh nghiệp để kiểm tra bộ lọc.
        // Giới hạn nghiệp vụ là tối đa 5 lĩnh vực cho một doanh nghiệp.
        $additionalIndustries = [
            'cong-nghe-kinh-bac-so' => ['thuong-mai-dien-tu', 'tu-dong-hoa-iot'],
            'co-khi-an-phu-bac-ninh' => ['cong-nghiep-ho-tro'],
            'dien-tu-viet-thanh' => ['tu-dong-hoa-iot'],
            'logistics-dong-do-kinh-bac' => ['xuat-nhap-khau'],
            'xay-dung-ha-tang-bac-ha' => ['vat-lieu-xay-dung'],
            'thuc-pham-sach-vi-kinh-bac' => ['nong-nghiep-cong-nghe-cao'],
            'vat-lieu-xanh-dai-phuc' => ['xay-dung-dan-dung-cong-nghiep'],
            'nong-nghiep-cong-nghe-cao-an-thinh' => ['thuc-pham-do-uong'],
            'thuong-mai-phan-phoi-minh-phat' => ['thuong-mai-dien-tu'],
            'noi-that-khong-gian-viet' => ['xay-dung-dan-dung-cong-nghiep'],
            'giao-duc-cong-nghe-nextstep' => ['phan-mem-chuyen-doi-so'],
            'tu-dong-hoa-kinh-bac' => ['phan-mem-chuyen-doi-so', 'dien-tu-cong-nghiep'],
            'minh-duc-tech-demo' => ['thuong-mai-dien-tu', 'tu-dong-hoa-iot'],
            'thu-trang-logistics-demo' => ['xuat-nhap-khau'],
            'nam-viet-noi-that-demo' => ['xay-dung-dan-dung-cong-nghiep'],
            'khanh-vy-food-demo' => ['nong-nghiep-cong-nghe-cao'],
        ];

        $ids = [];
        foreach ($businesses as $index => $business) {
            $status = $business['status'] ?? 'approved';
            $memberCode = $business['member_code'] ?? null;

            $ids[$business['slug']] = $this->upsert('businesses', ['slug' => $business['slug']], [
                'business_category_id' => $categoryIds[$business['category']],
                'business_chapter_id' => $chapterIds[$business['chapter']],
                'name' => $business['name'],
                'legal_name' => $business['name'],
                'tax_code' => $business['tax_code'],
                'business_type' => $business['business_type'],
                'business_size' => $business['size'],
                'phone' => '0200 000 '.str_pad((string) (1101 + $index), 4, '0', STR_PAD_LEFT),
                'email' => 'contact'.($index + 1).'@dnt-seed.example',
                'website' => str_replace('-', '', $business['slug']).'.example',
                'address' => 'Khu vực doanh nghiệp Bắc Ninh',
                'province' => 'Bắc Ninh',
                'district' => $index % 2 === 0 ? 'Bắc Ninh' : 'Từ Sơn',
                'summary' => $business['summary'],
                'description' => $business['summary'].' Doanh nghiệp tham gia kết nối, hợp tác và chia sẻ nguồn lực trong cộng đồng doanh nhân trẻ.',
                'social_links' => json_encode(['linkedin' => null, 'facebook' => null], JSON_UNESCAPED_UNICODE),
                'location' => json_encode(['province' => 'Bắc Ninh'], JSON_UNESCAPED_UNICODE),
                'status' => $status,
                'is_featured' => $status === 'approved' && $index < 8,
                'submitted_by_user_id' => isset($business['applicant']) ? $demoUserIds[$business['applicant']] : null,
                'representative_job_title' => $business['job_title'] ?? ($index % 3 === 0 ? 'Giám đốc điều hành' : 'Giám đốc'),
                'approved_at' => $status === 'approved' ? $now->copy()->subDays(45 - $index) : null,
            ], $now);

            if ($status === 'approved') {
                DB::table('businesses')->where('id', $ids[$business['slug']])->whereNull('membership_code')->update([
                    'membership_code' => 'DNTBN-DN-'.str_pad((string) $ids[$business['slug']], 6, '0', STR_PAD_LEFT),
                ]);
            }

            if ($memberCode) {
                $this->upsert('business_members', [
                    'business_id' => $ids[$business['slug']],
                    'member_id' => $memberIds[$memberCode],
                ], [
                    'role' => 'representative',
                    'job_title' => $business['job_title'] ?? ($index % 3 === 0 ? 'Giám đốc điều hành' : 'Giám đốc'),
                    'is_primary' => true,
                    'status' => 'active',
                    'started_at' => $now->copy()->subMonths(10)->toDateString(),
                    'ended_at' => null,
                ], $now);
            }

            $industrySlugs = array_values(array_unique([
                $business['industry'],
                ...($additionalIndustries[$business['slug']] ?? []),
            ]));

            foreach ($industrySlugs as $industryIndex => $industrySlug) {
                $this->upsert('business_industries', [
                    'business_id' => $ids[$business['slug']],
                    'industry_id' => $industryIds[$industrySlug],
                ], ['is_primary' => $industryIndex === 0], $now);
            }

            $this->seedBusinessStatusHistory($ids[$business['slug']], $status, $now, isset($business['applicant']));
        }

        return $ids;
    }

    private function seedBusinessStatusHistory(int $businessId, string $status, Carbon $now, bool $isApplicationDemo): void
    {
        $steps = $isApplicationDemo
            ? match ($status) {
                'draft' => [['from' => null, 'to' => 'draft', 'reason' => 'Hồ sơ được tạo từ tài khoản minh hoạ và chưa gửi duyệt.']],
                'pending' => [
                    ['from' => null, 'to' => 'draft', 'reason' => 'Hồ sơ được tạo từ tài khoản minh hoạ.'],
                    ['from' => 'draft', 'to' => 'pending', 'reason' => 'Hồ sơ đã được gửi tới Hội để kiểm tra.'],
                ],
                'rejected' => [
                    ['from' => null, 'to' => 'draft', 'reason' => 'Hồ sơ được tạo từ tài khoản minh hoạ.'],
                    ['from' => 'draft', 'to' => 'pending', 'reason' => 'Hồ sơ đã được gửi tới Hội để kiểm tra.'],
                    ['from' => 'pending', 'to' => 'rejected', 'reason' => 'Cần bổ sung bản mô tả năng lực và thông tin xác thực người đại diện.'],
                ],
                default => [
                    ['from' => null, 'to' => 'draft', 'reason' => 'Hồ sơ được tạo từ tài khoản minh hoạ.'],
                    ['from' => 'draft', 'to' => 'pending', 'reason' => 'Hồ sơ đã được gửi tới Hội để kiểm tra.'],
                    ['from' => 'pending', 'to' => 'approved', 'reason' => 'Hồ sơ đã được Hội duyệt; người nộp được công nhận là hội viên đại diện.'],
                ],
            }
        : [[
            'from' => 'pending',
            'to' => 'approved',
            'reason' => 'Khởi tạo dữ liệu danh bạ doanh nghiệp.',
        ]];

        foreach ($steps as $index => $step) {
            $this->upsert('business_status_histories', [
                'business_id' => $businessId,
                'to_status' => $step['to'],
            ], [
                'from_status' => $step['from'],
                'reason' => $step['reason'],
                'metadata' => json_encode(['source' => 'dnt-foundation-seeder', 'demo_application' => $isApplicationDemo], JSON_UNESCAPED_UNICODE),
                'changed_at' => $now->copy()->subDays(count($steps) - $index),
            ], $now, false);
        }
    }

    /** @param array<string, int> $businessIds @param array<string, int> $memberIds @param array<string, int> $categoryIds @param array<string, int> $industryIds */
    private function seedOfferings(array $businessIds, array $memberIds, array $categoryIds, array $industryIds, Carbon $now): void
    {
        $offerings = [
            ['slug' => 'nen-tang-quan-tri-kinh-doanh-kb-one', 'business' => 'cong-nghe-kinh-bac-so', 'member' => 'DNTBN-001', 'category' => 'giai-phap-cong-nghe', 'industry' => 'phan-mem-chuyen-doi-so', 'type' => 'service', 'name' => 'Nền tảng quản trị kinh doanh KB One', 'summary' => 'Quản lý bán hàng, khách hàng và vận hành trên một nền tảng.'],
            ['slug' => 'gia-cong-chi-tiet-co-khi-theo-ban-ve', 'business' => 'co-khi-an-phu-bac-ninh', 'member' => 'DNTBN-002', 'category' => 'san-pham-cong-nghiep', 'industry' => 'co-khi-chinh-xac', 'type' => 'service', 'name' => 'Gia công chi tiết cơ khí theo bản vẽ', 'summary' => 'Gia công linh kiện, đồ gá và chi tiết cơ khí chính xác.'],
            ['slug' => 'tu-dieu-khien-cong-nghiep-viet-thanh', 'business' => 'dien-tu-viet-thanh', 'member' => 'DNTBN-003', 'category' => 'san-pham-cong-nghiep', 'industry' => 'dien-tu-cong-nghiep', 'type' => 'product', 'name' => 'Tủ điều khiển công nghiệp Việt Thành', 'summary' => 'Thiết kế, lắp ráp tủ điều khiển theo yêu cầu dây chuyền.'],
            ['slug' => 'kho-bai-va-hoan-tat-don-hang-kinh-bac', 'business' => 'logistics-dong-do-kinh-bac', 'member' => 'DNTBN-004', 'category' => 'dich-vu-logistics', 'industry' => 'van-tai-kho-bai', 'type' => 'service', 'name' => 'Kho bãi và hoàn tất đơn hàng Kinh Bắc', 'summary' => 'Lưu kho, đóng gói và giao nhận linh hoạt cho doanh nghiệp.'],
            ['slug' => 'thi-cong-nha-xuong-va-ha-tang', 'business' => 'xay-dung-ha-tang-bac-ha', 'member' => 'DNTBN-005', 'category' => 'dich-vu-chuyen-mon', 'industry' => 'xay-dung-dan-dung-cong-nghiep', 'type' => 'service', 'name' => 'Thi công nhà xưởng và hạ tầng', 'summary' => 'Thi công trọn gói nhà xưởng, kho bãi và hạ tầng kỹ thuật.'],
            ['slug' => 'qua-tang-dac-san-kinh-bac', 'business' => 'thuc-pham-sach-vi-kinh-bac', 'member' => 'DNTBN-006', 'category' => 'dich-vu-thuong-mai', 'industry' => 'thuc-pham-do-uong', 'type' => 'product', 'name' => 'Quà tặng đặc sản Kinh Bắc', 'summary' => 'Bộ quà tặng nông sản và thực phẩm chế biến theo mùa.'],
            ['slug' => 'gach-hoan-thien-xanh-dai-phuc', 'business' => 'vat-lieu-xanh-dai-phuc', 'member' => 'DNTBN-007', 'category' => 'san-pham-cong-nghiep', 'industry' => 'vat-lieu-xay-dung', 'type' => 'product', 'name' => 'Vật liệu hoàn thiện xanh Đại Phúc', 'summary' => 'Giải pháp vật liệu hoàn thiện công trình bền vững.'],
            ['slug' => 'nong-san-sach-theo-chuan-truy-xuat', 'business' => 'nong-nghiep-cong-nghe-cao-an-thinh', 'member' => 'DNTBN-008', 'category' => 'dich-vu-thuong-mai', 'industry' => 'nong-nghiep-cong-nghe-cao', 'type' => 'product', 'name' => 'Nông sản sạch theo chuẩn truy xuất', 'summary' => 'Nông sản theo mùa với thông tin truy xuất rõ ràng.'],
            ['slug' => 'ket-noi-kenh-phan-phoi-mien-bac', 'business' => 'thuong-mai-phan-phoi-minh-phat', 'member' => 'DNTBN-009', 'category' => 'dich-vu-thuong-mai', 'industry' => 'phan-phoi-ban-le', 'type' => 'service', 'name' => 'Kết nối kênh phân phối miền Bắc', 'summary' => 'Phát triển kênh bán lẻ và phân phối theo khu vực.'],
            ['slug' => 'dao-tao-ky-nang-so-cho-doanh-nghiep', 'business' => 'giao-duc-cong-nghe-nextstep', 'member' => 'DNTBN-011', 'category' => 'dich-vu-chuyen-mon', 'industry' => 'giao-duc-dao-tao', 'type' => 'service', 'name' => 'Đào tạo kỹ năng số cho doanh nghiệp', 'summary' => 'Chương trình kỹ năng số theo nhu cầu vị trí việc làm.'],
        ];

        foreach ($offerings as $index => $offering) {
            $offeringId = $this->upsert('business_offerings', ['slug' => $offering['slug']], [
                'business_id' => $businessIds[$offering['business']],
                'member_id' => $memberIds[$offering['member']],
                'offering_category_id' => $categoryIds[$offering['category']],
                'type' => $offering['type'],
                'name' => $offering['name'],
                'summary' => $offering['summary'],
                'description' => $offering['summary'].' Nội dung và điều kiện hợp tác được doanh nghiệp cập nhật khi kết nối.',
                'price_label' => 'Liên hệ để nhận báo giá',
                'unit' => $offering['type'] === 'product' ? 'sản phẩm' : 'gói dịch vụ',
                'status' => 'published',
                'is_featured' => $index < 6,
                'published_at' => $now->copy()->subDays(30 - $index),
                'expires_at' => null,
            ], $now);

            $this->upsert('offering_industries', [
                'business_offering_id' => $offeringId,
                'industry_id' => $industryIds[$offering['industry']],
            ], [], $now);
        }
    }

    private function seedEvents(Carbon $now): void
    {
        // Chuẩn hoá khoá của bản nháp seeder ban đầu, không tạo sự kiện trùng khi chạy lại.
        DB::table('events')
            ->where('slug', 'toạ-dam-chuyen-doi-so-doanh-nghiep-vua-va-nho')
            ->update(['slug' => 'toa-dam-chuyen-doi-so-doanh-nghiep-vua-va-nho', 'updated_at' => $now]);

        $day = $now->copy()->startOfDay();
        $events = [
            ['slug' => 'ket-noi-cung-cau-cong-nghiep-ho-tro-2026', 'title' => 'Kết nối cung cầu công nghiệp hỗ trợ 2026', 'type' => 'networking', 'summary' => 'Phiên kết nối giữa doanh nghiệp sản xuất, công nghiệp hỗ trợ và đơn vị thu mua.', 'venue_name' => 'Trung tâm Văn hoá Kinh Bắc', 'starts_at' => $day->copy()->addDays(10)->setTime(8, 30), 'ends_at' => $day->copy()->addDays(10)->setTime(11, 30), 'capacity' => 180],
            ['slug' => 'toa-dam-chuyen-doi-so-doanh-nghiep-vua-va-nho', 'title' => 'Toạ đàm chuyển đổi số cho doanh nghiệp vừa và nhỏ', 'type' => 'seminar', 'summary' => 'Chia sẻ lộ trình chuyển đổi số có thể triển khai theo từng quy mô doanh nghiệp.', 'venue_name' => 'Không gian sáng tạo Bắc Ninh', 'starts_at' => $day->copy()->addDays(24)->setTime(14, 0), 'ends_at' => $day->copy()->addDays(24)->setTime(16, 30), 'capacity' => 120],
            ['slug' => 'giao-thuong-doanh-nghiep-tre-vung-kinh-bac', 'title' => 'Ngày giao thương doanh nghiệp trẻ vùng Kinh Bắc', 'type' => 'trade_fair', 'summary' => 'Giới thiệu sản phẩm, kết nối đối tác và mở rộng cơ hội hợp tác liên vùng.', 'venue_name' => 'Trung tâm Hội nghị tỉnh Bắc Ninh', 'starts_at' => $day->copy()->addDays(45)->setTime(8, 0), 'ends_at' => $day->copy()->addDays(45)->setTime(17, 0), 'capacity' => 350],
        ];

        foreach ($events as $event) {
            $this->upsert('events', ['slug' => $event['slug']], [
                ...$event,
                'content' => $event['summary'].' Sự kiện dành cho doanh nghiệp quan tâm đến kết nối, hợp tác và phát triển thị trường.',
                'venue_address' => 'Bắc Ninh',
                'location' => json_encode(['province' => 'Bắc Ninh'], JSON_UNESCAPED_UNICODE),
                'registration_opens_at' => $now,
                'registration_closes_at' => $event['starts_at']->copy()->subDays(2),
                'status' => 'published',
                'visibility' => 'public',
                'settings' => json_encode(['registration_required' => true], JSON_UNESCAPED_UNICODE),
                'published_at' => $now->copy()->subDays(7),
            ], $now);
        }
    }

    /** @param array<string, int> $businessIds @param array<string, int> $memberIds @param array<string, int> $industryIds */
    private function seedTradePosts(array $businessIds, array $memberIds, array $industryIds, Carbon $now): void
    {
        $posts = [
            ['slug' => 'can-mua-vat-tu-co-khi-gia-cong', 'business' => 'co-khi-an-phu-bac-ninh', 'member' => 'DNTBN-002', 'industry' => 'co-khi-chinh-xac', 'type' => 'buy', 'title' => 'Tìm đối tác cung ứng vật tư cơ khí gia công', 'summary' => 'Tìm đối tác cung ứng thép tấm, nhôm và vật tư gia công theo lô định kỳ.', 'budget_label' => 'Theo báo giá và năng lực cung ứng'],
            ['slug' => 'cung-cap-giai-phap-quan-tri-cho-doanh-nghiep', 'business' => 'cong-nghe-kinh-bac-so', 'member' => 'DNTBN-001', 'industry' => 'phan-mem-chuyen-doi-so', 'type' => 'sell', 'title' => 'Cung cấp giải pháp quản trị cho doanh nghiệp', 'summary' => 'Tư vấn và triển khai phần mềm quản trị phù hợp doanh nghiệp vừa và nhỏ.', 'budget_label' => 'Gói triển khai linh hoạt'],
            ['slug' => 'hop-tac-phan-phoi-nong-san-sach', 'business' => 'nong-nghiep-cong-nghe-cao-an-thinh', 'member' => 'DNTBN-008', 'industry' => 'nong-nghiep-cong-nghe-cao', 'type' => 'cooperate', 'title' => 'Tìm đối tác hợp tác phân phối nông sản sạch', 'summary' => 'Mời đối tác bán lẻ và phân phối cùng phát triển thị trường nông sản truy xuất.', 'budget_label' => 'Hợp tác theo khu vực'],
            ['slug' => 'can-tim-doi-tac-van-tai-noi-dia', 'business' => 'logistics-dong-do-kinh-bac', 'member' => 'DNTBN-004', 'industry' => 'van-tai-kho-bai', 'type' => 'cooperate', 'title' => 'Cần tìm đối tác vận tải nội địa', 'summary' => 'Kết nối đơn vị vận tải có năng lực giao nhận tuyến Bắc Ninh – Hà Nội – Hải Phòng.', 'budget_label' => 'Theo sản lượng thực tế'],
            ['slug' => 'cung-cap-vat-lieu-hoan-thien-cong-trinh', 'business' => 'vat-lieu-xanh-dai-phuc', 'member' => 'DNTBN-007', 'industry' => 'vat-lieu-xay-dung', 'type' => 'sell', 'title' => 'Cung cấp vật liệu hoàn thiện công trình', 'summary' => 'Giải pháp vật liệu hoàn thiện thân thiện môi trường cho công trình mới.', 'budget_label' => 'Theo hạng mục công trình'],
        ];

        foreach ($posts as $post) {
            $postId = $this->upsert('trade_posts', ['slug' => $post['slug']], [
                'member_id' => $memberIds[$post['member']],
                'business_id' => $businessIds[$post['business']],
                'type' => $post['type'],
                'title' => $post['title'],
                'summary' => $post['summary'],
                'content' => $post['summary'].' Ưu tiên đối tác có thông tin minh bạch và khả năng hợp tác lâu dài.',
                'budget_label' => $post['budget_label'],
                'location_label' => 'Bắc Ninh và khu vực phía Bắc',
                'contact_name' => 'Ban kết nối doanh nghiệp',
                'contact_phone' => '0200 000 1999',
                'contact_email' => 'ketnoi@dnt-seed.example',
                'status' => 'approved',
                'approved_at' => $now->copy()->subDays(5),
                'expires_at' => $now->copy()->addDays(60),
                'closed_at' => null,
            ], $now);

            $this->upsert('trade_post_industries', [
                'trade_post_id' => $postId,
                'industry_id' => $industryIds[$post['industry']],
            ], [], $now);
        }
    }

    private function seedIntros(Carbon $now): void
    {
        $intros = [
            [
                'slug' => 'gioi-thieu-hoi',
                'title' => 'Đồng hành cùng doanh nghiệp trẻ Bắc Ninh',
                'summary' => 'Hội là không gian kết nối để doanh nghiệp trẻ chia sẻ năng lực, cơ hội và trách nhiệm với cộng đồng.',
                'content' => '<p>Hội Doanh nhân trẻ tỉnh Bắc Ninh hướng tới một cộng đồng doanh nghiệp trẻ năng động, minh bạch và có khả năng cùng nhau tạo ra các cơ hội phát triển bền vững.</p>',
            ],
            [
                'slug' => 'co-cau-to-chuc-hoi',
                'title' => 'Cơ cấu, tổ chức của Hội',
                'summary' => 'Cơ cấu vận hành của Hội và các tổ chức trực thuộc.',
                'content' => '<ol><li><strong>Đại hội</strong>: Cơ quan lãnh đạo cao nhất của Hội, nhiệm kỳ 03 năm một lần.</li><li><strong>Ban Chấp hành Hội</strong>: Cơ quan lãnh đạo của Hội giữa hai kỳ Đại hội.</li><li><strong>Ban Kiểm tra Hội</strong>: Hoạt động độc lập để kiểm tra, giám sát việc chấp hành Điều lệ và tài chính Hội.</li><li><strong>Ban Thường vụ Hội</strong>: Cơ quan thường trực lãnh đạo giữa hai kỳ họp Ban Chấp hành.</li><li><strong>Văn phòng và 09 Ban chuyên môn</strong>: Văn phòng Hội; Ban Cố vấn; Ban Truyền thông; Ban Vận động chính sách; Ban Quan hệ quốc tế; Ban Xúc tiến thương mại; Ban Hội viên; Ban Quan hệ cộng đồng; Ban Thi đua khen thưởng; Ban Thể thao.</li><li><strong>Các tổ chức thuộc Hội</strong>: 09 Chi hội trực thuộc và các câu lạc bộ.<ul><li>Chi hội Bắc Giang - Yên Dũng</li><li>Chi hội Hiệp Hòa - Việt Yên</li><li>Chi hội Yên Thế - Lạng Giang - Tân Yên</li><li>Chi hội Lục Ngạn - Lục Nam - Sơn Động</li><li>Chi hội Kinh Bắc</li><li>Chi hội Nam Đuống</li><li>Chi hội Từ Sơn</li><li>Chi hội Tiên Du</li><li>Chi hội Yên Phong</li></ul></li></ol>',
            ],
        ];

        foreach ($intros as $sortOrder => $intro) {
            $this->upsert('intros', ['slug' => $intro['slug']], [
                ...$intro,
                'is_active' => true,
                'sort_order' => ($sortOrder + 1) * 10,
            ], $now);
        }
    }

    private function seedNews(): void
    {
        $locale = app()->getLocale();
        app()->setLocale('vi');

        try {
            $categories = [
                ['slug' => 'tin-tuc-hoat-dong', 'name' => 'Tin tức & hoạt động', 'description' => 'Hoạt động kết nối và phát triển doanh nghiệp trẻ.'],
                ['slug' => 'ket-noi-doanh-nghiep', 'name' => 'Kết nối doanh nghiệp', 'description' => 'Thông tin hợp tác, chia sẻ nguồn lực và thị trường.'],
                ['slug' => 'giao-thuong-hop-tac', 'name' => 'Giao thương & hợp tác', 'description' => 'Cơ hội giao thương trong cộng đồng doanh nghiệp.'],
            ];

            $categoryIds = [];
            foreach ($categories as $sortOrder => $categoryData) {
                $category = PostCategory::query()
                    ->whereHas('slugs', fn ($query) => $query->where('locale', 'vi')->where('slug', $categoryData['slug']))
                    ->firstOrNew();

                $category->fill([
                    'name' => ['vi' => $categoryData['name']],
                    'description' => ['vi' => $categoryData['description']],
                    'seo_title' => ['vi' => $categoryData['name'].' | DNT Bắc Ninh'],
                    'seo_description' => ['vi' => $categoryData['description']],
                    'sort_order' => ($sortOrder + 1) * 10,
                    'is_home' => true,
                    'is_active' => true,
                ]);
                $category->setSlugOverride($categoryData['slug'])->save();
                $categoryIds[$categoryData['slug']] = $category->id;
            }

            $posts = [
                ['slug' => 'khoi-dong-chuong-trinh-ket-noi-doanh-nghiep-2026', 'category' => 'tin-tuc-hoat-dong', 'title' => 'Khởi động chương trình kết nối doanh nghiệp trẻ năm 2026', 'summary' => 'Chương trình hướng tới chia sẻ nguồn lực, mở rộng đối tác và đồng hành cùng doanh nghiệp trẻ Bắc Ninh.', 'featured' => true],
                ['slug' => 'ket-noi-cung-cau-cong-nghiep-ho-tro', 'category' => 'ket-noi-doanh-nghiep', 'title' => 'Kết nối cung cầu trong lĩnh vực công nghiệp hỗ trợ', 'summary' => 'Các doanh nghiệp sản xuất cùng trao đổi nhu cầu nguyên vật liệu, gia công và phát triển chuỗi cung ứng.', 'featured' => true],
                ['slug' => 'chia-se-kinh-nghiem-chuyen-doi-so', 'category' => 'tin-tuc-hoat-dong', 'title' => 'Doanh nghiệp trẻ chia sẻ kinh nghiệm chuyển đổi số', 'summary' => 'Những bài học thực tiễn về ứng dụng công nghệ trong vận hành, bán hàng và chăm sóc khách hàng.', 'featured' => false],
                ['slug' => 'mo-rong-chuoi-cung-ung-xanh', 'category' => 'giao-thuong-hop-tac', 'title' => 'Mở rộng chuỗi cung ứng xanh từ Bắc Ninh', 'summary' => 'Cộng đồng doanh nghiệp cùng tìm kiếm đối tác có định hướng sản xuất, phân phối và tiêu dùng bền vững.', 'featured' => false],
            ];

            foreach ($posts as $index => $postData) {
                $post = Post::withTrashed()
                    ->whereHas('slugs', fn ($query) => $query->where('locale', 'vi')->where('slug', $postData['slug']))
                    ->firstOrNew();

                $post->fill([
                    'post_category_id' => $categoryIds[$postData['category']],
                    'name' => ['vi' => $postData['title']],
                    'summary' => ['vi' => $postData['summary']],
                    'content' => ['vi' => '<p>'.$postData['summary'].'</p><p>Nội dung được khởi tạo để vận hành trang tin của cổng thông tin DNT Bắc Ninh.</p>'],
                    'seo_title' => ['vi' => $postData['title'].' | DNT Bắc Ninh'],
                    'seo_description' => ['vi' => $postData['summary']],
                    'seo_keywords' => ['vi' => 'doanh nhân trẻ Bắc Ninh, kết nối doanh nghiệp, giao thương'],
                    'is_featured' => $postData['featured'],
                    'is_active' => true,
                    'view_count' => 0,
                    'published_at' => now()->subDays(10 - $index),
                    'deleted_at' => null,
                ]);
                $post->setSlugOverride($postData['slug'])->save();
            }
        } finally {
            app()->setLocale($locale);
        }
    }

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $values
     */
    private function upsert(string $table, array $identity, array $values, Carbon $now, bool $timestamps = true): int
    {
        $query = DB::table($table)->where($identity);
        $existing = $query->first(['id']);

        if ($existing) {
            $update = $values;
            if ($timestamps) {
                $update['updated_at'] = $now;
            }
            DB::table($table)->where('id', $existing->id)->update($update);

            return (int) $existing->id;
        }

        $insert = [...$identity, ...$values];
        if ($timestamps) {
            $insert['created_at'] = $now;
            $insert['updated_at'] = $now;
        }

        return (int) DB::table($table)->insertGetId($insert);
    }
}
