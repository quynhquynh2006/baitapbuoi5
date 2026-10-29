<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/ProductRepository.php';

$repository = new ProductRepository(getDatabaseConnection());

// Xử lý tham số tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5; // Số sản phẩm trên mỗi trang

// Đếm tổng số bản ghi phù hợp và tính số trang
$totalRows = $repository->count($search);
$totalPages = max(1, (int)ceil($totalRows / $limit));

// Giới hạn trang hiện tại không vượt quá tổng số trang nếu có dữ liệu
if ($page > $totalPages && $totalRows > 0) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;
$products = $repository->getAll($search, $limit, $offset);

// Tính toán khoảng bản ghi đang hiển thị
$startRecord = $totalRows > 0 ? $offset + 1 : 0;
$endRecord = min($offset + $limit, $totalRows);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Soft Pastel Dashboard</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --bg-page: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-light: #e2e8f0;
            --accent-primary: #4f46e5;
            --accent-hover: #4338ca;
            --accent-ring: rgba(99, 102, 241, 0.15);
            --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Navbar & Topbar Modern Minimalist */
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

        /* Modern Card Styling */
        .dashboard-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: all 0.2s ease-in-out;
        }

        /* Custom Input & Search */
        .search-control {
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.6rem 0.95rem;
            font-size: 0.925rem;
            color: var(--text-primary);
            background-color: #ffffff;
            transition: all 0.2s;
        }
        .search-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px var(--accent-ring);
            outline: none;
        }
        .search-addon {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--text-secondary);
        }
        .input-group > .search-control {
            border-radius: 0 10px 10px 0;
        }

        /* Custom Accent Buttons */
        .btn-accent {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.58rem 1.15rem;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }
        .btn-accent:hover, .btn-accent:focus {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transform: translateY(-1px);
        }

        .btn-soft-secondary {
            background-color: #f1f5f9;
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.58rem 0.95rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-soft-secondary:hover {
            background-color: #e2e8f0;
            color: var(--text-primary);
        }

        /* Table Styling */
        .table-custom {
            margin-bottom: 0;
        }
        .table-custom thead th {
            background: #f8fafc;
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-light);
        }
        .table-custom tbody tr {
            transition: background-color 0.15s ease;
        }
        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }
        .table-custom tbody td {
            padding: 1.1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
        }

        /* Code/SKU Badge */
        .sku-badge {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid var(--border-light);
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Soft Pastel Badges */
        .badge-soft-active {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.775rem;
            padding: 0.25rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .badge-soft-inactive {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid var(--border-light);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.775rem;
            padding: 0.25rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .badge-soft-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
        }
        .badge-soft-warning {
            background-color: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
        }
        .badge-soft-neutral {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid var(--border-light);
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.775rem;
            padding: 0.25rem 0.65rem;
        }

        /* Action Buttons: Tách rời, nền xám nhạt, hover đổi màu pastel */
        .action-btn-group {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn-action-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            font-size: 0.85rem;
            transition: all 0.18s ease;
            text-decoration: none;
            background-color: #f1f5f9;
            color: #64748b;
            padding: 0;
        }
        /* Xem: xám đậm */
        .btn-action-view:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }
        /* Sửa: hổ phách nhạt */
        .btn-action-edit:hover {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
            transform: translateY(-2px);
        }
        /* Xóa: đỏ nhạt */
        .btn-action-delete:hover {
            background-color: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
            transform: translateY(-2px);
        }

        /* Price text: Slate 900 trung tính */
        .price-text {
            color: #0f172a;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Custom Pagination */
        .pagination-custom .page-link {
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
            border-radius: 8px !important;
            margin: 0 2px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            background: #ffffff;
            transition: all 0.15s;
        }
        .pagination-custom .page-link:hover {
            background-color: #f1f5f9;
            color: var(--text-primary);
            border-color: #cbd5e1;
        }
        .pagination-custom .page-item.active .page-link {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
        }
        .pagination-custom .page-item.disabled .page-link {
            background-color: #f8fafc;
            color: #cbd5e1;
            border-color: #f1f5f9;
        }

        /* Soft Pastel Alert Overrides */
        .custom-pastel-alert.alert-success {
            background-color: #ecfdf5 !important;
            border: 1px solid #a7f3d0 !important;
            color: #065f46 !important;
            border-radius: 12px;
        }
        .custom-pastel-alert.alert-danger {
            background-color: #fef2f2 !important;
            border: 1px solid #fecaca !important;
            color: #991b1b !important;
            border-radius: 12px;
        }
        .custom-pastel-alert.alert-warning {
            background-color: #fffbeb !important;
            border: 1px solid #fde68a !important;
            color: #92400e !important;
            border-radius: 12px;
        }
        .custom-pastel-alert.alert-info {
            background-color: #eef2ff !important;
            border: 1px solid #c7d2fe !important;
            color: #3730a3 !important;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <!-- Topbar Tối Giản Hiện Đại -->
    <header class="top-navbar mb-4 sticky-top">
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
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> PDO Repository Pattern
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        <!-- Flash Message thông báo trạng thái -->
        <?= renderFlashMessages() ?>

        <!-- Khu vực tiêu đề trang & Công cụ tác vụ -->
        <div class="d-md-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">Danh Sách Sản Phẩm</h1>
                <p class="text-secondary small mb-0">Quản lý kho hàng, tìm kiếm đa tiêu chí và xử lý dữ liệu chuẩn an toàn.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="create.php" class="btn btn-accent shadow-sm">
                    <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
                </a>
            </div>
        </div>

        <!-- Thanh Tìm kiếm và Bộ Lọc Tinh Tế -->
        <div class="dashboard-card p-3 mb-4">
            <form action="index.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-7 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text search-addon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control search-control" 
                               placeholder="Tìm theo mã SP (SKU) hoặc tên sản phẩm..." 
                               value="<?= e($search) ?>" 
                               aria-label="Tìm kiếm">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-accent">
                        Tìm kiếm
                    </button>
                </div>
                <?php if ($search !== ''): ?>
                    <div class="col-auto">
                        <a href="index.php" class="btn btn-soft-secondary" title="Xóa bộ lọc tìm kiếm">
                            <i class="fa-solid fa-rotate-left me-1"></i> Đặt lại
                        </a>
                    </div>
                    <div class="col-12 mt-2">
                        <span class="text-secondary small">
                            Đang lọc theo từ khóa: <strong class="text-dark">"<?= e($search) ?>"</strong> 
                            &bull; Tìm thấy <strong><?= $totalRows ?></strong> kết quả
                        </span>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bảng Danh Sách Dữ Liệu Sản Phẩm -->
        <div class="dashboard-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 70px;">ID</th>
                            <th scope="col" style="width: 140px;">Mã Sản Phẩm</th>
                            <th scope="col">Tên Sản Phẩm</th>
                            <th scope="col" class="text-end" style="width: 150px;">Đơn Giá</th>
                            <th scope="col" class="text-center" style="width: 120px;">Tồn Kho</th>
                            <th scope="col" class="text-center" style="width: 140px;">Trạng Thái</th>
                            <th scope="col" class="text-center" style="width: 150px;">Ngày Tạo</th>
                            <th scope="col" class="text-center" style="width: 130px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="mb-3 text-secondary opacity-50">
                                            <i class="fa-regular fa-folder-open fa-3x"></i>
                                        </div>
                                        <h6 class="fw-semibold text-dark mb-1">Không tìm thấy sản phẩm nào</h6>
                                        <p class="text-secondary small mb-3">Vui lòng thử lại với từ khóa khác hoặc tạo sản phẩm mới.</p>
                                        <?php if ($search !== ''): ?>
                                            <a href="index.php" class="btn btn-sm btn-soft-secondary">
                                                Xóa bộ lọc tìm kiếm
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td class="text-center text-secondary small fw-medium">
                                        #<?= (int)$product['id'] ?>
                                    </td>
                                    <td>
                                        <span class="sku-badge">
                                            <?= e($product['code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail.php?id=<?= (int)$product['id'] ?>" class="text-decoration-none fw-semibold text-dark d-block">
                                            <?= e($product['name']) ?>
                                        </a>
                                        <?php if (!empty($product['description'])): ?>
                                            <span class="text-secondary small text-truncate d-block" style="max-width: 340px;">
                                                <?= e($product['description']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="price-text">
                                            <?= formatCurrency($product['price']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ((int)$product['quantity'] <= 0): ?>
                                            <span class="badge-soft-danger">Hết hàng</span>
                                        <?php elseif ((int)$product['quantity'] <= 5): ?>
                                            <span class="badge-soft-warning"><?= (int)$product['quantity'] ?> (Sắp hết)</span>
                                        <?php else: ?>
                                            <span class="badge-soft-neutral"><?= (int)$product['quantity'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($product['status'] === 'active'): ?>
                                            <span class="badge-soft-active">
                                                <i class="fa-solid fa-circle text-emerald-500" style="font-size: 6px;"></i> Đang bán
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-soft-inactive">
                                                <i class="fa-solid fa-circle text-slate-400" style="font-size: 6px;"></i> Ngừng bán
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-secondary small">
                                        <?= formatDateTime($product['created_at']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btn-group">
                                            <!-- Nút Xem (Tách rời, hover xám đậm) -->
                                            <a href="detail.php?id=<?= (int)$product['id'] ?>" 
                                               class="btn-action-icon btn-action-view" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Xem chi tiết">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- Nút Sửa (Tách rời, hover hổ phách nhạt) -->
                                            <a href="edit.php?id=<?= (int)$product['id'] ?>" 
                                               class="btn-action-icon btn-action-edit" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Chỉnh sửa sản phẩm">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <!-- Nút Xóa (Tách rời, POST method, hover đỏ nhạt) -->
                                            <form action="delete.php" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm: &quot;<?= e($product['name']) ?>&quot; (Mã: <?= e($product['code']) ?>) không?\nHành động này không thể hoàn tác!');">
                                                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                                <button type="submit" 
                                                        class="btn-action-icon btn-action-delete" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="Xóa sản phẩm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Bảng: Thống Kê & Phân Trang Minimalist -->
            <?php if ($totalRows > 0): ?>
                <div class="p-3 border-top d-md-flex align-items-center justify-content-between" style="border-color: var(--border-light) !important;">
                    <div class="text-secondary small mb-2 mb-md-0">
                        Hiển thị <strong><?= $startRecord ?></strong> - <strong><?= $endRecord ?></strong> trên <strong><?= $totalRows ?></strong> sản phẩm
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Phân trang danh sách sản phẩm">
                            <ul class="pagination pagination-custom justify-content-center justify-content-md-end mb-0">
                                <!-- Nút Về Trang Đầu -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(['page' => 1, 'search' => $search]) ?>" title="Trang đầu">
                                        <i class="fa-solid fa-angles-left"></i>
                                    </a>
                                </li>

                                <!-- Nút Lùi 1 trang -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(['page' => $page - 1, 'search' => $search]) ?>" title="Trang trước">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </a>
                                </li>

                                <!-- Các số trang -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(['page' => $i, 'search' => $search]) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Nút Tới 1 trang -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(['page' => $page + 1, 'search' => $search]) ?>" title="Trang sau">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                </li>

                                <!-- Nút Đến Trang Cuối -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(['page' => $totalPages, 'search' => $search]) ?>" title="Trang cuối">
                                        <i class="fa-solid fa-angles-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kích hoạt Bootstrap Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>
</body>
</html>
