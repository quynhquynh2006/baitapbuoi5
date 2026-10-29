<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/repositories/ProductRepository.php';

$repository = new ProductRepository(getDatabaseConnection());

$errors = [];
$formData = [
    'code'        => '',
    'name'        => '',
    'price'       => '',
    'quantity'    => '0',
    'status'      => 'active',
    'description' => '',
];

// Xử lý khi người dùng gửi form bằng phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thu thập và làm sạch dữ liệu đầu vào
    $formData['code']        = strtoupper(trim((string)($_POST['code'] ?? '')));
    $formData['name']        = trim((string)($_POST['name'] ?? ''));
    $formData['price']       = trim((string)($_POST['price'] ?? ''));
    $formData['quantity']    = trim((string)($_POST['quantity'] ?? '0'));
    $formData['status']      = trim((string)($_POST['status'] ?? 'active'));
    $formData['description'] = trim((string)($_POST['description'] ?? ''));

    // 1. Kiểm tra mã sản phẩm (code)
    if ($formData['code'] === '') {
        $errors['code'] = 'Mã sản phẩm không được để trống.';
    } elseif (!preg_match('/^[A-Z0-9_-]{3,50}$/', $formData['code'])) {
        $errors['code'] = 'Mã sản phẩm từ 3-50 ký tự, chỉ gồm chữ hoa, số, dấu gạch nối (-) hoặc gạch dưới (_).';
    } else {
        // Kiểm tra mã sản phẩm duy nhất thông qua Repository (Prepared Statement)
        $existingProduct = $repository->getByCode($formData['code']);
        if ($existingProduct !== null) {
            $errors['code'] = 'Mã sản phẩm "' . $formData['code'] . '" đã tồn tại trong hệ thống. Vui lòng chọn mã khác.';
        }
    }

    // 2. Kiểm tra tên sản phẩm
    if ($formData['name'] === '') {
        $errors['name'] = 'Tên sản phẩm không được để trống.';
    } elseif (mb_strlen($formData['name']) < 2 || mb_strlen($formData['name']) > 255) {
        $errors['name'] = 'Tên sản phẩm phải có độ dài từ 2 đến 255 ký tự.';
    }

    // 3. Kiểm tra đơn giá
    if ($formData['price'] === '') {
        $errors['price'] = 'Đơn giá không được để trống.';
    } elseif (!is_numeric($formData['price']) || (float)$formData['price'] < 0) {
        $errors['price'] = 'Đơn giá phải là số dương hợp lệ (>= 0).';
    }

    // 4. Kiểm tra số lượng tồn kho
    if ($formData['quantity'] === '') {
        $errors['quantity'] = 'Số lượng không được để trống.';
    } elseif (filter_var($formData['quantity'], FILTER_VALIDATE_INT) === false || (int)$formData['quantity'] < 0) {
        $errors['quantity'] = 'Số lượng tồn kho phải là số nguyên không âm (>= 0).';
    }

    // 5. Kiểm tra trạng thái
    if (!in_array($formData['status'], ['active', 'inactive'], true)) {
        $errors['status'] = 'Trạng thái không hợp lệ.';
    }

    // Nếu không có lỗi xác thực, thực hiện lưu vào CSDL (Mô hình POST-Redirect-GET)
    if (empty($errors)) {
        $insertData = [
            'code'        => $formData['code'],
            'name'        => $formData['name'],
            'price'       => number_format((float)$formData['price'], 2, '.', ''),
            'quantity'    => (int)$formData['quantity'],
            'description' => $formData['description'] !== '' ? $formData['description'] : null,
            'status'      => $formData['status'],
        ];

        $newId = $repository->create($insertData);

        // Lưu Flash Message vào Session
        setFlash('success', 'Thêm mới sản phẩm "' . $formData['name'] . '" (Mã: ' . $formData['code'] . ') thành công!');

        // Điều hướng về trang danh sách để chống submit đúp (PRG Pattern)
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm Mới - ShopDemo</title>
    
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

        /* Form Card */
        .dashboard-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        /* Form Controls */
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
        .input-group-text {
            background-color: #f8fafc;
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
            border-radius: 0 10px 10px 0;
        }

        /* Buttons */
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

        /* Soft Alert */
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Sản phẩm</a></li>
                <li class="breadcrumb-item text-secondary active" aria-current="page">Thêm mới sản phẩm</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="dashboard-card overflow-hidden">
                    <div class="p-4 border-bottom" style="border-color: var(--border-light) !important;">
                        <h2 class="h4 fw-bold text-dark mb-1">Thêm Sản Phẩm Mới</h2>
                        <p class="text-secondary small mb-0">Điền thông tin chi tiết để khởi tạo mặt hàng vào hệ thống dữ liệu.</p>
                    </div>

                    <div class="p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert-soft-danger mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                                    <strong>Vui lòng kiểm tra lại thông tin biểu mẫu:</strong>
                                </div>
                                <ul class="mb-0 ps-4 small">
                                    <?php foreach ($errors as $errorMsg): ?>
                                        <li><?= e($errorMsg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="create.php" method="POST" novalidate>
                            <div class="row g-3">
                                <!-- Mã sản phẩm -->
                                <div class="col-md-6">
                                    <label for="code" class="form-label">
                                        Mã Sản Phẩm (SKU) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                                           id="code" 
                                           name="code" 
                                           placeholder="Ví dụ: LAP-DELL-01" 
                                           value="<?= e($formData['code']) ?>" 
                                           required>
                                    <?php if (isset($errors['code'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['code']) ?></div>
                                    <?php else: ?>
                                        <div class="form-text text-secondary" style="font-size: 0.8rem;">Từ 3-50 ký tự in hoa, số, gạch ngang (-) hoặc gạch dưới (_).</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Trạng thái -->
                                <div class="col-md-6">
                                    <label for="status" class="form-label">
                                        Trạng Thái Kinh Doanh <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="active" <?= ($formData['status'] === 'active') ? 'selected' : '' ?>>
                                            Đang bán (Active)
                                        </option>
                                        <option value="inactive" <?= ($formData['status'] === 'inactive') ? 'selected' : '' ?>>
                                            Ngừng bán (Inactive)
                                        </option>
                                    </select>
                                    <?php if (isset($errors['status'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['status']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Tên sản phẩm -->
                                <div class="col-12">
                                    <label for="name" class="form-label">
                                        Tên Sản Phẩm <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           id="name" 
                                           name="name" 
                                           placeholder="Nhập tên chi tiết sản phẩm..." 
                                           value="<?= e($formData['name']) ?>" 
                                           required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['name']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Giá bán -->
                                <div class="col-md-6">
                                    <label for="price" class="form-label">
                                        Đơn Giá Bán (VNĐ) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                               id="price" 
                                               name="price" 
                                               placeholder="0" 
                                               value="<?= e($formData['price']) ?>" 
                                               required>
                                        <span class="input-group-text">₫</span>
                                        <?php if (isset($errors['price'])): ?>
                                            <div class="invalid-feedback small"><?= e($errors['price']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Số lượng -->
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">
                                        Số Lượng Tồn Kho <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           step="1" 
                                           min="0" 
                                           class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>" 
                                           id="quantity" 
                                           name="quantity" 
                                           placeholder="0" 
                                           value="<?= e($formData['quantity']) ?>" 
                                           required>
                                    <?php if (isset($errors['quantity'])): ?>
                                        <div class="invalid-feedback small"><?= e($errors['quantity']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Mô tả chi tiết -->
                                <div class="col-12">
                                    <label for="description" class="form-label">Mô Tả Sản Phẩm</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Nhập thông tin giới thiệu, quy cách sản phẩm..."><?= e($formData['description']) ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-4 mt-3 border-top" style="border-color: var(--border-light) !important;">
                                <a href="index.php" class="btn-soft-back">
                                    <i class="fa-solid fa-arrow-left"></i> Hủy & Quay Lại
                                </a>
                                <button type="submit" class="btn btn-accent">
                                    <i class="fa-solid fa-check"></i> Lưu Sản Phẩm
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
