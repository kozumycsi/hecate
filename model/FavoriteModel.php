<?php
require_once __DIR__ . '/../service/conexao.php';

class FavoriteModel
{
    private PDO $conn;

    public function __construct()
    {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
    }

    public function toggleFavorite(int $userId, int $productId): bool
    {
        if ($this->isFavorite($userId, $productId)) {
            $this->removeFavorite($userId, $productId);
            return false;
        }

        $this->addFavorite($userId, $productId);
        return true;
    }

    public function isFavorite(int $userId, int $productId): bool
    {
        $sql = "SELECT 1 FROM favoritos WHERE user_id = ? AND product_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetchColumn();
    }

    public function addFavorite(int $userId, int $productId): bool
    {
        $sql = "INSERT INTO favoritos (user_id, product_id) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }

    public function removeFavorite(int $userId, int $productId): bool
    {
        $sql = "DELETE FROM favoritos WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }

    public function getFavoritesWithProducts(int $userId): array
    {
        $sql = "SELECT f.id, f.product_id, f.created_at,
                       p.nome, p.preco, p.imagem, p.descricao
                FROM favoritos f
                INNER JOIN produto p ON p.id_produto = f.product_id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFavorites(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM favoritos WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}

