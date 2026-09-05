# Đăng ký và quản lý hội viên doanh nghiệp

## Quy trình ba cấp

1. Doanh nghiệp nộp hồ sơ trực tiếp tại `/dang-ky-hoi-vien`: thông tin người đại diện, pháp lý doanh nghiệp, **một Chi hội**, **từ 1 đến 5 khối ngành nghề** và đơn đã ký/đóng dấu. Chưa tạo tài khoản, không yêu cầu mật khẩu.
2. **Văn phòng Hội** kiểm tra hồ sơ hợp lệ, xác nhận Chi hội và chuyển thẩm định.
3. **Chi hội trưởng** được phân công đúng Chi hội xác minh, thẩm định và đề xuất chuẩn y.
4. **Trưởng ban Hội viên** chuẩn y cuối cùng. Lúc này mới cấp mã `DNTBN-DN-xxxxxx`, công khai doanh nghiệp trên danh bạ và cấp tài khoản cho người đại diện.

Tư cách hội viên thuộc `Business`, duy nhất theo mã số thuế và mã hội viên. Một người đại diện có thể quản lý nhiều doanh nghiệp; mỗi doanh nghiệp vẫn có hồ sơ, Chi hội và mã hội viên riêng. Bảng `members` hiện có tiếp tục dùng cho người đại diện và quan hệ với sự kiện/giao thương.

Các trạng thái: `draft` → `pending` → `chapter_pending` → `board_pending` → `approved`. Tại cấp đang xử lý, cán bộ có thể **Yêu cầu bổ sung** (`changes_requested`) hoặc **Từ chối kết nạp** (`rejected`), bắt buộc ghi lý do. Hồ sơ bổ sung gửi lại từ bước Văn phòng kiểm tra; hồ sơ từ chối kết nạp không được tự mở lại qua liên kết sửa.

Thao tác duyệt khóa bản ghi trong transaction, kiểm tra đúng cấp và phân công Chi hội. Chuẩn y kiểm tra lại đơn, Chi hội đang hoạt động, kết quả hai cấp trước và giới hạn khối ngành nghề. Thông tin công khai chỉ lấy hồ sơ đã chuẩn y.

## Theo dõi và cấp tài khoản

- Sau khi nộp, ứng viên nhận mã hồ sơ và liên kết riêng `/ho-so-gia-nhap/{business}` có chữ ký, hiệu lực 30 ngày. Liên kết này cho xem tiến độ, lịch sử, lý do phản hồi và bổ sung hồ sơ khi được yêu cầu.
- `/tra-cuu-ho-so` cho xin lại liên kết bằng mã hồ sơ cùng email người đại diện. Hệ thống trả cùng một thông báo dù thông tin có khớp hay không; giới hạn số lần gửi.
- Các trang riêng không lưu cache, không lập chỉ mục và không truyền URL hồ sơ qua Referer. Không chia sẻ liên kết theo dõi cho người khác.
- Khi chuẩn y, tài khoản mới nhận email đăng nhập và liên kết thiết lập mật khẩu dùng một lần qua password broker hiện có. Không gửi mật khẩu rõ. Nếu liên kết hết hạn, dùng Quên mật khẩu.
- Người đại diện đã có tài khoản hội viên hợp lệ được dùng lại tài khoản, không đổi mật khẩu. Email trùng tài khoản cán bộ hoặc tài khoản không được phép đăng nhập cần Văn phòng xử lý trước khi chuẩn y.
- Tài khoản và hồ sơ đã duyệt từ trước được giữ nguyên. Tài khoản người đăng ký cũ tiếp tục dùng luồng theo dõi hiện có. Sau khi có tài khoản, email theo dõi là email đăng nhập hiện tại.

Đơn đã ký nằm trên disk `local`, không ở thư mục công khai. Chỉ người có quyền xem hồ sơ hoặc người giữ liên kết tải có chữ ký còn hạn mới tải được. Chỉ nhận **PDF, DOC, DOCX tối đa 10 MB**, kiểm tra đuôi và MIME từ nội dung tệp. Khi bổ sung có thể giữ đơn cũ; đơn thay thế phải qua cùng kiểm tra.

## Panel và quyền

`/hoi` dùng chung panel Filament hiện có, phân quyền theo vai trò; resource mới có `Pages`, `Schemas`, `Tables` riêng.

| Vai trò | Quyền nghiệp vụ |
| --- | --- |
| `association_manager` — Văn phòng Hội | Tiếp nhận hồ sơ; duyệt cập nhật hồ sơ đã chuẩn y; quản lý Chi hội, cán bộ; quản trị và kiểm duyệt nội dung |
| `chapter_manager` — Chi hội trưởng | Xem hồ sơ thuộc Chi hội đang hoạt động được phân công, từ bước thẩm định; đề xuất chuẩn y |
| `membership_head` — Trưởng ban Hội viên | Xem hồ sơ và chuẩn y cuối cùng; không có quyền quản trị nội dung/cán bộ mặc định |
| `communications_manager` — Ban Truyền thông | Quản lý tin tức, sự kiện, thông tin Hội, thư viện ảnh; duyệt bài hội viên và tin giao thương; không xem đơn hoặc quản lý cán bộ |

Tại **Cán bộ & phân quyền** (`/hoi/can-bo`), giao vai trò và Chi hội cho cán bộ thực tế. Migration bổ sung vai trò, **không tự giao vai trò Trưởng ban cho cán bộ cũ**. Có thể giao nhiều vai trò nếu tổ chức cần kiêm nhiệm. Quản trị hệ thống có quyền Văn phòng/Trưởng ban; bước Chi hội vẫn yêu cầu phân công Chi hội, không có action bỏ qua cấp.

`/thanh-vien` là panel hội viên đã có, được bổ sung:

- **Hồ sơ cá nhân**: sửa tên, điện thoại, mật khẩu; đổi email đăng nhập cần xác nhận qua email mới.
- **Doanh nghiệp của tôi**: bản cập nhật nằm trong `pending_profile`, có phiên bản kiểm tra. Trong lúc chờ, hồ sơ cũ, logo, khối ngành nghề và tư cách hội viên vẫn công khai bình thường. Văn phòng duyệt mới áp dụng; yêu cầu sửa phải được doanh nghiệp gửi lại trước khi duyệt.
- **Bài viết doanh nghiệp** (`/thanh-vien/bai-viet`): gửi giới thiệu sản phẩm/dịch vụ hoặc tin doanh nghiệp. Dùng bảng `posts` hiện có, mặc định chờ duyệt; Văn phòng/Ban Truyền thông duyệt tại `/hoi/bai-viet-hoi-vien` hoặc phản hồi lý do cần sửa.
- **Tin giao thương**: tiếp tục dùng chức năng đăng nhu cầu/cung cấp hiện có, với quyền kiểm duyệt cho Văn phòng/Ban Truyền thông. Tin công khai phải đã duyệt, còn hạn và thuộc hội viên chính thức.

Bài viết/tin giao thương sửa lại phải chờ duyệt lại trước khi hiển thị. Bài chính thức của Hội tiếp tục dùng CMS tin tức hiện có. Sự kiện, tài liệu và thông tin Hội không bị tạo thêm nguồn dữ liệu trùng.

## Nâng cấp dữ liệu và triển khai

Hai migration ngày `2026_09_05` bổ sung thông tin thẩm định cấp Chi hội, mã/email hồ sơ, dữ liệu chờ duyệt bài viết và bản cập nhật doanh nghiệp. Trạng thái `rejected` cũ vốn là yêu cầu bổ sung được chuyển sang `changes_requested`, kể cả lịch sử. Hội viên đã chuẩn y, tài khoản, nội dung và tệp hiện có được giữ nguyên; không tạo giả lịch sử ba cấp.

Sao lưu database và uploads theo quy trình vận hành, sau đó chạy trong thư mục ứng dụng:

```sh
git pull --ff-only dnt main
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan filament:optimize-clear
php artisan migrate --force
pnpm install --frozen-lockfile
pnpm run build
php artisan filament:optimize
php artisan queue:restart
```

Không chạy `migrate:fresh`. Migration đã đồng bộ `AssociationRoleSeeder`. Hai migration này không xóa dữ liệu khi rollback; nếu cần hạ phiên bản, dùng bản sao lưu theo kế hoạch riêng.

Email thông báo tiến độ, mời thiết lập mật khẩu và xác nhận đổi email cần cấu hình mailer thật, địa chỉ gửi phù hợp và `APP_URL` đúng tên miền HTTPS. Local đang dùng `MAIL_MAILER=log`, nên chưa có bằng chứng email tới hộp thư thật. `QUEUE_CONNECTION=database` cần worker được Supervisor/systemd hoặc dịch vụ tương đương giữ hoạt động:

```sh
php artisan queue:work --tries=3 --backoff=60
```

Không chạy worker như một lệnh ngắn trong phiên deploy rồi đóng terminal. Kiểm tra `php artisan queue:failed` khi vận hành; chỉ thử gửi tới hộp thư được phép sử dụng.

## Kiểm thử

Test dùng database riêng `doanhnhantre_testing`, disk tải lên giả và notification giả, không gửi email thật. Bộ kiểm tra liên quan:

```sh
php artisan test --filter='BusinessDirectoryTest|BusinessMembershipWorkflowTest|ThreeStageMembershipTest|MembershipApplicationUploadTest|DntAssociationContentTest|ProfessionalGroupImportTest|AssociationMarketplaceTest|AssociationPublicationPagesTest'
node --test tests/Frontend/membership-application-upload.test.mjs
```

Bao gồm nộp đơn không tạo tài khoản, đủ ba cấp, giới hạn ngành/Chi hội, quyền và tải đơn, liên kết riêng hết hạn, bổ sung/từ chối, chống duyệt lặp, dùng lại tài khoản, đặt mật khẩu một lần, hồ sơ cá nhân, bản cập nhật doanh nghiệp và bài hội viên chờ duyệt.
