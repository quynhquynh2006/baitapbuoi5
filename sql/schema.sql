-- ============================================================
-- HỆ THỐNG QUẢN LÝ LỚP HỌC PHẦN (StudentHub - lop_hoc_phan)
-- Sinh viên thực hiện: Tạ Như Quỳnh - Bài Tập Về Nhà 5
-- CSDL: studenthub_personal
-- Bảng: lop_hoc_phan
-- Chuẩn mã hóa: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `studenthub_personal` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `studenthub_personal`;

-- Xóa bảng cũ nếu tồn tại để khởi tạo mới đồng bộ
DROP TABLE IF EXISTS `lop_hoc_phan`;

-- Tạo bảng quản lý lớp học phần
CREATE TABLE IF NOT EXISTS `lop_hoc_phan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_lop` VARCHAR(50) NOT NULL UNIQUE,
    `ten_mon_hoc` VARCHAR(255) NOT NULL,
    `so_tin_chi` INT NOT NULL,
    `giang_vien` VARCHAR(150) NOT NULL,
    `phong_hoc` VARCHAR(50) NOT NULL,
    `lich_hoc` VARCHAR(150) NOT NULL,
    `si_so_toi_da` INT NOT NULL DEFAULT 40,
    `trang_thai` ENUM('mo', 'khoa') NOT NULL DEFAULT 'mo',
    `hoc_ky` VARCHAR(50) NOT NULL DEFAULT 'Học kỳ 1 (2026 - 2027)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_lop_ten_mon` (`ten_mon_hoc`),
    INDEX `idx_lop_giang_vien` (`giang_vien`),
    INDEX `idx_lop_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
