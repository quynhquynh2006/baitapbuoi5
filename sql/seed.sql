-- ============================================================
-- CSDL: shopdemo
-- Dữ liệu mẫu (Seed Data) kiểm thử tính năng Tìm kiếm & Phân trang
-- ============================================================

USE `shopdemo`;

-- Xóa dữ liệu cũ trước khi nạp
TRUNCATE TABLE `products`;

INSERT INTO `products` (`code`, `name`, `price`, `quantity`, `description`, `status`) VALUES
('LAP-DELL-01', 'Laptop Dell XPS 13 9315 Core i5', 24990000.00, 15, 'Laptop cao cấp mỏng nhẹ, màn hình 13.4 inch FHD+, RAM 16GB, SSD 512GB.', 'active'),
('LAP-APL-02', 'MacBook Air M2 13.6 inch 256GB', 26500000.00, 20, 'Chip Apple M2 mạnh mẽ, thời lượng pin lên đến 18 tiếng, vỏ nhôm nguyên khối.', 'active'),
('LAP-ASU-03', 'Laptop Asus ROG Strix G16 Gaming', 32990000.00, 8, 'Cấu hình gaming khủng Intel Core i7-13650HX, RTX 4060, màn 165Hz.', 'active'),
('PHO-APL-01', 'Điện thoại iPhone 15 Pro Max 256GB', 29990000.00, 25, 'Khung viền Titan siêu nhẹ, camera tiềm vọng 5x, chip A17 Pro.', 'active'),
('PHO-SAM-02', 'Điện thoại Samsung Galaxy S24 Ultra', 28490000.00, 18, 'Trí tuệ nhân tạo Galaxy AI, bút S-Pen tích hợp, camera 200MP.', 'active'),
('PHO-XIA-03', 'Điện thoại Xiaomi 14 Ultra 5G', 22990000.00, 10, 'Hệ thống ống kính Leica chuyên nghiệp, chip Snapdragon 8 Gen 3.', 'active'),
('TAB-APL-01', 'iPad Pro 11 inch M4 Wi-Fi 128GB', 26990000.00, 12, 'Màn hình OLED Ultra Retina XDR thế hệ mới, thiết kế siêu mỏng.', 'active'),
('MOU-LOG-01', 'Chuột không dây Logitech MX Master 3S', 2150000.00, 45, 'Cảm biến 8000 DPI hoạt động trên mọi bề mặt, con lăn điện từ MagSpeed.', 'active'),
('KEY-LOG-02', 'Bàn phím cơ không dây Logitech MX Mechanical', 3450000.00, 30, 'Phím bấm low-profile tactile êm ái, kết nối đa thiết bị linh hoạt.', 'active'),
('MON-DEL-01', 'Màn hình Dell UltraSharp U2724D 27 inch 2K', 9800000.00, 14, 'Tấm nền IPS Black độ tương phản 2000:1, chuẩn màu đồ họa 98% DCI-P3.', 'active'),
('MON-LG-02', 'Màn hình Gaming LG UltraGear 27GR75Q', 6790000.00, 22, 'Độ phân giải 2K 165Hz, thời gian đáp ứng 1ms GtG, hỗ trợ G-Sync.', 'active'),
('EAR-APL-01', 'Tai nghe AirPods Pro 2 MagSafe USB-C', 5490000.00, 35, 'Chống ồn chủ động gấp 2 lần, chip H2, cổng sạc Type-C tiện lợi.', 'active'),
('EAR-SON-02', 'Tai nghe chụp tai Sony WH-1000XM5', 7990000.00, 16, 'Công nghệ chống ồn hàng đầu thế giới, âm thanh Hi-Res Audio không dây.', 'active'),
('ACC-ANK-01', 'Sạc Anker Prime 67W 3 Cổng GaN', 1190000.00, 50, 'Công nghệ sạc nhanh GaN III tiên tiến, nhỏ gọn, an toàn tản nhiệt.', 'active'),
('ACC-OLD-01', 'Chuột quang có dây cổng PS/2 Cổ điển', 50000.00, 0, 'Sản phẩm mẫu cũ đã ngừng kinh doanh và nhập hàng.', 'inactive');
