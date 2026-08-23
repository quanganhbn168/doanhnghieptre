<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('homepage.homepage_member_benefit_title', [
            'vi' => 'Lợi ích dành cho hội viên',
        ]);

        $this->migrator->add('homepage.homepage_member_benefits', [
            [
                'icon' => 'fa-solid fa-people-group',
                'title' => 'Mở rộng mạng lưới',
                'description' => 'Kết nối với cộng đồng doanh nhân trẻ uy tín tại Bắc Ninh và toàn quốc.',
            ],
            [
                'icon' => 'fa-solid fa-tower-broadcast',
                'title' => 'Nâng tầm thương hiệu',
                'description' => 'Quảng bá doanh nghiệp trên nền tảng truyền thông của Hội.',
            ],
            [
                'icon' => 'fa-solid fa-graduation-cap',
                'title' => 'Đào tạo & phát triển',
                'description' => 'Tham gia các chương trình đào tạo kỹ năng, quản trị và chuyên đề.',
            ],
            [
                'icon' => 'fa-solid fa-calendar-days',
                'title' => 'Tham gia sự kiện',
                'description' => 'Ưu tiên tham dự hội thảo, tọa đàm, sự kiện thương mại và giao lưu kết nối.',
            ],
            [
                'icon' => 'fa-solid fa-chart-line',
                'title' => 'Cơ hội giao thương',
                'description' => 'Tiếp cận cơ hội kinh doanh, kết nối cung cầu và hợp tác hiệu quả.',
            ],
            [
                'icon' => 'fa-solid fa-hand-holding-heart',
                'title' => 'Hỗ trợ doanh nghiệp',
                'description' => 'Tư vấn pháp lý, tài chính, chuyển đổi số và các chính sách hỗ trợ.',
            ],
        ]);
    }
};
