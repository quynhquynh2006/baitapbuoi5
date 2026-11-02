<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/LopHocPhanRepository.php';

$repository = new LopHocPhanRepository(getDatabaseConnection());

// Lấy và kiểm tra ID từ tham số URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$classItem = null;
if ($id && $id > 0) {
    $classItem = $repository->getById((int)$id);
}

// Nếu không tìm thấy hoặc ID không hợp lệ, trả về HTTP 404
if (!$classItem) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Không Tìm Thấy Lớp Học Phần</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .card-404 { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05); }
            .btn-accent { background-color: #4f46e5; border-color: #4f46e5; color: #ffffff; border-radius: 10px; font-weight: 600; padding: 0.6rem 1.4rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; }
            .btn-accent:hover { background-color: #4338ca; color: #ffffff; }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card-404 py-5 px-4">
                        <div class="mb-4">
                            <span class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 76px; height: 76px;">
                                <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                            </span>
                        </div>
                        <h2 class="fw-bold mb-2 text-dark">404 - Lớp Học Phần Không Tồn Tại</h2>
                        <p class="text-secondary small mb-4">
                            Lớp học phần với định danh <code>ID: <?= e($_GET['id'] ?? 'N/A') ?></code> không tồn tại trên hệ thống hoặc đã được xóa trước đó.
                        </p>
                        <a href="index.php" class="btn-accent">
                            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách lớp học phần
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
    <title><?= e($classItem['ma_lop']) ?> - <?= e($classItem['ten_mon_hoc']) ?> | StudentHub</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Font Awesome 6 -->
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

        /* Bento Grid Card Design */
        .bento-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 18px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bento-card:hover {
            box-shadow: 0 8px 30px -4px rgba(15, 23, 42, 0.08);
        }

        .bento-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e0e7ff;
        }

        .bento-icon-circle {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        /* Badges */
        .code-badge-lg {
            font-family: 'Courier New', Courier, monospace;
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            padding: 0.35rem 0.85rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

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
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span class="fs-5 fw-bold text-dark d-block lh-1">StudentHub</span>
                        <small class="text-secondary" style="font-size: 0.75rem;">Quản Lý Lớp Học Phần &bull; Tạ Như Quỳnh</small>
                    </div>
                </a>
                <a href="index.php" class="btn-soft-back small">
                    <i class="fa-solid fa-arrow-left"></i> Danh sách
                </a>
            </div>
        </div>
    </header>

    <main class="container pb-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Lớp học phần</a></li>
                <li class="breadcrumb-item text-secondary active" aria-current="page">Chi tiết #<?= (int)$classItem['id'] ?></li>
            </ol>
        </nav>

        <!-- Bento Card Layout -->
        <div class="row g-4">
            <!-- 1. BENTO HERO CARD (Toàn chiều rộng): Tiêu đề & Mã lớp & Học kỳ -->
            <div class="col-12">
                <div class="bento-card bento-hero">
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="code-badge-lg"><?= e($classItem['ma_lop']) ?></span>
                                <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small">
                                    ID #<?= (int)$classItem['id'] ?>
                                </span>
                            </div>
                            <h1 class="h3 fw-bold text-dark mb-1"><?= e($classItem['ten_mon_hoc']) ?></h1>
                            <p class="text-secondary mb-0 small">
                                <i class="fa-regular fa-calendar-check me-1 text-primary"></i>
                                <?= e($classItem['hoc_ky']) ?> &bull; Khoa Công nghệ Thông tin
                            </p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <?= renderTrangThaiBadge($classItem['trang_thai']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. BENTO CARD: Giảng viên phụ trách -->
            <div class="col-md-6 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon-circle bg-primary-subtle text-primary">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <span class="text-secondary small text-uppercase fw-bold letter-spacing-1 d-block mb-1">
                        Cán Bộ Giảng Dạy
                    </span>
                    <h5 class="fw-bold text-dark mb-2"><?= e($classItem['giang_vien']) ?></h5>
                    <p class="text-secondary small mb-0">
                        Phụ trách chuyên môn, hướng dẫn bài tập thực hành & chấm thi kết thúc học phần.
                    </p>
                </div>
            </div>

            <!-- 3. BENTO CARD: Địa điểm & Thời khóa biểu -->
            <div class="col-md-6 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon-circle bg-amber-subtle text-warning-emphasis" style="background:#fef3c7; color:#b45309;">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <span class="text-secondary small text-uppercase fw-bold letter-spacing-1 d-block mb-1">
                        Lịch Học & Phòng Học
                    </span>
                    <h5 class="fw-bold text-dark mb-1"><?= e($classItem['lich_hoc']) ?></h5>
                    <div class="text-dark small fw-semibold">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i>
                        <?= e($classItem['phong_hoc']) ?>
                    </div>
                </div>
            </div>

            <!-- 4. BENTO CARD: Tín chỉ & Sĩ số sinh viên -->
            <div class="col-md-12 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon-circle bg-success-subtle text-success">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="text-secondary small text-uppercase fw-bold letter-spacing-1 d-block mb-1">
                        Đào Tạo & Quy Mô
                    </span>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="text-secondary small d-block">Số tín chỉ</span>
                            <span class="fs-4 fw-bold text-success"><?= (int)$classItem['so_tin_chi'] ?> TC</span>
                        </div>
                        <div class="vr my-1"></div>
                        <div>
                            <span class="text-secondary small d-block">Sĩ số tối đa</span>
                            <span class="fs-4 fw-bold text-dark"><?= (int)$classItem['si_so_toi_da'] ?> SV</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. BENTO CARD: Thông tin quản trị & Thời gian -->
            <div class="col-12">
                <div class="bento-card">
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-4 text-secondary small mb-3 mb-md-0">
                            <div>
                                <i class="fa-regular fa-clock me-1"></i>
                                Thời gian tạo: <strong class="text-dark"><?= formatDateTime($classItem['created_at']) ?></strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-arrows-rotate me-1"></i>
                                Cập nhật: <strong class="text-dark"><?= formatDateTime($classItem['updated_at']) ?></strong>
                            </div>
                        </div>

                        <!-- Cụm nút thao tác -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="index.php" class="btn-soft-back">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="edit.php?id=<?= (int)$classItem['id'] ?>" class="btn-soft-edit">
                                <i class="fa-solid fa-pen"></i> Chỉnh sửa
                            </a>
                            <form action="delete.php" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa lớp học phần: &quot;<?= e($classItem['ten_mon_hoc']) ?>&quot; không?\nHành động này không thể hoàn tác!');">
                                <input type="hidden" name="id" value="<?= (int)$classItem['id'] ?>">
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
