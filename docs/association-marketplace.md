# Panel doanh nghiệp, chợ và nội dung Hội

## Địa chỉ và quyền

- `/thanh-vien`: doanh nghiệp hội viên chính thức quản lý hồ sơ và tin giao thương; người đại diện chỉ thấy tin của doanh nghiệp mình đại diện.
- `/giao-thuong`: chợ công khai, tìm theo tên/nội dung/doanh nghiệp, lọc nhu cầu và phân trang.
- `/hoi/cho-doanh-nghiep`: Hội xem nội dung và duyệt, yêu cầu bổ sung hoặc đóng tin.
- `/hoi/tin-tuc`, `/hoi/chuyen-muc`: biên tập, xuất bản/ẩn và đặt lịch tin tức; dùng chung Post/PostCategory, schemas, tables và services với CMS hiện có.
- `/hoi/thong-tin-hoi`: các trang giới thiệu/cơ cấu tổ chức của Hội, kéo thả thứ tự.
- `/hoi/su-kien`: tạo và quản lý sự kiện, xem người đăng ký, điểm danh, hủy đăng ký.
- `/hoi/media`: thư viện ảnh cho nội dung Hội.

`association_manager` (Văn phòng) và `communications_manager` (Ban Truyền thông) dùng chức năng nội dung qua quyền `content.moderate`. Danh sách đăng ký sự kiện và điểm danh vẫn cần quyền tổ chức `association.manage`. `chapter_manager` chỉ thẩm định hội viên của Chi hội được phân công; `membership_head` chuẩn y kết nạp. Hai vai trò này không quản trị nội dung mặc định. Super admin và admin giữ quyền quản lý Hội.

Bài viết giới thiệu sản phẩm/dịch vụ của hội viên được gửi tại `/thanh-vien/bai-viet`, kiểm duyệt tại `/hoi/bai-viet-hoi-vien`. Các bài này dùng chung bảng `posts` nhưng có doanh nghiệp sở hữu và trạng thái duyệt riêng. Xem đầy đủ quy trình ba cấp, cập nhật hồ sơ và cấp tài khoản trong [membership-workflow.md](membership-workflow.md).

## Chợ cơ bản

Giữ mã dữ liệu `buy`, `sell`, `cooperate` để không đổi tin cũ; nhãn lần lượt là Cần tìm dịch vụ / sản phẩm, Cung cấp dịch vụ / sản phẩm, Mời hợp tác. Mỗi tin có doanh nghiệp, nội dung, khu vực, giá/ngân sách tham khảo và liên hệ điện thoại/email. Không có thanh toán hay đơn hàng.

Tin mới hoặc sửa tin -> chờ Hội duyệt -> công khai. Tin được yêu cầu bổ sung hiển thị lý do cho doanh nghiệp. Đóng tin sẽ ẩn công khai; mở/gửi lại phải qua duyệt. Tin hết hạn, đã xóa hoặc có doanh nghiệp chưa được duyệt không hiển thị. Quyền sở hữu và trạng thái được kiểm tra lại trong giao dịch khi lưu/duyệt.

## Sự kiện

Sự kiện dùng bảng events và event_registrations đang có, không tạo bảng trùng. Trang `/su-kien/{slug}` có chương trình, thời gian, địa điểm và form đăng ký. Chỉ sự kiện công khai, đã xuất bản đến hạn mới hiện. Chỉ nhận đăng ký trong thời gian cho phép và trước khi sự kiện bắt đầu. Sức chứa tính cả người đi cùng; việc đăng ký, hủy và đổi sức chứa khóa cùng bản ghi sự kiện để tránh vượt số chỗ. Không xóa sự kiện/đăng ký qua panel; dùng trạng thái hủy để giữ hồ sơ.

## Cập nhật và kiểm tra

Phần chợ tiếp tục dùng dữ liệu hiện có. Bản nâng cấp ngày 05/09/2026 có migration cho duyệt ba cấp, bài viết hội viên và bản cập nhật hồ sơ; triển khai theo `docs/membership-workflow.md`. Không chạy `migrate:fresh` trên dữ liệu đang dùng.

```sh
php artisan migrate --force
php artisan db:seed --class=AssociationRoleSeeder --force
pnpm install --frozen-lockfile
pnpm run build
php artisan optimize:clear
```

Kiểm thử bằng database riêng `doanhnhantre_testing` trong phpunit.xml:

```sh
php vendor/phpunit/phpunit/phpunit --filter='AssociationMarketplaceTest|BusinessMembershipWorkflowTest|DntAssociationContentTest'
```
