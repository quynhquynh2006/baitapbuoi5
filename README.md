# Hệ Thống Quản Lý Sản Phẩm (CRUD PHP - MySQL Chuẩn PDO & Repository Pattern)

Dự án thực hành Backend PHP tiêu chuẩn doanh nghiệp cho bài tập thực hành CRUD, tuân thủ nghiêm ngặt các nguyên tắc bảo mật, cấu trúc tách tầng sạch (**Repository Pattern**), an toàn dữ liệu và tối ưu trải nghiệm người dùng (**POST-Redirect-GET**).

---

## 1. Cấu Trúc Thư Mục Dự Án

```text
baitapbuoi5/
├── config/
│   └── database.php              # Cấu hình kết nối PDO (DSN, ErrorMode, FetchMode)
├── helpers/
│   └── session.php               # Helpers: Flash Message, Session, XSS Filter, Redirect
├── repositories/
│   └── ProductRepository.php     # Tầng truy vấn CSDL: Toàn bộ SQL đóng gói tại đây
├── sql/
│   ├── schema.sql                # Khởi tạo CSDL shopdemo và bảng products
│   └── seed.sql                  # Dữ liệu mẫu (15 sản phẩm) phục vụ test phân trang
├── index.php                     # Màn hình danh sách, tìm kiếm & phân trang
├── detail.php                    # Màn hình xem chi tiết sản phẩm (Xử lý 404 Not Found)
├── create.php                    # Màn hình thêm mới sản phẩm (Xử lý POST-Redirect-GET)
├── edit.php                      # Màn hình cập nhật sản phẩm (Xử lý POST-Redirect-GET)
├── delete.php                    # Xử lý xóa sản phẩm (Bảo mật POST, chặn GET trả về 405)
└── README.md                     # Tài liệu hướng dẫn cài đặt và vận hành
```

---

## 2. Các Tiêu Chuẩn Kỹ Thuật & Kiến Trúc Đã Triển Khai

### 2.1. Kết Nối CSDL Chuẩn PDO Hiện Đại
- Sử dụng DSN chuẩn: `mysql:host=localhost;dbname=shopdemo;charset=utf8mb4`.
- Chế độ bắt lỗi: `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
- Chế độ nạp dữ liệu mặc định: `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- Vô hiệu hóa giả lập prepare: `PDO::ATTR_EMULATE_PREPARES => false`, đảm bảo MySQL thực thi prepared statement gốc và ép kiểu số nguyên an toàn cho `LIMIT` và `OFFSET`.

### 2.2. An Toàn Dữ Liệu & Chống Tấn Công SQL Injection
- **100% câu truy vấn** tương tác dữ liệu đầu vào đều sử dụng **Prepared Statements** với tham số ràng buộc `:param` thông qua `$stmt->bindValue()`.
- Tuyệt đối không nối chuỗi biến trực tiếp vào câu lệnh SQL.
- Sử dụng helper `e()` bọc hàm `htmlspecialchars()` với cờ `ENT_QUOTES | UTF-8` trên mọi dữ liệu xuất ra View để chống triệt để **Stored & Reflected XSS**.

### 2.3. Tách Tầng Tường Minh (Repository Pattern)
- Toàn bộ các thao tác `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `COUNT` được đóng gói độc lập trong lớp `ProductRepository` (`repositories/ProductRepository.php`).
- Các file giao diện (`index.php`, `detail.php`, `create.php`, `edit.php`, `delete.php`) chỉ đóng vai trò Controller/View, gọi hàm nghiệp vụ từ Repository, tuyệt đối **không chứa bất kỳ dòng mã SQL nào**.

### 2.4. Mô Hình POST-Redirect-GET (PRG) & Flash Messages
- Khi người dùng gửi form Thêm mới (`create.php`), Cập nhật (`edit.php`), hoặc Xóa (`delete.php`), kịch bản nhận dữ liệu qua `POST`, xử lý xong sẽ ghi thông báo trạng thái vào Session (`setFlash()`), sau đó gọi lệnh `redirect('index.php')`.
- Ngăn chặn hoàn toàn lỗi trình duyệt hỏi **"Confirm Form Resubmission"** hoặc gửi lặp bản ghi khi người dùng nhấn phím **F5**.
- Thông báo chỉ hiển thị đúng 1 lần duy nhất rồi tự động xóa khỏi Session.

### 2.5. Bảo Mật Thao Tác Xóa & HTTP 405 Method Not Allowed
- Thao tác Xóa được thực hiện thông qua Form `POST` với nút bấm có sự kiện JavaScript `confirm()` cảnh báo người dùng.
- File `delete.php` kiểm tra chặt chẽ `$_SERVER['REQUEST_METHOD'] !== 'POST'`. Nếu phát hiện người dùng cố tình truy cập bằng phương thức `GET` (nhập trực tiếp URL hoặc bấm link), hệ thống sẽ lập tức thiết lập Header `Allow: POST`, trả về mã trạng thái **HTTP 405 Method Not Allowed** và hiển thị trang cảnh báo an ninh.

### 2.6. Phân Trang An Toàn & Tìm Kiếm Đa Tiêu Chí
- **Tìm kiếm:** Cho phép lọc linh hoạt theo **Mã sản phẩm (Code)** hoặc **Tên sản phẩm (Name)** thông qua Prepared Statement với toán tử `LIKE :search`.
- **Phân trang:** Áp dụng công thức an toàn `LIMIT :limit OFFSET :offset` với giá trị được ràng buộc kiểu `PDO::PARAM_INT`. Các liên kết chuyển trang tự động duy trì tham số tìm kiếm hiện tại (`http_build_query`).
- **Mã lỗi HTTP 404:** Màn hình `detail.php` và `edit.php` kiểm tra tính hợp lệ của ID. Nếu ID không tồn tại hoặc sai định dạng, kịch bản trả về HTTP 404 Not Found kèm giao diện thân thiện.

---

## 3. Hướng Dẫn Cài Đặt & Chạy Ứng Dụng Với XAMPP

### Bước 1: Khởi động dịch vụ XAMPP
1. Mở **XAMPP Control Panel**.
2. Nhấn nút **Start** tại 2 module **Apache** và **MySQL**.

### Bước 2: Nạp Cơ Sở Dữ Liệu
Bạn có thể lựa chọn 1 trong 2 cách sau:

#### Cách 1: Nạp qua phpMyAdmin (Giao diện trực quan)
1. Truy cập trình duyệt vào đường dẫn: `http://localhost/phpmyadmin/`.
2. Chọn tab **Import** (Nhập).
3. Nhấn **Choose File** và chọn tệp `baitapbuoi5/sql/schema.sql`, sau đó bấm **Import**.
4. Tiếp tục chọn tab **Import**, chọn tệp `baitapbuoi5/sql/seed.sql` và bấm **Import** để nạp dữ liệu mẫu.

#### Cách 2: Nạp bằng MySQL Command Line
Mở PowerShell hoặc Command Prompt và thực thi lệnh:
```bash
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\baitapbuoi5\sql\schema.sql"
c:\xampp\mysql\bin\mysql.exe -u root < "c:\xampp\htdocs\baitapbuoi5\sql\seed.sql"
```

### Bước 3: Truy Cập Ứng Dụng
Mở trình duyệt web bất kỳ và truy cập địa chỉ:
```text
http://localhost/baitapbuoi5/
```

---

## 4. Kịch Bản Kiểm Thử Tính Năng (Test Checklist)

| STT | Tình huống kiểm thử | Các bước thực hiện | Kết quả mong đợi |
|---|---|---|---|
| 1 | **Xem danh sách & Phân trang** | Truy cập `http://localhost/baitapbuoi5/`, bấm sang các trang 2, 3 | Dữ liệu chuyển trang mượt mà (5 sản phẩm/trang), số thứ tự và phân trang chính xác |
| 2 | **Tìm kiếm sản phẩm** | Nhập từ khóa `Dell` hoặc `APL` vào ô tìm kiếm rồi bấm Tìm kiếm | Bảng chỉ hiển thị các sản phẩm có mã hoặc tên chứa từ khóa tìm kiếm |
| 3 | **Thêm sản phẩm mới (PRG)** | Nhấn **Thêm Sản Phẩm Mới**, nhập form hợp lệ rồi bấm Lưu | Dữ liệu được thêm mới, chuyển hướng về `index.php` kèm Flash message màu xanh thông báo thành công |
| 4 | **F5 không bị gửi lặp dữ liệu** | Sau khi thêm mới hoặc sửa, nhấn liên tục phím **F5** | Trang web tải lại bình thường, không xuất hiện popup cảnh báo gửi lại form, không sinh bản ghi rác |
| 5 | **Xem chi tiết sản phẩm** | Bấm vào biểu tượng con mắt (Xem) ở một dòng sản phẩm | Hiển thị đầy đủ thông tin: ID, Mã, Tên, Giá VND, Tồn kho, Mô tả, Ngày tạo |
| 6 | **Kiểm tra mã lỗi HTTP 404** | Truy cập link sản phẩm không tồn tại: `http://localhost/baitapbuoi5/detail.php?id=999999` | Trả về tiêu đề HTTP `404 Not Found` và giao diện thông báo sản phẩm không tồn tại |
| 7 | **Chỉnh sửa sản phẩm** | Bấm nút Sửa, thay đổi giá tiền hoặc số lượng rồi Lưu | Cập nhật thành công, điều hướng về `index.php` với Flash message xác nhận |
| 8 | **Xóa sản phẩm hợp lệ** | Bấm nút Thùng rác (Xóa) tại một dòng sản phẩm | Xuất hiện hộp thoại JavaScript Confirm. Chọn OK -> Dữ liệu được xóa an toàn qua POST |
| 9 | **Kiểm tra bảo mật HTTP 405** | Nhập trực tiếp vào thanh địa chỉ trình duyệt: `http://localhost/baitapbuoi5/delete.php?id=1` (phương thức GET) | Hệ thống chặn ngay lập tức, trả về HTTP status `405 Method Not Allowed` và thông báo cảnh báo |
