# BÁO CÁO BÀI TẬP VỀ NHÀ 5: HỆ THỐNG QUẢN LÝ LỚP HỌC PHẦN (StudentHub)

* **Học phần:** Phát triển Ứng dụng Web với PHP & MySQL
* **Sinh viên thực hiện:** **Tạ Như Quỳnh**
* **Kiến trúc áp dụng:** Clean Architecture, Repository Pattern, PHP 8.x PDO, PRG Pattern
* **Cơ sở dữ liệu:** MySQL (`studenthub_personal`), Bảng: `lop_hoc_phan`
* **Giao diện:** Modern Minimalist / Soft Pastel Dashboard & Bento Card Grid

---

## 1. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
baitapbuoi5/
├── config/
│   └── database.php              # Kết nối PDO chuẩn (utf8mb4, ERRMODE_EXCEPTION, EMULATE_PREPARES => false)
├── helpers/
│   └── session.php               # Quản lý Flash messages, Session, XSS Filter e(), Redirect, Status Badge
├── repositories/
│   └── LopHocPhanRepository.php  # Tách tầng: Đóng gói 100% câu truy vấn SQL bằng Prepared Statements
├── sql/
│   ├── schema.sql                # Khởi tạo CSDL studenthub_personal và bảng lop_hoc_phan
│   └── seed.sql                  # 15 bản ghi lớp học phần mẫu ngành CNTT để test phân trang
├── index.php                     # Màn hình danh sách, tìm kiếm đa tiêu chí & phân trang an toàn
├── create.php                    # Màn hình thêm mới lớp học phần (Validate nghiêm ngặt, PRG pattern)
├── edit.php                      # Màn hình cập nhật lớp học phần (Xử lý 404, PRG pattern)
├── detail.php                    # Màn hình xem chi tiết lớp học phần (Bento card layout, xử lý 404)
├── delete.php                    # Xử lý xóa an toàn (Bắt buộc POST, chặn GET trả về 405 Method Not Allowed)
└── README.md                     # Báo cáo cá nhân Tạ Như Quỳnh + 5 Test Case kiểm thử
```

---

## 2. NGUYÊN TẮC KIẾN TRÚC & TIÊU CHUẨN KỸ THUẬT

### 2.1. Kết nối CSDL chuẩn PDO Hiện Đại
* **DSN:** `mysql:host=localhost;dbname=studenthub_personal;charset=utf8mb4`.
* **Chế độ lỗi:** `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
* **Chế độ fetch:** `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
* **Vô hiệu hóa giả lập prepare:** `PDO::ATTR_EMULATE_PREPARES => false`, bảo đảm MySQL xử lý đúng kiểu số nguyên (`PDO::PARAM_INT`) cho `LIMIT` và `OFFSET`.

### 2.2. Tách Tầng Tường Minh (Repository Pattern)
* Toàn bộ các truy vấn SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`, `COUNT`) được đóng gói 100% trong lớp `LopHocPhanRepository.php`.
* Các file giao diện (`index.php`, `create.php`, `edit.php`, `detail.php`, `delete.php`) tuyệt đối **không chứa bất kỳ dòng lệnh SQL nào**.

### 2.3. An Toàn Dữ Liệu & Chống Tấn Công SQL Injection
* 100% các biến đầu vào từ người dùng được truyền qua **Prepared Statement** với placeholder `:param` và ràng buộc kiểu rõ ràng (`PDO::PARAM_STR`, `PDO::PARAM_INT`).
* 100% dữ liệu xuất ra giao diện được bao bọc bởi hàm helper `e($val)` tương đương `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` để ngăn ngừa tấn công **Cross-Site Scripting (XSS)**.

### 2.4. Mô Hình POST-Redirect-GET (PRG) & Flash Messages
* Các thao tác Thêm mới, Chỉnh sửa, Xóa được xử lý qua phương thức `POST`.
* Sau khi xử lý CSDL thành công, hệ thống lưu thông báo vào Session (`setFlash()`) và chuyển hướng (`redirect('index.php')`).
* Ngăn chặn tuyệt đối tình trạng người dùng nhấn **F5** bị gửi lặp dữ liệu (Form Resubmission).

### 2.5. Bảo Mật Thao Tác Xóa & Mã Lỗi HTTP 405 Method Not Allowed
* Hành động xóa bắt buộc phải thực thi qua `POST` từ form có xác nhận JavaScript.
* File `delete.php` kiểm tra nếu `$_SERVER['REQUEST_METHOD'] !== 'POST'` sẽ gửi header `Allow: POST`, trả về mã **`405 Method Not Allowed`** kèm trang cảnh báo an ninh.

### 2.6. Xử Lý Mã Lỗi HTTP 404 Not Found & Bento Card Grid
* File `detail.php` và `edit.php` kiểm tra sự tồn tại của ID. Nếu ID rỗng hoặc không có trong CSDL, hệ thống trả về mã **`404 Not Found`**.
* Trang chi tiết áp dụng thiết kế **Bento Card Grid** hiện đại với các khối thông tin độc lập: Cán bộ giảng dạy, Thời khóa biểu & Địa điểm, Đào tạo & Quy mô, Thông tin quản trị.

---

## 3. HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN TRÊN XAMPP

### Bước 1: Khởi động XAMPP
* Mở **XAMPP Control Panel** và nhấn **Start** tại 2 dịch vụ: **Apache** và **MySQL**.

### Bước 2: Nạp CSDL
Mở cửa sổ PowerShell/CMD và thực thi lệnh:
```powershell
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\baitapbuoi5\sql\schema.sql"
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\baitapbuoi5\sql\seed.sql"
```
*(Hoặc truy cập `http://localhost/phpmyadmin/`, tạo database `studenthub_personal` và lần lượt Import hai file `sql/schema.sql` và `sql/seed.sql`)*.

### Bước 3: Truy cập ứng dụng
Mở trình duyệt web và truy cập địa chỉ:
```text
http://localhost/baitapbuoi5/
```

---

## 4. KỊCH BẢN KIỂM THỬ CHI TIẾT (5 TEST CASES NHẬP LIỆU ĐÚNG / SAI)

| Mã TC | Tên Tình Huống Kiểm Thử | Dữ Liệu Đầu Vào (Input) | Các Bước Thực Hiện | Kết Quả Mong Đợi | Trạng Thái |
|:---:|---|---|---|---|:---:|
| **TC-01** | **Thêm mới lớp học phần thành công (Valid Input) & PRG** | • Mã lớp: `SE3015_01`<br>• Tên môn: `Kiểm thử phần mềm tự động`<br>• Số tín chỉ: `3`<br>• Giảng viên: `TS. Vũ Mai Lan`<br>• Phòng: `Lab 02 - Tòa A`<br>• Lịch học: `Thứ 5 (Tiết 1-3)`<br>• Sĩ số: `35`<br>• Trạng thái: `mo` | 1. Nhấp **Thêm Lớp Học Phần**<br>2. Nhập đầy đủ thông tin hợp lệ<br>3. Bấm **Lưu Lớp Học Phần**<br>4. Thử bấm **F5** tại trang chủ | • Thêm bản ghi mới thành công vào MySQL.<br>• Chuyển hướng (302) về `index.php`.<br>• Hiển thị Flash Message màu xanh: *"Thêm mới lớp học phần... thành công!"*.<br>• Bấm F5 không bị gửi lặp dữ liệu. | **PASS** |
| **TC-02** | **Bắt lỗi trùng mã lớp (Duplicate Code) & Dữ liệu rỗng** | • Mã lớp: `ITEC2104_01` *(đã tồn tại trong hệ thống)*<br>• Tên môn: *để trống*<br>• Số tín chỉ: `-2` *(sai định dạng)*<br>• Giảng viên: *để trống* | 1. Vào trang `create.php`<br>2. Nhập mã lớp bị trùng và để trống các trường bắt buộc<br>3. Bấm **Lưu Lớp Học Phần** | • Hệ thống không insert dữ liệu.<br>• Hiển thị khối thông báo màu đỏ liệt kê chi tiết các lỗi validation.<br>• Các trường input vi phạm bị bôi đỏ (`is-invalid`).<br>• Dữ liệu đã nhập được giữ nguyên trong form. | **PASS** |
| **TC-03** | **Tìm kiếm đa tiêu chí & Phân trang an toàn** | • Từ khóa tìm kiếm: `PHP`<br>• Lọc trạng thái: `mo` (Đang mở) | 1. Tại thanh tìm kiếm, nhập `PHP`<br>2. Chọn trạng thái `Đang mở đăng ký`<br>3. Bấm **Lọc dữ liệu**<br>4. Nhấp chuyển qua các trang phân trang (Trang 1, Trang 2) | • Bảng chỉ hiển thị các lớp thỏa mãn đồng thời: chứa chữ PHP và trạng thái đang mở.<br>• Phân trang an toàn với `LIMIT 5 OFFSET x`.<br>• Các liên kết chuyển trang giữ nguyên tham số tìm kiếm trên URL (`search=PHP&trang_thai=mo`). | **PASS** |
| **TC-04** | **Kiểm thử an ninh: Cố tình xóa bằng phương thức GET** | • URL: `http://localhost/baitapbuoi5/delete.php?id=1` *(truy cập trực tiếp qua thanh địa chỉ trình duyệt)* | 1. Mở tab mới trên trình duyệt<br>2. Gõ trực tiếp link `delete.php?id=1`<br>3. Nhấn Enter để gửi request `GET` | • Bản ghi có ID=1 **không bị xóa**.<br>• Máy chủ gửi header `Allow: POST` và HTTP Status Code **`405 Method Not Allowed`**.<br>• Xuất hiện trang cảnh báo an ninh màu đỏ từ chối hành động. | **PASS** |
| **TC-05** | **Xem chi tiết với ID không tồn tại (Xử lý 404)** | • URL: `http://localhost/baitapbuoi5/detail.php?id=99999` *(ID không có trong CSDL)* | 1. Nhập vào thanh địa chỉ: `detail.php?id=99999`<br>2. Nhấn Enter | • Hệ thống trả về HTTP Status Code **`404 Not Found`**.<br>• Hiển thị giao diện thông báo *"404 - Lớp Học Phần Không Tồn Tại"* thân thiện.<br>• Cung cấp nút quay lại danh sách lớp học phần an toàn. | **PASS** |
