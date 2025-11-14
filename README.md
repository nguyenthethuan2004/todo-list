# Todo List Pro (PHP + MySQL)

Ứng dụng quản lý công việc full-stack viết bằng PHP thuần, MySQL và Bootstrap. Dự án tập trung vào việc quản lý task cá nhân/nhóm với nhiều tiện ích nâng cao: phân trang, lọc, Kanban, analytics, checklist, nhắc việc, file đính kèm, chia sẻ quyền, chế độ tối và thông báo email.

## ✨ Tính năng chính

- **Xác thực & thông báo email**
  - Đăng ký, đăng nhập, đăng xuất an toàn bằng `password_hash/password_verify`.
  - Gửi email chào mừng khi đăng ký và thông báo khi tạo task (SMTP Gmail / Mailtrap / dịch vụ khác).
- **Quản lý task nâng cao**
  - CRUD task theo trạng thái (`pending`, `in_progress`, `completed`) và mức ưu tiên (`high`, `medium`, `low`).
  - Tự đề xuất priority dựa trên hạn và tự gợi ý tag từ nội dung.
  - Lọc theo trạng thái/ưu tiên/tag/từ khóa, sắp xếp `due_date` và phân trang 6 task/trang.
- **Checklist & tiến độ**
  - Sub-task dạng checklist, tính % hoàn thành và hiển thị progress bar.
- **Nhắc việc đa kênh**
  - Tạo reminder qua email/browser, lưu log gửi để dễ theo dõi.
- **Bình luận & file đính kèm**
  - Comment dạng timeline và upload tài liệu (lưu trong `uploads/`).
- **Chia sẻ & phân quyền**
  - Mời user khác cộng tác trên task với vai trò viewer/editor.
- **Kanban board**
  - Giao diện kéo thả trạng thái (3 cột) hiển thị tag, ưu tiên, tiến độ.
- **Analytics & biểu đồ**
  - Thẻ KPI (tổng task, hoàn thành, quá hạn, thời gian trung bình).
  - Biểu đồ doughnut (trạng thái, ưu tiên) và line chart (xu hướng theo tháng) với Chart.js.
- **Trải nghiệm UI**
  - Bootstrap 5, chế độ sáng/tối, thông báo tự ẩn, layout responsive.

## 🛠 Công nghệ sử dụng

| Layer          | Công nghệ |
|---------------|-----------|
| Backend       | PHP 8+, PDO, PHPMailer |
| Database      | MySQL / MariaDB (script trong `database.sql`, auto ensure bằng `app/setup/schema.php`) |
| Frontend      | HTML, Bootstrap 5, Vanilla JS, Chart.js |
| SMTP          | Gmail App Password hoặc Mailtrap (cấu hình ở `app/config/mail.php`) |

## 📁 Cấu trúc chính

```
app/
  config/         db.php, mail.php
  controllers/    authController, taskController, ... (attachment, reminder, share, comment)
  helpers/        functions.php (redirect, flash, sanitize, theme ...), mail.php
  middlewares/    auth.php
  models/         User, Task (+ Tag, Subtask, Reminder, Attachment, Comment, Collaborator …)
  setup/          schema.php (tự tạo/alter bảng khi thiếu)
assets/           CSS & JS
public/           entry point cho từng trang (login, register, tasks, kanban, analytics,…)
views/            layout và view blade-lite (auth, tasks, partials)
vendor/           PHPMailer (đã tải sẵn)
uploads/          lưu file đính kèm
database.sql      script tạo toàn bộ schema
```

## 🚀 Hướng dẫn chạy

1. **Clone / copy dự án** vào thư mục phục vụ bởi web server (vd: `xampp/htdocs/todo-list`).
2. **Cấu hình database**
   - Mở `app/config/db.php`, chỉnh host/port/user/password/dbname phù hợp.
   - Tạo database trống `todo_app` (hoặc tên bạn chọn) và:
     - Import `database.sql`, hoặc
     - Truy cập trang web, `app/setup/schema.php` sẽ tự kiểm tra và tạo bảng/cột còn thiếu.
3. **Cấu hình SMTP**
   - Mở `app/config/mail.php`, đổi `username`, `password`, `from_email`, `from_name` theo SMTP bạn dùng (Gmail App Password, Mailtrap, SendGrid...).
   - Nếu dùng Gmail: bật 2FA → tạo App Password → điền vào file.
4. **Khởi động server**
   - Bật Apache + MySQL (XAMPP) hoặc dùng PHP built-in: `php -S localhost:8000 -t public`.
   - Mở `http://localhost/todo-list/public/` → điều hướng tự động đến trang login.

## 🔧 Mẹo cấu hình SMTP Gmail

1. Bật “Xác minh 2 bước” trong Google Account.
2. Truy cập **App passwords** → tạo app “Todo App” → lấy chuỗi 16 ký tự.
3. Điền vào `app/config/mail.php`:
   ```php
   return [
       'host' => 'smtp.gmail.com',
       'port' => 465,
       'username' => 'you@gmail.com',
       'password' => 'xxxx xxxx xxxx xxxx', // App Password
       'encryption' => 'ssl',
       'from_email' => 'you@gmail.com',
       'from_name' => 'Todo App',
   ];
   ```

## ✅ Lưu ý khi sử dụng

- Sau khi chỉnh `app/config/mail.php`, đảm bảo user đăng ký/tạo task có trường `email` để nhận thông báo.
- Thư mục `uploads/` cần quyền ghi để lưu file đính kèm.
- Nếu muốn reset dữ liệu nhanh, xóa database hoặc chạy lại `database.sql`.
- PHPMailer được tải thủ công ở `vendor/PHPMailer-master/`. Có thể thay bằng Composer nếu môi trường hỗ trợ.

## 📄 Giấy phép

Dự án phục vụ mục đích học tập/demo. Bạn có thể tự do tùy chỉnh, triển khai nội bộ hoặc mở rộng theo nhu cầu.

by Thế Thuận

