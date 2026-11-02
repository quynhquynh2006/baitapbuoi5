<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/LopHocPhanRepository.php';

$repository = new LopHocPhanRepository(getDatabaseConnection());

// Lấy ID từ URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>404 - Không Tìm Thấy Lớp Học Phần</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .card-404 { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05); }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card-404 py-5 px-4">
                        <h2 class="fw-bold mb-2 text-danger">404 Not Found</h2>
                        <p class="text-secondary small mb-4">ID lớp học phần không hợp lệ hoặc thiếu.</p>
                        <a href="index.php" class="btn btn-primary" style="background:#4f46e5; border-color:#4f46e5; border-radius:10px;">Quay lại danh sách</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$classItem = $repository->getById((int)$id);

if (!$classItem) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>404 - Không Tìm Thấy Lớp Học Phần</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #0f172a; }
            .card-404 { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05); }
        </style>
    </head>
    <body class="d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card-404 py-5 px-4">
                        <h2 class="fw-bold mb-2 text-danger">404 Not Found</h2>
                        <p class="text-secondary small mb-4">Không tìm thấy lớp học phần có ID #<?= (int)$id ?> để cập nhật.</p>
                        <a href="index.php" class="btn btn-primary" style="background:#4f46e5; border-color:#4f46e5; border-radius:10px;">Quay lại danh sách</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$errors = [];
$formData = [
    'ma_lop'       => $classItem['ma_lop'],
    'ten_mon_hoc'  => $classItem['ten_mon_hoc'],
    'so_tin_chi'   => (string)$classItem['so_tin_chi'],
    'giang_vien'   => $classItem['giang_vien'],
    'phong_hoc'    => $classItem['phong_hoc'],
    'lich_hoc'     => $classItem['lich_hoc'],
    'si_so_toi_da' => (string)$classItem['si_so_toi_da'],
    'trang_thai'   => $classItem['trang_thai'],
    'hoc_ky'       => $classItem['hoc_ky'],
];

// Xử lý khi gửi form cập nhật qua POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['ma_lop']       = strtoupper(trim((string)($_POST['ma_lop'] ?? '')));
    $formData['ten_mon_hoc']  = trim((string)($_POST['ten_mon_hoc'] ?? ''));
    $formData['so_tin_chi']   = trim((string)($_POST['so_tin_chi'] ?? ''));
    $formData['giang_vien']   = trim((string)($_POST['giang_vien'] ?? ''));
    $formData['phong_hoc']    = trim((string)($_POST['phong_hoc'] ?? ''));
    $formData['lich_hoc']     = trim((string)($_POST['lich_hoc'] ?? ''));
    $formData['si_so_toi_da'] = trim((string)($_POST['si_so_toi_da'] ?? ''));
    $formData['trang_thai']   = trim((string)($_POST['trang_thai'] ?? 'mo'));
    $formData['hoc_ky']       = trim((string)($_POST['hoc_ky'] ?? ''));

    // 1. Kiểm tra mã lớp
    if ($formData['ma_lop'] === '') {
        $errors['ma_lop'] = 'Mã lớp học phần không được để trống.';
    } elseif (!preg_match('/^[A-Z0-9_-]{3,50}$/', $formData['ma_lop'])) {
        $errors['ma_lop'] = 'Mã lớp phải từ 3 đến 50 ký tự (chữ hoa, số, dấu gạch nối - hoặc _).';
    } else {
        // Kiểm tra trùng mã lớp (loại trừ ID hiện tại)
        $existing = $repository->getByMaLop($formData['ma_lop'], (int)$id);
        if ($existing !== null) {
            $errors['ma_lop'] = 'Mã lớp "' . $formData['ma_lop'] . '" đã được sử dụng bởi lớp khác (#ID: ' . $existing['id'] . ').';
        }
    }

    // 2. Kiểm tra tên môn học
    if ($formData['ten_mon_hoc'] === '') {
        $errors['ten_mon_hoc'] = 'Tên môn học không được để trống.';
    } elseif (mb_strlen($formData['ten_mon_hoc']) < 3 || mb_strlen($formData['ten_mon_hoc']) > 255) {
        $errors['ten_mon_hoc'] = 'Tên môn học phải từ 3 đến 255 ký tự.';
    }

    // 3. Kiểm tra số tín chỉ
    if ($formData['so_tin_chi'] === '') {
        $errors['so_tin_chi'] = 'Số tín chỉ không được để trống.';
    } elseif (filter_var($formData['so_tin_chi'], FILTER_VALIDATE_INT) === false || (int)$formData['so_tin_chi'] < 1 || (int)$formData['so_tin_chi'] > 10) {
        $errors['so_tin_chi'] = 'Số tín chỉ phải là số nguyên từ 1 đến 10.';
    }

    // 4. Kiểm tra giảng viên phụ trách
    if ($formData['giang_vien'] === '') {
        $errors['giang_vien'] = 'Tên giảng viên phụ trách không được để trống.';
    } elseif (mb_strlen($formData['giang_vien']) < 3 || mb_strlen($formData['giang_vien']) > 150) {
        $errors['giang_vien'] = 'Tên giảng viên phụ trách phải từ 3 đến 150 ký tự.';
    }

    // 5. Kiểm tra phòng học
    if ($formData['phong_hoc'] === '') {
        $errors['phong_hoc'] = 'Phòng học không được để trống.';
    } elseif (mb_strlen($formData['phong_hoc']) < 2 || mb_strlen($formData['phong_hoc']) > 50) {
        $errors['phong_hoc'] = 'Phòng học phải từ 2 đến 50 ký tự.';
    }

    // 6. Kiểm tra lịch học
    if ($formData['lich_hoc'] === '') {
        $errors['lich_hoc'] = 'Lịch học không được để trống.';
    } elseif (mb_strlen($formData['lich_hoc']) < 5 || mb_strlen($formData['lich_hoc']) > 150) {
        $errors['lich_hoc'] = 'Lịch học phải từ 5 đến 150 ký tự.';
    }

    // 7. Kiểm tra sĩ số tối đa
    if ($formData['si_so_toi_da'] === '') {
        $errors['si_so_toi_da'] = 'Sĩ số tối đa không được để trống.';
    } elseif (filter_var($formData['si_so_toi_da'], FILTER_VALIDATE_INT) === false || (int)$formData['si_so_toi_da'] < 10 || (int)$formData['si_so_toi_da'] > 200) {
        $errors['si_so_toi_da'] = 'Sĩ số tối đa phải là số nguyên từ 10 đến 200 sinh viên.';
    }

    // 8. Kiểm tra trạng thái
    if (!in_array($formData['trang_thai'], ['mo', 'khoa'], true)) {
        $errors['trang_thai'] = 'Trạng thái lớp không hợp lệ.';
    }

    // Nếu không có lỗi, cập nhật CSDL và điều hướng PRG
    if (empty($errors)) {
        $updateData = [
            'ma_lop'       => $formData['ma_lop'],
            'ten_mon_hoc'  => $formData['ten_mon_hoc'],
            'so_tin_chi'   => (int)$formData['so_tin_chi'],
            'giang_vien'   => $formData['giang_vien'],
            'phong_hoc'    => $formData['phong_hoc'],
            'lich_hoc'     => $formData['lich_hoc'],
            'si_so_toi_da' => (int)$formData['si_so_toi_da'],
            'trang_thai'   => $formData['trang_thai'],
            'hoc_ky'       => $formData['hoc_ky'],
        ];

        $repository->update((int)$id, $updateData);

        // Flash message
        setFlash('success', 'Cập nhật thông tin lớp học phần "' . $formData['ten_mon_hoc'] . '" (Mã: ' . $formData['ma_lop'] . ') thành công!');

        // POST-Redirect-GET
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa #<?= (int)$classItem['id'] ?> - <?= e($classItem['ten_mon_hoc']) ?></title>
    
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

        .dashboard-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .form-control, .form-select {
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.65rem 0.95rem;
            font-size: 0.925rem;
            color: var(--text-primary);
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px var(--accent-ring);
            outline: none;
        }

        .btn-accent {
            background-color: var(--accent-primary);
            border-color: var(--accent-primary);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.6rem 1.4rem;
            font-weight: 600;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }
        .btn-accent:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
            transform: translateY(-1px);
        }

        .btn-soft-back {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s;
        }
        .btn-soft-back:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }

        .alert-soft-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 1rem 1.25rem;
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
                <li class="breadcrumb-item"><a href="detail.php?id=<?= (int)$classItem['id'] ?>" class="text-decoration-none text-secondary">Chi tiết #<?= (int)$classItem['id'] ?></a></li>
                <li class="breadcrumb-item text-secondary active" aria-current="page">Chỉnh sửa</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="dashboard-card overflow-hidden">
                    <div class="p-4 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--border-light) !important;">
                        <div>
                            <h2 class="h4 fw-bold text-dark mb-1">
                                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Lớp Học Phần
                            </h2>
                            <p class="text-secondary small mb-0">Cập nhật thông tin cho lớp học phần #<?= (int)$classItem['id'] ?> - <?= e($classItem['ma_lop']) ?>.</p>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                            ID: #<?= (int)$classItem['id'] ?>
                        </span>
                    </div>

                    <div class="p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert-soft-danger mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                                    <strong>Vui lòng kiểm tra lại các trường dữ liệu:</strong>
                                </div>
                                <ul class="mb-0 ps-4 small">
                                    <?php foreach ($errors as $errorMsg): ?>
                                        <li><?= e($errorMsg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="edit.php?id=<?= (int)$classItem['id'] ?>" method="POST" novalidate>
                            <div class="row g-3">
                                <!-- Mã lớp -->
                                <div class="col-md-6">
                                    <label for="ma_lop" class="form-label">
                                        Mã Lớp Học Phần <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['ma_lop']) ? 'is-invalid' : '' ?>" 
                                           id="ma_lop" 
                                           name="ma_lop" 
                                           value="<?= e($formData['ma_lop']) ?>" 
                                           required>
                                    <?php if (isset($errors['ma_lop'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['ma_lop']) ?></div>
                                    <?php else: ?>
                                        <div class="form-text text-secondary" style="font-size: 0.8rem;">3-50 ký tự in hoa, số, gạch ngang (-) hoặc gạch dưới (_).</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tên môn học -->
                                <div class="col-md-6">
                                    <label for="ten_mon_hoc" class="form-label">
                                        Tên Môn Học <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['ten_mon_hoc']) ? 'is-invalid' : '' ?>" 
                                           id="ten_mon_hoc" 
                                           name="ten_mon_hoc" 
                                           value="<?= e($formData['ten_mon_hoc']) ?>" 
                                           required>
                                    <?php if (isset($errors['ten_mon_hoc'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['ten_mon_hoc']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Số tín chỉ -->
                                <div class="col-md-4">
                                    <label for="so_tin_chi" class="form-label">
                                        Số Tín Chỉ <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           min="1" 
                                           max="10" 
                                           class="form-control <?= isset($errors['so_tin_chi']) ? 'is-invalid' : '' ?>" 
                                           id="so_tin_chi" 
                                           name="so_tin_chi" 
                                           value="<?= e($formData['so_tin_chi']) ?>" 
                                           required>
                                    <?php if (isset($errors['so_tin_chi'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['so_tin_chi']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Giảng viên phụ trách -->
                                <div class="col-md-8">
                                    <label for="giang_vien" class="form-label">
                                        Giảng Viên Phụ Trách <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['giang_vien']) ? 'is-invalid' : '' ?>" 
                                           id="giang_vien" 
                                           name="giang_vien" 
                                           value="<?= e($formData['giang_vien']) ?>" 
                                           required>
                                    <?php if (isset($errors['giang_vien'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['giang_vien']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Phòng học -->
                                <div class="col-md-6">
                                    <label for="phong_hoc" class="form-label">
                                        Phòng Học <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['phong_hoc']) ? 'is-invalid' : '' ?>" 
                                           id="phong_hoc" 
                                           name="phong_hoc" 
                                           value="<?= e($formData['phong_hoc']) ?>" 
                                           required>
                                    <?php if (isset($errors['phong_hoc'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['phong_hoc']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Lịch học -->
                                <div class="col-md-6">
                                    <label for="lich_hoc" class="form-label">
                                        Lịch Học <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['lich_hoc']) ? 'is-invalid' : '' ?>" 
                                           id="lich_hoc" 
                                           name="lich_hoc" 
                                           value="<?= e($formData['lich_hoc']) ?>" 
                                           required>
                                    <?php if (isset($errors['lich_hoc'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['lich_hoc']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Sĩ số tối đa -->
                                <div class="col-md-4">
                                    <label for="si_so_toi_da" class="form-label">
                                        Sĩ Số Tối Đa (SV) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           min="10" 
                                           max="200" 
                                           class="form-control <?= isset($errors['si_so_toi_da']) ? 'is-invalid' : '' ?>" 
                                           id="si_so_toi_da" 
                                           name="si_so_toi_da" 
                                           value="<?= e($formData['si_so_toi_da']) ?>" 
                                           required>
                                    <?php if (isset($errors['si_so_toi_da'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['si_so_toi_da']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Trạng thái -->
                                <div class="col-md-4">
                                    <label for="trang_thai" class="form-label">
                                        Trạng Thái Đăng Ký <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($errors['trang_thai']) ? 'is-invalid' : '' ?>" 
                                            id="trang_thai" 
                                            name="trang_thai" 
                                            required>
                                        <option value="mo" <?= ($formData['trang_thai'] === 'mo') ? 'selected' : '' ?>>
                                            Đang mở đăng ký
                                        </option>
                                        <option value="khoa" <?= ($formData['trang_thai'] === 'khoa') ? 'selected' : '' ?>>
                                            Đã khóa lớp
                                        </option>
                                    </select>
                                    <?php if (isset($errors['trang_thai'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['trang_thai']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Học kỳ -->
                                <div class="col-md-4">
                                    <label for="hoc_ky" class="form-label">
                                        Học Kỳ Áp Dụng <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="hoc_ky" 
                                           name="hoc_ky" 
                                           value="<?= e($formData['hoc_ky']) ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top" style="border-color: var(--border-light) !important;">
                                <a href="index.php" class="btn-soft-back">
                                    <i class="fa-solid fa-arrow-left"></i> Hủy & Quay Lại
                                </a>
                                <button type="submit" class="btn btn-accent">
                                    <i class="fa-solid fa-check"></i> Lưu Thay Đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
