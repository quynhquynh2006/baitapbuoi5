<?php
declare(strict_types=1);

/**
 * ============================================================================
 * HELPER QUẢN LÝ SESSION, FLASH MESSAGES & TIỆN ÍCH BẢO MẬT
 * Dự án: StudentHub - Quản Lý Lớp Học Phần (BÀI TẬP VỀ NHÀ 5)
 * Sinh viên thực hiện: Tạ Như Quỳnh
 * ============================================================================
 */

// Khởi động session an toàn nếu chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Thiết lập Flash Message lưu tạm vào Session
 *
 * @param string $type Loại thông báo ('success', 'danger', 'warning', 'info')
 * @param string $message Nội dung thông báo
 * @return void
 */
function setFlash(string $type, string $message): void
{
    if (!isset($_SESSION['_flash_messages']) || !is_array($_SESSION['_flash_messages'])) {
        $_SESSION['_flash_messages'] = [];
    }
    $_SESSION['_flash_messages'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Kiểm tra xem có flash message nào đang chờ hiển thị hay không
 *
 * @return bool
 */
function hasFlash(): bool
{
    return !empty($_SESSION['_flash_messages']);
}

/**
 * Lấy danh sách Flash Messages và xóa khỏi Session (Chỉ hiển thị 1 lần)
 *
 * @return array
 */
function getFlashes(): array
{
    if (!isset($_SESSION['_flash_messages'])) {
        return [];
    }

    $messages = $_SESSION['_flash_messages'];
    unset($_SESSION['_flash_messages']);
    return $messages;
}

/**
 * Render trực tiếp HTML Bootstrap 5 Alert phong cách Soft Pastel cho Flash Messages
 *
 * @return string
 */
function renderFlashMessages(): string
{
    $flashes = getFlashes();
    if (empty($flashes)) {
        return '';
    }

    $iconMap = [
        'success' => '<i class="fa-solid fa-circle-check text-emerald-600 me-2 fs-5"></i>',
        'danger'  => '<i class="fa-solid fa-circle-exclamation text-rose-600 me-2 fs-5"></i>',
        'warning' => '<i class="fa-solid fa-triangle-exclamation text-amber-600 me-2 fs-5"></i>',
        'info'    => '<i class="fa-solid fa-circle-info text-indigo-600 me-2 fs-5"></i>',
    ];

    $html = '';
    foreach ($flashes as $flash) {
        $rawType = $flash['type'];
        $type    = htmlspecialchars($rawType, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        $icon    = $iconMap[$rawType] ?? '<i class="fa-solid fa-bell me-2 fs-5"></i>';

        $html .= <<<HTML
        <div class="alert alert-{$type} alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4 custom-pastel-alert" role="alert">
            <div class="d-flex align-items-center flex-grow-1">
                {$icon}
                <div class="fw-medium">{$message}</div>
            </div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
HTML;
    }
    return $html;
}

/**
 * Thoát các ký tự đặc biệt trong chuỗi để phòng chống tấn công XSS (Cross-Site Scripting)
 *
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Điều hướng người dùng về một URL và dừng thực thi kịch bản (POST-Redirect-GET)
 *
 * @param string $url
 * @return void
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/**
 * Định dạng ngày giờ thân thiện theo chuẩn Việt Nam (d/m/Y H:i)
 *
 * @param string|null $datetime
 * @return string
 */
function formatDateTime(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }
    $time = strtotime($datetime);
    if ($time === false) {
        return $datetime;
    }
    return date('d/m/Y H:i', $time);
}

/**
 * Render Soft Badge trạng thái lớp học phần
 *
 * @param string $status 'mo' hoặc 'khoa'
 * @return string
 */
function renderTrangThaiBadge(string $status): string
{
    if ($status === 'mo') {
        return '<span class="badge-soft-active"><i class="fa-solid fa-circle text-emerald-500" style="font-size: 6px;"></i> Đang mở</span>';
    }
    return '<span class="badge-soft-inactive"><i class="fa-solid fa-lock text-slate-400" style="font-size: 8px;"></i> Đã khóa</span>';
}

/**
 * Lấy CSRF token từ session
 *
 * @return string
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Xác thực tính hợp lệ của CSRF token
 *
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}
