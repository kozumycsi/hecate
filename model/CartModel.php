<?php
require_once __DIR__ . '/../service/conexao.php';

class CartModel {
    private $conn;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
        $this->ensureCartTable();
    }

    /**
     * Garante que a tabela carrinho existe
     */
    private function ensureCartTable(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS carrinho (
              id INT AUTO_INCREMENT PRIMARY KEY,
              id_usuario INT NOT NULL,
              id_produto INT NOT NULL,
              quantidade INT NOT NULL DEFAULT 1,
              tamanho VARCHAR(50) NULL,
              cor VARCHAR(50) NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY uniq_user_product_size_color (id_usuario, id_produto, tamanho, cor),
              KEY idx_carrinho_usuario (id_usuario),
              KEY idx_carrinho_produto (id_produto),
              CONSTRAINT fk_carrinho_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(idusuario) ON DELETE CASCADE,
              CONSTRAINT fk_carrinho_produto FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $this->conn->exec($sql);
        } catch (\Throwable $e) {
            // Ignora se a tabela já existir ou houver erro de permissão
        }
    }

    /**
     * Adiciona ou atualiza um item no carrinho
     */
    public function addItem(int $userId, int $productId, int $quantity = 1, ?string $tamanho = null, ?string $cor = null): bool {
        try {
            // Verifica se o item já existe
            $sql = "SELECT id, quantidade FROM carrinho 
                    WHERE id_usuario = ? AND id_produto = ? 
                    AND (tamanho <=> ?) AND (cor <=> ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $productId, $tamanho, $cor]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Atualiza a quantidade
                $newQuantity = (int)$existing['quantidade'] + $quantity;
                $sql = "UPDATE carrinho SET quantidade = ? WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$newQuantity, $existing['id']]);
            } else {
                // Insere novo item
                $sql = "INSERT INTO carrinho (id_usuario, id_produto, quantidade, tamanho, cor) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$userId, $productId, $quantity, $tamanho, $cor]);
            }
        } catch (\Throwable $e) {
            error_log("Erro ao adicionar item ao carrinho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza a quantidade de um item no carrinho
     */
    public function updateQuantity(int $userId, int $itemId, int $quantity): bool {
        try {
            // Verifica se o item pertence ao usuário
            $sql = "SELECT id FROM carrinho WHERE id = ? AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$itemId, $userId]);
            if (!$stmt->fetch()) {
                return false; // Item não pertence ao usuário
            }

            if ($quantity <= 0) {
                return $this->removeItem($userId, $itemId);
            }

            $sql = "UPDATE carrinho SET quantidade = ? WHERE id = ? AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$quantity, $itemId, $userId]);
        } catch (\Throwable $e) {
            error_log("Erro ao atualizar quantidade do carrinho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove um item do carrinho
     */
    public function removeItem(int $userId, int $itemId): bool {
        try {
            $sql = "DELETE FROM carrinho WHERE id = ? AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$itemId, $userId]);
        } catch (\Throwable $e) {
            error_log("Erro ao remover item do carrinho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove múltiplos itens do carrinho
     */
    public function removeItems(int $userId, array $itemIds): bool {
        try {
            if (empty($itemIds)) {
                return true;
            }
            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
            $sql = "DELETE FROM carrinho WHERE id IN ($placeholders) AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $params = array_merge($itemIds, [$userId]);
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log("Erro ao remover itens do carrinho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpa todo o carrinho do usuário
     */
    public function clearCart(int $userId): bool {
        try {
            $sql = "DELETE FROM carrinho WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (\Throwable $e) {
            error_log("Erro ao limpar carrinho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todos os itens do carrinho do usuário com informações do produto
     */
    public function getCartItems(int $userId): array {
        try {
            $sql = "SELECT c.id, c.id_produto, c.quantidade, c.tamanho, c.cor,
                           p.nome, p.preco, p.imagem, p.estoque
                    FROM carrinho c
                    INNER JOIN produto p ON c.id_produto = p.id_produto
                    WHERE c.id_usuario = ?
                    ORDER BY c.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Erro ao buscar itens do carrinho: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conta o número de itens no carrinho
     */
    public function countItems(int $userId): int {
        try {
            $sql = "SELECT SUM(quantidade) as total FROM carrinho WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

