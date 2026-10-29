<?php
declare(strict_types=1);

/**
 * Class ProductRepository
 * Đóng gói toàn bộ các thao tác truy vấn CSDL liên quan đến thực thể Sản phẩm (Products).
 * Tuân thủ mô hình Repository Pattern, 100% truy vấn dùng Prepared Statements.
 */
class ProductRepository
{
    /**
     * @param PDO $pdo Đối tượng kết nối PDO được tiêm vào (Dependency Injection)
     */
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Lấy danh sách sản phẩm có hỗ trợ tìm kiếm và phân trang
     *
     * @param string $search Từ khóa tìm kiếm theo mã sản phẩm hoặc tên sản phẩm
     * @param int $limit Số lượng bản ghi trên một trang
     * @param int $offset Vị trí bắt đầu lấy dữ liệu
     * @return array Danh sách các sản phẩm (mỗi sản phẩm là một mảng liên kết)
     */
    public function getAll(string $search = '', int $limit = 10, int $offset = 0): array
    {
        $search = trim($search);

        if ($search !== '') {
            $sql = 'SELECT * FROM products 
                    WHERE code LIKE :search_code OR name LIKE :search_name 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset';
            $stmt = $this->pdo->prepare($sql);
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':search_code', $searchPattern, PDO::PARAM_STR);
            $stmt->bindValue(':search_name', $searchPattern, PDO::PARAM_STR);
        } else {
            $sql = 'SELECT * FROM products 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset';
            $stmt = $this->pdo->prepare($sql);
        }

        // Ràng buộc tham số LIMIT và OFFSET dưới dạng số nguyên (PDO::PARAM_INT)
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số lượng bản ghi thỏa mãn điều kiện tìm kiếm (phục vụ tính số trang)
     *
     * @param string $search Từ khóa tìm kiếm
     * @return int Tổng số lượng sản phẩm
     */
    public function count(string $search = ''): int
    {
        $search = trim($search);

        if ($search !== '') {
            $sql = 'SELECT COUNT(*) FROM products 
                    WHERE code LIKE :search_code OR name LIKE :search_name';
            $stmt = $this->pdo->prepare($sql);
            $searchPattern = '%' . $search . '%';
            $stmt->bindValue(':search_code', $searchPattern, PDO::PARAM_STR);
            $stmt->bindValue(':search_name', $searchPattern, PDO::PARAM_STR);
        } else {
            $sql = 'SELECT COUNT(*) FROM products';
            $stmt = $this->pdo->prepare($sql);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy thông tin chi tiết một sản phẩm theo ID
     *
     * @param int $id ID sản phẩm
     * @return array|null Trả về mảng dữ liệu sản phẩm hoặc null nếu không tìm thấy
     */
    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM products WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch();
        return $product !== false ? $product : null;
    }

    /**
     * Lấy thông tin sản phẩm theo mã sản phẩm (SKU/Code)
     * Dùng để kiểm tra tính duy nhất khi tạo mới hoặc cập nhật
     *
     * @param string $code Mã sản phẩm
     * @param int|null $excludeId ID cần loại trừ khi kiểm tra lúc cập nhật
     * @return array|null
     */
    public function getByCode(string $code, ?int $excludeId = null): ?array
    {
        if ($excludeId !== null) {
            $sql = 'SELECT * FROM products WHERE code = :code AND id != :exclude_id LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':code', $code, PDO::PARAM_STR);
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        } else {
            $sql = 'SELECT * FROM products WHERE code = :code LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        }

        $stmt->execute();
        $product = $stmt->fetch();
        return $product !== false ? $product : null;
    }

    /**
     * Tạo mới một sản phẩm vào CSDL
     *
     * @param array $data Dữ liệu sản phẩm [code, name, price, quantity, description, status]
     * @return int ID của bản ghi vừa được tạo (lastInsertId)
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO products (code, name, price, quantity, description, status) 
                VALUES (:code, :name, :price, :quantity, :description, :status)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':code', $data['code'], PDO::PARAM_STR);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':quantity', (int)$data['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':description', $data['description'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'] ?? 'active', PDO::PARAM_STR);

        $stmt->execute();
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin một sản phẩm
     *
     * @param int $id ID sản phẩm cần cập nhật
     * @param array $data Dữ liệu cần cập nhật
     * @return bool True nếu cập nhật thành công, False nếu thất bại
     */
    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE products 
                SET code = :code, 
                    name = :name, 
                    price = :price, 
                    quantity = :quantity, 
                    description = :description, 
                    status = :status 
                WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':code', $data['code'], PDO::PARAM_STR);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':quantity', (int)$data['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':description', $data['description'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'] ?? 'active', PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Xóa một sản phẩm theo ID
     *
     * @param int $id ID sản phẩm cần xóa
     * @return bool True nếu xóa thành công, False nếu thất bại
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM products WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
