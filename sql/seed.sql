-- ============================================================
-- DỮ LIỆU MẪU LỚP HỌC PHẦN (StudentHub - lop_hoc_phan)
-- Sinh viên: Tạ Như Quỳnh - Bài Tập Về Nhà 5
-- Dữ liệu gồm 15 lớp học phần chuyên ngành Công nghệ thông tin
-- ============================================================

USE `studenthub_personal`;

-- Xóa dữ liệu cũ nếu có
TRUNCATE TABLE `lop_hoc_phan`;

INSERT INTO `lop_hoc_phan` (`ma_lop`, `ten_mon_hoc`, `so_tin_chi`, `giang_vien`, `phong_hoc`, `lich_hoc`, `si_so_toi_da`, `trang_thai`, `hoc_ky`) VALUES
('ITEC2104_01', 'Lập trình Web nâng cao với PHP & MySQL', 3, 'TS. Trần Anh Tuấn', 'Lab 03 - Tòa A', 'Thứ 2 (Tiết 1-3)', 40, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('ITEC2104_02', 'Lập trình Web nâng cao với PHP & MySQL', 3, 'ThS. Nguyễn Thị Minh', 'Lab 04 - Tòa A', 'Thứ 4 (Tiết 7-9)', 40, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('COMP1020_01', 'Cấu trúc dữ liệu và Giải thuật', 4, 'PGS.TS. Lê Hoàng Sơn', 'P.402 - Tòa C', 'Thứ 3 (Tiết 1-4)', 45, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('COMP1020_02', 'Cấu trúc dữ liệu và Giải thuật', 4, 'TS. Đặng Quốc Huy', 'P.405 - Tòa C', 'Thứ 5 (Tiết 7-10)', 45, 'khoa', 'Học kỳ 1 (2026 - 2027)'),
('DBSE3101_01', 'Hệ quản trị Cơ sở dữ liệu và Tối ưu truy vấn', 3, 'ThS. Vũ Hải Đăng', 'Lab 02 - Tòa B', 'Thứ 6 (Tiết 1-3)', 35, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('SE4002_01', 'Kiến trúc phần mềm và Thiết kế mẫu (Design Patterns)', 3, 'TS. Phạm Minh Khôi', 'P.301 - Tòa B', 'Thứ 2 (Tiết 7-9)', 40, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('AI3010_01', 'Nhập môn Trí tuệ nhân tạo và Học máy', 4, 'TS. Bùi Mai Trang', 'P.501 - Tòa C', 'Thứ 4 (Tiết 1-4)', 50, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('NETW2005_01', 'Mạng máy tính và An toàn hệ thống', 3, 'ThS. Hoàng Trọng Nghĩa', 'Lab 01 - Tòa A', 'Thứ 5 (Tiết 1-3)', 40, 'khoa', 'Học kỳ 1 (2026 - 2027)'),
('DEVOPS302_01', 'Thực hành DevOps và Điện toán đám mây', 3, 'ThS. Nguyễn Văn Thắng', 'Lab 05 - Tòa B', 'Thứ 7 (Tiết 1-3)', 30, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('SE3001_01', 'Phân tích thiết kế hệ thống thông tin hướng đối tượng', 3, 'TS. Nguyễn Hữu Dũng', 'P.304 - Tòa B', 'Thứ 3 (Tiết 7-9)', 45, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('MOBI4010_01', 'Phát triển ứng dụng di động đa nền tảng Flutter', 3, 'ThS. Đỗ Quang Huy', 'Lab 04 - Tòa A', 'Thứ 6 (Tiết 7-9)', 35, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('SEC3022_01', 'An toàn và Bảo mật thông tin trong doanh nghiệp', 3, 'TS. Cao Đức Việt', 'P.205 - Tòa C', 'Thứ 2 (Tiết 4-6)', 40, 'khoa', 'Học kỳ 1 (2026 - 2027)'),
('IOT2015_01', 'Lập trình thiết bị nhúng và Internet of Things (IoT)', 3, 'ThS. Trương Ngọc Hưng', 'Lab IoT - Tòa B', 'Thứ 5 (Tiết 4-6)', 30, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('PRJ4900_01', 'Đồ án Chuyên ngành Công nghệ phần mềm', 4, 'Hội đồng Bộ môn CNPM', 'P.Hội thảo - Tòa A', 'Thứ 7 (Tiết 7-10)', 25, 'mo', 'Học kỳ 1 (2026 - 2027)'),
('COMP1001_01', 'Nhập môn Lập trình và Thuật toán cơ bản', 3, 'ThS. Lê Thanh Thảo', 'P.102 - Tòa C', 'Thứ 3 (Tiết 1-3)', 60, 'khoa', 'Học kỳ 1 (2026 - 2027)');
