-- ============================================================
-- CSDL: shopdemo
-- Mô tả: Cấu trúc cơ sở dữ liệu cho hệ thống quản lý sản phẩm CRUD
-- Tương thích: MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `shopdemo` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `shopdemo`;

-- Xóa bảng cũ nếu đã tồn tại để tránh xung đột
DROP TABLE IF EXISTS `products`;

-- Bảng lưu trữ thông tin sản phẩm
CREATE TABLE `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `quantity` INT NOT NULL DEFAULT 0,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `uq_products_code` UNIQUE (`code`),
    INDEX `idx_products_name` (`name`),
    INDEX `idx_products_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
