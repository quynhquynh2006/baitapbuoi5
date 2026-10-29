<?php
declare(strict_types=1);

/**
 * Helpers quản lý Session, Flash Messages, Sanitization và Chuyển hướng
 */

// Khởi động session an toàn nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Thiết lập Flash Message lưu vào Session
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
 * Lấy ra danh sách toàn bộ Flash Messages và xóa khỏi Session (Chỉ hiển thị 1 lần)
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
 * Render trực tiếp HTML Bootstrap 5 Alert phong cách Soft Pastel cho các Flash Messages
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
        $type = htmlspecialchars($rawType, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        $icon = $iconMap[$rawType] ?? '<i class="fa-solid fa-bell me-2 fs-5"></i>';

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
 * Định dạng số tiền sang định dạng tiền tệ Việt Nam Đồng (VND)
 *
 * @param mixed $amount
 * @return string
 */
function formatCurrency(mixed $amount): string
{
    $val = (float)($amount ?? 0);
    return number_format($val, 0, ',', '.') . ' ₫';
}

/**
 * Định dạng ngày giờ chuẩn hiển thị thân thiện (d/m/Y H:i)
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
 * Khởi tạo hoặc lấy CSRF token hiện tại trong session
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
 * Kiểm tra tính hợp lệ của CSRF token
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
