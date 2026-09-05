# Đăng ký và quản lý hội viên doanh nghiệp

## Luồng nghiệp vụ

1. Doanh nghiệp nộp hồ sơ trực tiếp tại `/dang-ky-hoi-vien`, kèm đơn đã ký/đóng dấu theo mẫu đang sử dụng.
2. Người đại diện đặt email/mật khẩu ngay trong hồ sơ, được đăng nhập để theo dõi tại `/tai-khoan`; không phải chờ duyệt tài khoản. Người đã có thông tin đăng nhập dùng lại tài khoản hiện có.
3. Cán bộ Hội kiểm tra hồ sơ, chọn Chi hội tiếp nhận và bấm **Hội duyệt và chuyển Chi hội**.
4. Cán bộ được phân công đúng Chi hội bấm **Chi hội tiếp nhận**. Doanh nghiệp mới trở thành hội viên chính thức, được cấp mã `DNTBN-DN-xxxxxx`, xuất hiện trên danh bạ và sử dụng cổng `/thanh-vien`.
5. Hội hoặc Chi hội có thể **Yêu cầu bổ sung**, bắt buộc ghi lý do. Người đại diện sửa hồ sơ rồi gửi lại từ bước Hội duyệt.

Tư cách và mã hội viên thuộc `Business`. Bảng `members` hiện có được giữ để quản lý người đại diện và tương thích các quan hệ giao thương/sự kiện. Hồ sơ doanh nghiệp đã duyệt từ trước được giữ trạng thái, bổ sung mã doanh nghiệp theo ID; không tạo giả lịch sử duyệt hai cấp.

## Panel và quyền

- `/hoi`: panel Filament chung cho Hội và Chi hội, có dashboard, hồ sơ/chi tiết/lịch sử, danh mục Chi hội và quản lý cán bộ.
- `association_manager`: quyền `association.review`, `association.manage`; xét duyệt tất cả hồ sơ và quản lý Chi hội, cán bộ.
- `chapter_manager`: quyền `chapters.receive`; chỉ thấy hồ sơ/đơn thuộc Chi hội đang hoạt động được phân công, sau bước Hội duyệt. Không truy cập panel quản trị website hoặc quản lý cán bộ.
- `super_admin`/`admin`: có thể vào panel Hội để quản lý tổ chức, phân công. Bước tiếp nhận vẫn cần quyền Chi hội và phân công đúng Chi hội; không có thao tác bỏ qua quy trình.

Tại **Cán bộ & phân quyền**, tạo cán bộ, chọn vai trò và Chi hội được phân công. Có thể giao một cán bộ nhiều Chi hội hoặc cả hai vai trò. Không sửa chính tài khoản đang đăng nhập tại màn hình này; tài khoản quản trị hệ thống không nằm trong danh sách cán bộ có thể sửa.

Hồ sơ, action, thống kê, URL chi tiết và tải đơn đều kiểm tra phạm vi. Đơn đã ký nằm trên disk `local`, không đưa ra thư mục công khai. Thao tác duyệt khóa bản ghi trong transaction và kiểm tra lại trạng thái để tránh duyệt trùng hoặc dùng màn hình cũ.

## Triển khai trên cơ sở dữ liệu hiện có

```sh
git pull --ff-only dnt main
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan filament:optimize-clear
php artisan migrate --force
php artisan db:seed --class=AssociationRoleSeeder --force
pnpm install --frozen-lockfile
pnpm run build
php artisan filament:optimize
```

Không chạy `migrate:fresh` hay seed lại tài khoản quản trị trên dữ liệu đang sử dụng. Migration chỉ gỡ bước chờ duyệt cho người đăng ký cũ đang `pending`, không có vai trò/quyền cán bộ; tài khoản bị từ chối và tài khoản có quyền quản trị giữ nguyên.

## Kiểm thử

`php artisan test` dùng database riêng `doanhnhantre_testing` (MySQL), không dùng database chạy website. Các test hồ sơ sử dụng disk giả cho file tải lên và kiểm tra HTTP/Livewire cho nộp đơn, duyệt hai cấp, bổ sung, quyền theo Chi hội, chống duyệt trùng và tạo/sửa cán bộ.
