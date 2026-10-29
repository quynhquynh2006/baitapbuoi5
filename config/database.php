<?php
declare(strict_types=1);

/**
 * Cấu hình kết nối Cơ sở dữ liệu sử dụng PHP Data Objects (PDO)
 * Đáp ứng các tiêu chí bảo mật, bắt ngoại lệ lỗi và thiết lập charset utf8mb4.
 */

$dbHost = 'localhost';
$dbName = 'shopdemo';
$dbUser = 'root';
$dbPass = 'Quynh@24012006';
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
    // Trả về mã lỗi 500 nếu không thể kết nối tới cơ sở dữ liệu
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lỗi Kết Nối Cơ Sở Dữ Liệu</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">Lỗi Kết Nối Cơ Sở Dữ Liệu (Database Connection Error)</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-danger fw-semibold">Không thể kết nối đến máy chủ MySQL với cấu hình hiện tại:</p>
                            <ul class="list-unstyled bg-light p-3 rounded border">
                                <li><strong>DSN:</strong> <code><?= htmlspecialchars($dsn, ENT_QUOTES, 'UTF-8') ?></code></li>
                                <li><strong>Người dùng:</strong> <code><?= htmlspecialchars($dbUser, ENT_QUOTES, 'UTF-8') ?></code></li>
                                <li><strong>Chi tiết lỗi:</strong> <?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?></li>
                            </ul>
                            <div class="alert alert-warning mb-0">
                                <strong>Gợi ý khắc phục:</strong>
                                <ol class="mb-0 mt-2 ps-3">
                                    <li>Đảm bảo dịch vụ MySQL trong XAMPP Control Panel đang ở trạng thái <strong>Running</strong>.</li>
                                    <li>Kiểm tra đã tạo database <code>shopdemo</code> và import file <code>sql/schema.sql</code> chưa.</li>
                                </ol>
                            </div>
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
 * Trả về đối tượng kết nối PDO toàn cục
 *
 * @return PDO
 */
function getDatabaseConnection(): PDO
{
    global $pdo;
    return $pdo;
}
