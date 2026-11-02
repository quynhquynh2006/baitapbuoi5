<?php
declare(strict_types=1);

/**
 * ============================================================================
 * CẤU HÌNH KẾT NỐI CƠ SỞ DỮ LIỆU - PDO (PHP 8.x)
 * Dự án: StudentHub - Quản Lý Lớp Học Phần (BÀI TẬP VỀ NHÀ 5)
 * Sinh viên thực hiện: Tạ Như Quỳnh
 * ============================================================================
 */

$dbHost  = 'localhost';
$dbName  = 'studenthub_personal';
$dbUser  = 'root';
$dbPass  = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StudentHub - Lỗi Kết Nối Cơ Sở Dữ Liệu</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .error-card { background: #ffffff; border: 1px solid #fee2e2; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05); }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="error-card p-4 p-md-5">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-3" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-database fa-lg"></i>
                            </span>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">Lỗi Kết Nối CSDL (PDO)</h4>
                                <small class="text-secondary">StudentHub - Tạ Như Quỳnh</small>
                            </div>
                        </div>
                        <p class="text-danger small mb-3">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                            Không thể kết nối đến máy chủ MySQL với database <code>studenthub_personal</code>.
                        </p>
                        <div class="bg-light p-3 rounded-3 border mb-4 font-monospace small text-break">
                            <div><strong>DSN:</strong> <?= htmlspecialchars($dsn, ENT_QUOTES, 'UTF-8') ?></div>
                            <div><strong>Chi tiết lỗi:</strong> <?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="alert alert-warning border-0 small mb-0" style="background:#fffbeb; color:#92400e;">
                            <strong>Hướng dẫn xử lý nhanh:</strong>
                            <ol class="ps-3 mb-0 mt-2">
                                <li>Mở <strong>XAMPP Control Panel</strong> và bật nút <strong>Start</strong> tại MySQL.</li>
                                <li>Import file <code>sql/schema.sql</code> và <code>sql/seed.sql</code> vào MySQL để tạo CSDL <code>studenthub_personal</code>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Trả về đối tượng PDO toàn cục
 *
 * @return PDO
 */
function getDatabaseConnection(): PDO
{
    global $pdo;
    return $pdo;
}
