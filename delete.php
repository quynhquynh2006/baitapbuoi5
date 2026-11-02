<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/LopHocPhanRepository.php';

// =========================================================================
// BẢO MẬT: Bắt buộc chỉ chấp nhận phương thức POST cho hành động Xóa.
// Nếu nhận Request GET (hoặc bất kỳ phương thức nào khác POST), 
// hệ thống chặn lại và trả về mã lỗi HTTP 405 Method Not Allowed.
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>405 Method Not Allowed - StudentHub</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .card-405 { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05); }
            .btn-accent { background-color: #4f46e5; border-color: #4f46e5; color: #ffffff; border-radius: 10px; font-weight: 600; padding: 0.6rem 1.4rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; }
            .btn-accent:hover { background-color: #4338ca; color: #ffffff; }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="card-405 py-5 px-4">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 72px; height: 72px;">
                                <i class="fa-solid fa-ban fa-2x"></i>
                            </span>
                        </div>
                        <h2 class="fw-bold mb-2 text-dark">405 Method Not Allowed</h2>
                        <div class="alert text-start my-3" style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; color:#92400e;">
                            <p class="mb-1 small"><strong>Cảnh báo an ninh:</strong> Hành động xóa lớp học phần không được phép thực thi thông qua phương thức <code><?= htmlspecialchars($_SERVER['REQUEST_METHOD'], ENT_QUOTES, 'UTF-8') ?></code>.</p>
                            <p class="mb-0 text-muted" style="font-size: 0.8rem;">Yêu cầu này bắt buộc phải gửi qua phương thức <strong>POST</strong> có xác nhận nhằm chống tấn công CSRF và xóa nhầm dữ liệu.</p>
                        </div>
                        <a href="index.php" class="btn btn-accent mt-2">
                            <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Xử lý khi nhận request hợp lệ bằng phương thức POST
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    setFlash('danger', 'ID lớp học phần không hợp lệ!');
    redirect('index.php');
}

$repository = new LopHocPhanRepository(getDatabaseConnection());

// Kiểm tra sự tồn tại của bản ghi trước khi xóa
$classItem = $repository->getById((int)$id);

if (!$classItem) {
    setFlash('warning', 'Lớp học phần với ID #' . $id . ' không tồn tại hoặc đã được xóa trước đó.');
    redirect('index.php');
}

// Tiến hành xóa qua Repository
$deleted = $repository->delete((int)$id);

if ($deleted) {
    setFlash('success', 'Đã xóa thành công lớp học phần "' . $classItem['ten_mon_hoc'] . '" (Mã: ' . $classItem['ma_lop'] . ').');
} else {
    setFlash('danger', 'Không thể xóa lớp học phần lúc này do lỗi hệ thống.');
}

// Điều hướng theo mô hình POST-Redirect-GET
redirect('index.php');
