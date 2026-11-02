<?php
declare(strict_types=1);

/**
 * ============================================================================
 * CLASS LopHocPhanRepository
 * Tầng Repository (Data Access Layer) đóng gói 100% các truy vấn CSDL
 * liên quan đến thực thể Lớp Học Phần (lop_hoc_phan).
 * 
 * Đáp ứng:
 * - Chuẩn Clean Architecture & Repository Pattern (Tách biệt tầng dữ liệu).
 * - 100% Prepared Statements chống triệt để tấn công SQL Injection.
 * - Hỗ trợ tìm kiếm đa tiêu chí, lọc trạng thái và phân trang an toàn.
 * 
 * Dự án: StudentHub - Quản Lý Lớp Học Phần (BÀI TẬP VỀ NHÀ 5)
 * Sinh viên thực hiện: Tạ Như Quỳnh
 * ============================================================================
 */
class LopHocPhanRepository
{
    /**
     * Khởi tạo Repository với Dependency Injection đối tượng PDO
     *
     * @param PDO $pdo Đối tượng kết nối PDO
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Lấy danh sách lớp học phần có hỗ trợ tìm kiếm đa tiêu chí, lọc trạng thái và phân trang
     *
     * @param string $search Từ khóa tìm kiếm (theo mã lớp, tên môn học hoặc giảng viên)
     * @param string $trangThai Lọc theo trạng thái ('mo', 'khoa' hoặc rỗng '')
     * @param int $limit Số lượng bản ghi trên một trang
     * @param int $offset Vị trí bắt đầu lấy bản ghi
     * @return array Danh sách lớp học phần
     */
    public function getAll(string $search = '', string $trangThai = '', int $limit = 5, int $offset = 0): array
    {
        $search = trim($search);
        $trangThai = trim($trangThai);

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(ma_lop LIKE :search_ma OR ten_mon_hoc LIKE :search_ten OR giang_vien LIKE :search_gv)';
            $searchPattern = '%' . $search . '%';
            $params[':search_ma'] = [$searchPattern, PDO::PARAM_STR];
            $params[':search_ten'] = [$searchPattern, PDO::PARAM_STR];
            $params[':search_gv']  = [$searchPattern, PDO::PARAM_STR];
        }

        if (in_array($trangThai, ['mo', 'khoa'], true)) {
            $conditions[] = 'trang_thai = :trang_thai';
            $params[':trang_thai'] = [$trangThai, PDO::PARAM_STR];
        }

        $sql = 'SELECT * FROM lop_hoc_phan';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);

        // Ràng buộc các tham số tìm kiếm và lọc
        foreach ($params as $placeholder => [$value, $type]) {
            $stmt->bindValue($placeholder, $value, $type);
        }

        // Ràng buộc LIMIT và OFFSET bắt buộc là kiểu số nguyên
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số lượng bản ghi thỏa mãn điều kiện tìm kiếm và lọc
     *
     * @param string $search Từ khóa tìm kiếm
     * @param string $trangThai Trạng thái lọc
     * @return int Tổng số bản ghi
     */
    public function count(string $search = '', string $trangThai = ''): int
    {
        $search = trim($search);
        $trangThai = trim($trangThai);

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(ma_lop LIKE :search_ma OR ten_mon_hoc LIKE :search_ten OR giang_vien LIKE :search_gv)';
            $searchPattern = '%' . $search . '%';
            $params[':search_ma'] = [$searchPattern, PDO::PARAM_STR];
            $params[':search_ten'] = [$searchPattern, PDO::PARAM_STR];
            $params[':search_gv']  = [$searchPattern, PDO::PARAM_STR];
        }

        if (in_array($trangThai, ['mo', 'khoa'], true)) {
            $conditions[] = 'trang_thai = :trang_thai';
            $params[':trang_thai'] = [$trangThai, PDO::PARAM_STR];
        }

        $sql = 'SELECT COUNT(*) FROM lop_hoc_phan';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $placeholder => [$value, $type]) {
            $stmt->bindValue($placeholder, $value, $type);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy thông tin chi tiết một lớp học phần theo ID
     *
     * @param int $id ID lớp học phần
     * @return array|null Trả về mảng dữ liệu hoặc null nếu không tồn tại
     */
    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM lop_hoc_phan WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $record = $stmt->fetch();
        return $record !== false ? $record : null;
    }

    /**
     * Tìm lớp học phần theo mã lớp (SKU/Code), hỗ trợ loại trừ ID khi cập nhật
     *
     * @param string $maLop Mã lớp học phần
     * @param int|null $excludeId ID cần loại trừ khi cập nhật
     * @return array|null
     */
    public function getByMaLop(string $maLop, ?int $excludeId = null): ?array
    {
        if ($excludeId !== null) {
            $sql = 'SELECT * FROM lop_hoc_phan WHERE ma_lop = :ma_lop AND id != :exclude_id LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':ma_lop', $maLop, PDO::PARAM_STR);
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        } else {
            $sql = 'SELECT * FROM lop_hoc_phan WHERE ma_lop = :ma_lop LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':ma_lop', $maLop, PDO::PARAM_STR);
        }

        $stmt->execute();
        $record = $stmt->fetch();
        return $record !== false ? $record : null;
    }

    /**
     * Thêm mới một lớp học phần
     *
     * @param array $data Dữ liệu lớp học phần
     * @return int ID của bản ghi vừa thêm mới
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO lop_hoc_phan (
                    ma_lop, 
                    ten_mon_hoc, 
                    so_tin_chi, 
                    giang_vien, 
                    phong_hoc, 
                    lich_hoc, 
                    si_so_toi_da, 
                    trang_thai, 
                    hoc_ky
                ) VALUES (
                    :ma_lop, 
                    :ten_mon_hoc, 
                    :so_tin_chi, 
                    :giang_vien, 
                    :phong_hoc, 
                    :lich_hoc, 
                    :si_so_toi_da, 
                    :trang_thai, 
                    :hoc_ky
                )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ma_lop', $data['ma_lop'], PDO::PARAM_STR);
        $stmt->bindValue(':ten_mon_hoc', $data['ten_mon_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':so_tin_chi', (int)$data['so_tin_chi'], PDO::PARAM_INT);
        $stmt->bindValue(':giang_vien', $data['giang_vien'], PDO::PARAM_STR);
        $stmt->bindValue(':phong_hoc', $data['phong_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':lich_hoc', $data['lich_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':si_so_toi_da', (int)$data['si_so_toi_da'], PDO::PARAM_INT);
        $stmt->bindValue(':trang_thai', $data['trang_thai'], PDO::PARAM_STR);
        $stmt->bindValue(':hoc_ky', $data['hoc_ky'], PDO::PARAM_STR);

        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin một lớp học phần
     *
     * @param int $id ID lớp học phần cần cập nhật
     * @param array $data Dữ liệu mới
     * @return bool True nếu cập nhật thành công, False nếu thất bại
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE lop_hoc_phan 
                SET ma_lop = :ma_lop, 
                    ten_mon_hoc = :ten_mon_hoc, 
                    so_tin_chi = :so_tin_chi, 
                    giang_vien = :giang_vien, 
                    phong_hoc = :phong_hoc, 
                    lich_hoc = :lich_hoc, 
                    si_so_toi_da = :si_so_toi_da, 
                    trang_thai = :trang_thai, 
                    hoc_ky = :hoc_ky 
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ma_lop', $data['ma_lop'], PDO::PARAM_STR);
        $stmt->bindValue(':ten_mon_hoc', $data['ten_mon_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':so_tin_chi', (int)$data['so_tin_chi'], PDO::PARAM_INT);
        $stmt->bindValue(':giang_vien', $data['giang_vien'], PDO::PARAM_STR);
        $stmt->bindValue(':phong_hoc', $data['phong_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':lich_hoc', $data['lich_hoc'], PDO::PARAM_STR);
        $stmt->bindValue(':si_so_toi_da', (int)$data['si_so_toi_da'], PDO::PARAM_INT);
        $stmt->bindValue(':trang_thai', $data['trang_thai'], PDO::PARAM_STR);
        $stmt->bindValue(':hoc_ky', $data['hoc_ky'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Xóa một lớp học phần theo ID
     *
     * @param int $id ID lớp học phần cần xóa
     * @return bool True nếu xóa thành công, False nếu thất bại
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM lop_hoc_phan WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
