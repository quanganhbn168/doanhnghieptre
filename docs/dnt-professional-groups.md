# Danh mục khối ngành nghề DNT Bắc Ninh

Nguồn đối chiếu ngày 05/09/2026: [Cơ cấu trên trang chủ](https://doanhnhantrebacninh.com/#co-cau) và [bộ lọc khối ngành nghề trong danh bạ](https://doanhnhantrebacninh.com/danh-ba).

Bản dữ liệu được lưu tại `database/data/dnt-professional-groups.json`, gồm 17 khối với tên, slug, thứ tự 01–17 và mô tả nếu nguồn có cung cấp. Nguồn có mô tả bổ sung cho khối bất động sản; không có ảnh minh họa riêng cho từng khối. Số lượng hội viên và hồ sơ doanh nghiệp trên nguồn không được nhập.

## Quản trị

- Panel Hội: `/hoi/khoi-nganh-nghe`, nhóm **Tổ chức Hội**. Cán bộ có quyền quản lý Hội được quản trị; cán bộ chi hội không được sửa danh mục chung.
- Panel admin: `/admin/professional-groups`, nhóm **Quản lý Hội**. Hai panel dùng chung dữ liệu và schema/table.
- Sửa tên, đường dẫn, mô tả, ảnh, trạng thái và lựa chọn hiển thị trang chủ. Kéo thả để sắp xếp.
- Khối đang sử dụng xuất hiện trong hồ sơ hội viên và bộ lọc danh bạ. Trang chủ chỉ lấy khối đang sử dụng và được bật hiển thị trang chủ, theo thứ tự quản trị; số doanh nghiệp được tính từ hội viên chính thức trong hệ thống này.

## Nhập vào hệ thống đã có dữ liệu

Sau khi cập nhật code và sao lưu cơ sở dữ liệu, chạy:

```sh
php artisan migrate --force
php artisan db:seed --class=DntProfessionalGroupSeeder --force
pnpm run build
php artisan optimize:clear
php artisan view:cache
```

Seeder riêng chỉ bổ sung danh mục nguồn và ngừng dùng 18 nhóm mẫu cũ làm khối ngành nghề. Các bản ghi cũ và liên kết tới doanh nghiệp, sản phẩm, tin giao thương được giữ nguyên, không tự gán sang khối mới. Doanh nghiệp hoặc cán bộ cần chọn lại khối phù hợp khi cập nhật hồ sơ. Bộ chọn hiển thị phân loại cũ để đối chiếu và yêu cầu chọn khối đang sử dụng.

Nhập lại nhận diện bản ghi bằng URL nguồn, giữ nguyên ID và các chỉnh sửa trong CMS, kể cả khi đã đổi slug hoặc tắt hiển thị. Nhóm do Hội bổ sung ngoài danh mục mẫu cũ không bị thay đổi. Không cần chạy lại toàn bộ `DntFoundationSeeder` để nhập danh mục này.
