<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/ProductRepository.php';

$repository = new ProductRepository(getDatabaseConnection());

// Lấy và kiểm tra ID từ tham số URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$product = null;
if ($id && $id > 0) {
    $product = $repository->getById((int)$id);
}

// Nếu không tìm thấy ID hoặc sản phẩm không tồn tại, trả về HTTP 404
if (!$product) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Không Tìm Thấy Sản Phẩm</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
            }
            .card-404 {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            }
            .btn-accent {
                background-color: #4f46e5;
                border-color: #4f46e5;
                color: #ffffff;
                border-radius: 10px;
                font-weight: 600;
                padding: 0.6rem 1.25rem;
            }
            .btn-accent:hover {
                background-color: #4338ca;
                color: #ffffff;
            }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card-404 py-5 px-4">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 72px; height: 72px;">
                                <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                            </span>
                        </div>
                        <h2 class="fw-bold mb-2 text-dark">404 - Không Tìm Thấy</h2>
                        <p class="text-secondary small mb-4">
                            Sản phẩm với định danh <code>ID: <?= e($_GET['id'] ?? 'N/A') ?></code> không tồn tại trên hệ thống hoặc đã bị gỡ bỏ trước đó.
                        </p>
                        <a href="index.php" class="btn btn-accent">
                            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết: <?= e($product['name']) ?> - ShopDemo</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --bg-page: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-light: #e2e8f0;
            --accent-primary: #4f46e5;
            --accent-hover: #4338ca;
            --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Topbar Minimalist */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 0.85rem 0;
        }
        .brand-logo-box {
            width: 38px;
            height: 38px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        /* Card container */
        .dashboard-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .sku-badge {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 0.25rem 0.65rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Soft Badges */
        .badge-soft-active {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .badge-soft-inactive {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid var(--border-light);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Detail Attribute Rows */
        .detail-row {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            width: 200px;
            flex-shrink: 0;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }
        .detail-value {
            flex-grow: 1;
            font-size: 0.95rem;
            color: var(--text-primary);
        }

        .price-highlight {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        /* Buttons */
        .btn-soft-back {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.55rem 1.15rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }
        .btn-soft-back:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }

        .btn-soft-edit {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 0.55rem 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }
        .btn-soft-edit:hover {
            background-color: #fde68a;
            color: #78350f;
            transform: translateY(-1px);
        }

        .btn-soft-delete {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 0.55rem 1.25rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }
        .btn-soft-delete:hover {
            background-color: #fecaca;
            color: #991b1b;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <header class="top-navbar mb-4">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="brand-logo-box">
                        <i class="fa-solid fa-cube"></i>
                    </div>
                    <div>
                        <span class="fs-5 fw-bold text-dark d-block lh-1">ShopDemo</span>
                        <small class="text-secondary" style="font-size: 0.75rem;">Product Management System</small>
                    </div>
                </a>
                <a href="index.php" class="btn-soft-back small">
                    <i class="fa-solid fa-arrow-left"></i> Danh sách
                </a>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        <!-- Breadcrumb tinh tế -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Sản phẩm</a></li>
                <li class="breadcrumb-item text-secondary active" aria-current="page">Chi tiết #<?= (int)$product['id'] ?></li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="dashboard-card overflow-hidden">
                    <!-- Header Card -->
                    <div class="p-4 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--border-light) !important;">
                        <div>
                            <span class="text-secondary small fw-medium">Sản phẩm ID #<?= (int)$product['id'] ?></span>
                            <h2 class="h4 fw-bold text-dark mb-0 mt-1"><?= e($product['name']) ?></h2>
                        </div>
                        <div>
                            <?php if ($product['status'] === 'active'): ?>
                                <span class="badge-soft-active">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Đang bán
                                </span>
                            <?php else: ?>
                                <span class="badge-soft-inactive">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Ngừng bán
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Chi Tiết Thuộc Tính -->
                    <div class="p-4">
                        <div class="detail-row">
                            <div class="detail-label">Mã Sản Phẩm (SKU)</div>
                            <div class="detail-value">
                                <span class="sku-badge"><?= e($product['code']) ?></span>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Đơn Giá Niêm Yết</div>
                            <div class="detail-value">
                                <span class="price-highlight"><?= formatCurrency($product['price']) ?></span>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Tồn Kho Khả Dụng</div>
                            <div class="detail-value">
                                <?php if ((int)$product['quantity'] <= 0): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-semibold">
                                        Hết hàng (0 sản phẩm)
                                    </span>
                                <?php elseif ((int)$product['quantity'] <= 5): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill fw-semibold">
                                        <?= (int)$product['quantity'] ?> sản phẩm (Cảnh báo sắp hết)
                                    </span>
                                <?php else: ?>
                                    <span class="fw-semibold text-dark"><?= (int)$product['quantity'] ?> sản phẩm</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Mô Tả Sản Phẩm</div>
                            <div class="detail-value">
                                <?php if (!empty($product['description'])): ?>
                                    <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.6;">
                                        <?= e($product['description']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-secondary opacity-75 fst-italic">Chưa có thông tin mô tả chi tiết.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Thời Gian Tạo</div>
                            <div class="detail-value text-secondary small">
                                <i class="fa-regular fa-clock me-1"></i> <?= formatDateTime($product['created_at']) ?>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Cập Nhật Gần Nhất</div>
                            <div class="detail-value text-secondary small">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> <?= formatDateTime($product['updated_at']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tác Vụ -->
                    <div class="p-4 bg-light border-top d-flex align-items-center justify-content-between" style="border-color: var(--border-light) !important;">
                        <a href="index.php" class="btn-soft-back">
                            <i class="fa-solid fa-arrow-left"></i> Quay lại
                        </a>
                        <div class="d-flex gap-2">
                            <a href="edit.php?id=<?= (int)$product['id'] ?>" class="btn-soft-edit">
                                <i class="fa-solid fa-pen"></i> Chỉnh sửa
                            </a>
                            <form action="delete.php" 
                                  method="POST" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm &quot;<?= e($product['name']) ?>&quot; không?\nHành động này không thể hoàn tác!');">
                                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                <button type="submit" class="btn-soft-delete">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
