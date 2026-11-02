<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/LopHocPhanRepository.php';

$repository = new LopHocPhanRepository(getDatabaseConnection());

// Xử lý các tham số tìm kiếm, lọc và phân trang
$search    = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$trangThai = isset($_GET['trang_thai']) ? trim((string)$_GET['trang_thai']) : '';
$page      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit     = 5; // Số lớp học phần trên mỗi trang

// Đếm tổng số bản ghi phù hợp và tính số trang
$totalRows  = $repository->count($search, $trangThai);
$totalPages = max(1, (int)ceil($totalRows / $limit));

// Giới hạn trang hiện tại không vượt quá tổng số trang
if ($page > $totalPages && $totalRows > 0) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;
$classes = $repository->getAll($search, $trangThai, $limit, $offset);

// Tính toán khoảng bản ghi đang hiển thị
$startRecord = $totalRows > 0 ? $offset + 1 : 0;
$endRecord   = min($offset + $limit, $totalRows);

// Tham số query dùng cho pagination
$queryParams = array_filter([
    'search'     => $search !== '' ? $search : null,
    'trang_thai' => $trangThai !== '' ? $trangThai : null,
]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Quản Lý Lớp Học Phần (Tạ Như Quỳnh)</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS & Font Awesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* Topbar Minimalist */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-light);
            padding: 0.85rem 0;
        }
        .brand-logo-box {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #4f46e5;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            border: 1px solid #c7d2fe;
        }

        /* Dashboard Card */
        .dashboard-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: all 0.2s ease-in-out;
        }

        /* Search Controls */
        .form-control-custom, .form-select-custom {
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.6rem 0.95rem;
            font-size: 0.92rem;
            color: var(--text-primary);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px var(--accent-ring);
            outline: none;
        }

        /* Buttons */
        .btn-accent {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.58rem 1.2rem;
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
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
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
        }

        /* Badges */
        .code-badge {
            font-family: 'Courier New', Courier, monospace;
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 0.22rem 0.6rem;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .credit-badge {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 0.2rem 0.55rem;
            font-weight: 700;
            font-size: 0.8rem;
        }

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

        /* Action Buttons */
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
        .btn-action-view:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }
        .btn-action-edit:hover {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
            transform: translateY(-2px);
        }
        .btn-action-delete:hover {
            background-color: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
            transform: translateY(-2px);
        }

        /* Pagination */
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

        /* Soft Alert */
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
    </style>
</head>
<body>
    <!-- Topbar Tối Giản Hiện Đại -->
    <header class="top-navbar mb-4 sticky-top">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="brand-logo-box">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="fs-5 fw-bold text-dark d-block lh-1">StudentHub</span>
                        <small class="text-secondary" style="font-size: 0.75rem;">Quản Lý Lớp Học Phần &bull; PHP PDO OOP</small>
                    </div>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                        <i class="fa-solid fa-user-check text-indigo me-1 text-primary"></i> 
                        Sinh viên: <strong>Tạ Như Quỳnh</strong>
                    </span>
                    <span class="badge bg-indigo-subtle text-primary border border-indigo-subtle px-3 py-2 rounded-pill small d-none d-md-inline-block">
                        BÀI TẬP VỀ NHÀ 5
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        <!-- Flash Message thông báo trạng thái -->
        <?= renderFlashMessages() ?>

        <!-- Khu vực tiêu đề trang & Nút thêm mới -->
        <div class="d-md-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">Danh Sách Lớp Học Phần</h1>
                <p class="text-secondary small mb-0">Hệ thống theo dõi thông tin học phần, giảng viên phụ trách, sĩ số và thời khóa biểu.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="create.php" class="btn btn-accent shadow-sm">
                    <i class="fa-solid fa-plus"></i> Thêm Lớp Học Phần
                </a>
            </div>
        </div>

        <!-- Thanh Tìm kiếm và Bộ Lọc Đa Tiêu Chí -->
        <div class="dashboard-card p-3 mb-4">
            <form action="index.php" method="GET" class="row g-2 align-items-center">
                <!-- Ô tìm kiếm -->
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary" style="border-radius: 10px 0 0 10px; border-color: var(--border-light);">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control form-control-custom border-start-0" 
                               style="border-radius: 0 10px 10px 0;"
                               placeholder="Tìm theo mã lớp, tên môn học, giảng viên..." 
                               value="<?= e($search) ?>" 
                               aria-label="Tìm kiếm">
                    </div>
                </div>

                <!-- Lọc trạng thái -->
                <div class="col-md-3 col-lg-3">
                    <select name="trang_thai" class="form-select form-select-custom">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="mo" <?= ($trangThai === 'mo') ? 'selected' : '' ?>>Đang mở đăng ký</option>
                        <option value="khoa" <?= ($trangThai === 'khoa') ? 'selected' : '' ?>>Đã khóa lớp</option>
                    </select>
                </div>

                <!-- Nút bấm lọc -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-filter"></i> Lọc dữ liệu
                    </button>
                </div>

                <?php if ($search !== '' || $trangThai !== ''): ?>
                    <div class="col-auto">
                        <a href="index.php" class="btn btn-soft-secondary" title="Xóa bộ lọc tìm kiếm">
                            <i class="fa-solid fa-rotate-left"></i> Đặt lại
                        </a>
                    </div>
                    <div class="col-12 mt-2">
                        <span class="text-secondary small">
                            Kết quả lọc: 
                            <?php if ($search !== ''): ?>
                                từ khóa <strong>"<?= e($search) ?>"</strong>
                            <?php endif; ?>
                            <?php if ($trangThai !== ''): ?>
                                [Trạng thái: <strong><?= $trangThai === 'mo' ? 'Đang mở' : 'Đã khóa' ?></strong>]
                            <?php endif; ?>
                            &bull; Tìm thấy <strong><?= $totalRows ?></strong> lớp học phần phù hợp
                        </span>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bảng Danh Sách Dữ Liệu Lớp Học Phần -->
        <div class="dashboard-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 60px;">ID</th>
                            <th scope="col" style="width: 140px;">Mã Lớp</th>
                            <th scope="col">Tên Môn Học</th>
                            <th scope="col" class="text-center" style="width: 90px;">Số TC</th>
                            <th scope="col" style="width: 180px;">Giảng Viên</th>
                            <th scope="col" style="width: 200px;">Lịch & Phòng Học</th>
                            <th scope="col" class="text-center" style="width: 110px;">Sĩ Số Tối Đa</th>
                            <th scope="col" class="text-center" style="width: 120px;">Trạng Thái</th>
                            <th scope="col" class="text-center" style="width: 130px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="py-4">
                                        <div class="mb-3 text-secondary opacity-50">
                                            <i class="fa-solid fa-folder-open fa-3x"></i>
                                        </div>
                                        <h6 class="fw-semibold text-dark mb-1">Không tìm thấy lớp học phần nào</h6>
                                        <p class="text-secondary small mb-3">Vui lòng điều chỉnh tiêu chí tìm kiếm hoặc thêm lớp mới.</p>
                                        <?php if ($search !== '' || $trangThai !== ''): ?>
                                            <a href="index.php" class="btn btn-sm btn-soft-secondary">
                                                Xóa bộ lọc tìm kiếm
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($classes as $item): ?>
                                <tr>
                                    <td class="text-center text-secondary small fw-medium">
                                        #<?= (int)$item['id'] ?>
                                    </td>
                                    <td>
                                        <span class="code-badge">
                                            <?= e($item['ma_lop']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detail.php?id=<?= (int)$item['id'] ?>" class="text-decoration-none fw-semibold text-dark d-block">
                                            <?= e($item['ten_mon_hoc']) ?>
                                        </a>
                                        <small class="text-secondary"><?= e($item['hoc_ky']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="credit-badge">
                                            <?= (int)$item['so_tin_chi'] ?> TC
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">
                                            <i class="fa-solid fa-chalkboard-user text-secondary me-1" style="font-size: 0.85rem;"></i>
                                            <?= e($item['giang_vien']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">
                                            <i class="fa-regular fa-calendar-days text-secondary me-1"></i>
                                            <?= e($item['lich_hoc']) ?>
                                        </div>
                                        <div class="small text-secondary">
                                            <i class="fa-solid fa-location-dot text-danger-emphasis me-1"></i>
                                            <?= e($item['phong_hoc']) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small fw-semibold">
                                            <?= (int)$item['si_so_toi_da'] ?> SV
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= renderTrangThaiBadge($item['trang_thai']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btn-group">
                                            <!-- Xem chi tiết -->
                                            <a href="detail.php?id=<?= (int)$item['id'] ?>" 
                                               class="btn-action-icon btn-action-view" 
                                               data-bs-toggle="tooltip" 
                                               title="Xem chi tiết lớp học phần">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- Sửa lớp học phần -->
                                            <a href="edit.php?id=<?= (int)$item['id'] ?>" 
                                               class="btn-action-icon btn-action-edit" 
                                               data-bs-toggle="tooltip" 
                                               title="Chỉnh sửa thông tin">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <!-- Xóa qua POST form an toàn -->
                                            <form action="delete.php" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa lớp học phần: &quot;<?= e($item['ten_mon_hoc']) ?>&quot; (Mã: <?= e($item['ma_lop']) ?>) không?\nHành động này không thể hoàn tác!');">
                                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                                <button type="submit" 
                                                        class="btn-action-icon btn-action-delete" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Xóa lớp học phần">
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

            <!-- Footer Bảng: Thống kê & Phân trang an toàn -->
            <?php if ($totalRows > 0): ?>
                <div class="p-3 border-top d-md-flex align-items-center justify-content-between" style="border-color: var(--border-light) !important;">
                    <div class="text-secondary small mb-2 mb-md-0">
                        Hiển thị <strong><?= $startRecord ?></strong> - <strong><?= $endRecord ?></strong> trên tổng số <strong><?= $totalRows ?></strong> lớp học phần
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Phân trang danh sách lớp học phần">
                            <ul class="pagination pagination-custom justify-content-center justify-content-md-end mb-0">
                                <!-- Trang đầu -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => 1])) ?>" title="Trang đầu">
                                        <i class="fa-solid fa-angles-left"></i>
                                    </a>
                                </li>

                                <!-- Lùi 1 trang -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>" title="Trang trước">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </a>
                                </li>

                                <!-- Các số trang -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Tới 1 trang -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>" title="Trang sau">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </a>
                                </li>

                                <!-- Trang cuối -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $totalPages])) ?>" title="Trang cuối">
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
        // Kích hoạt Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>
</body>
</html>
