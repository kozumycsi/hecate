<?php
require_once __DIR__ . '/../service/conexao.php';

class CategoryModel {
    private $conn;
    private $hasSortOrder = null;
    private $hasAtivo = null;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
    }

    private function getCurrentDatabase(): ?string {
        try {
            $stmt = $this->conn->query('SELECT DATABASE()');
            return $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function tableExists(string $table): bool {
        try {
            $dbName = $this->getCurrentDatabase();
            if (!$dbName) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
            $stmt->execute([$dbName, $table]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool {
        try {
            $dbName = $this->getCurrentDatabase();
            if (!$dbName) return false;
            $sql = 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$dbName, $table, $column]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function ensureFlags(): void {
        if ($this->hasSortOrder === null) {
            $this->hasSortOrder = $this->columnExists('categoria', 'sort_order');
        }
        if ($this->hasAtivo === null) {
            $this->hasAtivo = $this->columnExists('categoria', 'ativo');
        }
    }

    public function addCategory($name, $type = 'principal', $parentId = null, $active = 1, $sortOrder = null) {
        $this->ensureFlags();
        if ($this->hasAtivo || $this->hasSortOrder) {
            $sql = "INSERT INTO categoria (nome, tipo, parent_id" . ($this->hasAtivo ? ", ativo" : "") . ($this->hasSortOrder ? ", sort_order" : "") . ") VALUES (?,?,?" . ($this->hasAtivo ? ",?" : "") . ($this->hasSortOrder ? ",?" : "") . ")";
            $params = [$name, $type, $parentId];
            if ($this->hasAtivo) $params[] = (int)$active;
            if ($this->hasSortOrder) $params[] = $sortOrder;
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute($params)) {
                return (int)$this->conn->lastInsertId();
            }
            return false;
        } else {
            // fallback antigo
            $sql = "INSERT INTO categoria (nome, tipo, parent_id) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute([$name, $type, $parentId])) {
                return (int)$this->conn->lastInsertId();
            }
            return false;
        }
    }

    public function getCategories() {
        $this->ensureFlags();
        $order = $this->hasSortOrder ? "COALESCE(sort_order, 999999), nome ASC" : "nome ASC";
        $sql = "SELECT * FROM categoria ORDER BY $order";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoriesWithProductCounts() {
        $this->ensureFlags();
        $order = $this->hasSortOrder ? "COALESCE(c.sort_order, 999999), c.nome ASC" : "c.nome ASC";
        
        // Conta apenas produtos que realmente existem e estão vinculados diretamente à categoria
        // Para categorias tipo banner, não conta produtos (eles são gerenciados via produto_banner_categoria)
        $sql = "SELECT c.*, 
                COALESCE((
                    SELECT COUNT(DISTINCT p.id_produto)
                    FROM produto p
                    WHERE p.categoria = c.id_categoria
                      AND c.tipo != 'Categoria Tipo Banner'
                ), 0) AS quantidade
                FROM categoria c
                ORDER BY $order";
        
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Garante que a quantidade seja um número inteiro
        foreach ($result as &$row) {
            $row['quantidade'] = (int)($row['quantidade'] ?? 0);
        }
        
        return $result;
    }

    public function getCategoriesByType(string $type) {
        $this->ensureFlags();
        // Aceita sinônimos para compatibilidade com dados antigos
        $map = [
            'Categoria Principal' => ['Categoria Principal', 'principal', 'Principal'],
            'Subcategoria' => ['Subcategoria', 'subcategoria'],
            'Categoria Tipo Banner' => ['Categoria Tipo Banner', 'banner', 'Categoria de Banner']
        ];
        $accept = $map[$type] ?? [$type];
        $placeholders = implode(',', array_fill(0, count($accept), '?'));
        $whereAtivo = $this->hasAtivo ? " AND (ativo = 1)" : "";
        $order = $this->hasSortOrder ? "COALESCE(sort_order, 999999), nome ASC" : "nome ASC";
        $sql = "SELECT * FROM categoria WHERE tipo IN ($placeholders)$whereAtivo ORDER BY $order";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($accept);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubcategoriesByParent(int $parentId) {
        $this->ensureFlags();
        $whereAtivo = $this->hasAtivo ? " AND (ativo = 1)" : "";
        $order = $this->hasSortOrder ? "COALESCE(sort_order, 999999), nome ASC" : "nome ASC";
        $sql = "SELECT * FROM categoria WHERE tipo = 'Subcategoria' AND parent_id = ?$whereAtivo ORDER BY $order";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($id) {
        $sql = "SELECT * FROM categoria WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma categoria pelo nome (case-insensitive)
     * @param string $name Nome da categoria
     * @return array|null Informações da categoria ou null se não encontrada
     */
    public function getCategoryByName(string $name): ?array {
        $sql = "SELECT * FROM categoria WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateCategory($id, $name, $type = 'principal', $parentId = null, $active = 1, $sortOrder = null) {
        $this->ensureFlags();
        if ($this->hasAtivo || $this->hasSortOrder) {
            $sql = "UPDATE categoria SET nome = ?, tipo = ?, parent_id = ?" . ($this->hasAtivo ? ", ativo = ?" : "") . ($this->hasSortOrder ? ", sort_order = ?" : "") . " WHERE id_categoria = ?";
            $params = [$name, $type, $parentId];
            if ($this->hasAtivo) $params[] = (int)$active;
            if ($this->hasSortOrder) $params[] = $sortOrder;
            $params[] = $id;
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } else {
            $sql = "UPDATE categoria SET nome = ?, tipo = ?, parent_id = ? WHERE id_categoria = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$name, $type, $parentId, $id]);
        }
    }

    /**
     * Atualiza apenas a posição (sort_order) da categoria.
     */
    public function updateCategoryOrder(int $id, ?int $sortOrder): bool {
        $this->ensureFlags();
        if (!$this->hasSortOrder) {
            // Se a coluna não existir, não quebra a aplicação.
            return false;
        }
        $sql = "UPDATE categoria SET sort_order = ? WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        // Permite null para enviar pro final da lista
        $stmt->bindValue(1, $sortOrder, $sortOrder === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindValue(2, $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Verifica se uma categoria pode ser deletada (não tem produtos ou outras dependências)
     */
    public function canDeleteCategory($id): array {
        $issues = [];
        
        // Verifica produtos usando esta categoria
        $sql = "SELECT COUNT(*) as total FROM produto WHERE categoria = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $productCount = (int)($result['total'] ?? 0);
        
        if ($productCount > 0) {
            $issues[] = "$productCount produto(s) vinculado(s) a esta categoria";
        }
        
        // Verifica banners vinculados
        if ($this->tableExists('banner_categoria')) {
            $sql = "SELECT COUNT(*) as total FROM banner_categoria WHERE id_categoria = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $bannerCount = (int)($result['total'] ?? 0);
            
            if ($bannerCount > 0) {
                $issues[] = "$bannerCount banner(s) vinculado(s) a esta categoria";
            }
        }
        
        // Verifica vínculos em produto_banner_categoria
        if ($this->tableExists('produto_banner_categoria')) {
            $sql = "SELECT COUNT(*) as total FROM produto_banner_categoria WHERE id_categoria = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $productBannerCount = (int)($result['total'] ?? 0);
            
            if ($productBannerCount > 0) {
                $issues[] = "$productBannerCount vínculo(s) de produto-banner usando esta categoria";
            }
        }
        
        // Verifica subcategorias filhas
        $sql = "SELECT COUNT(*) as total FROM categoria WHERE parent_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $subcategoryCount = (int)($result['total'] ?? 0);
        
        if ($subcategoryCount > 0) {
            $issues[] = "$subcategoryCount subcategoria(s) filha(s)";
        }
        
        return [
            'canDelete' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Move produtos de uma categoria para outra (ou NULL)
     */
    public function moveProductsToCategory($fromCategoryId, $toCategoryId = null): bool {
        try {
            if ($toCategoryId === null) {
                // Tenta definir como NULL (pode falhar se a coluna não permitir NULL)
                $sql = "UPDATE produto SET categoria = NULL WHERE categoria = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$fromCategoryId]);
            } else {
                // Move para outra categoria
                $sql = "UPDATE produto SET categoria = ? WHERE categoria = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$toCategoryId, $fromCategoryId]);
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function deleteCategory($id, $moveProductsToCategoryId = null, $force = false) {
        // Se não for forçado, verifica dependências
        if (!$force) {
            $check = $this->canDeleteCategory($id);
            if (!$check['canDelete']) {
                // Se houver produtos, tenta movê-los se uma categoria destino foi fornecida
                if ($moveProductsToCategoryId !== null) {
                    $this->moveProductsToCategory($id, $moveProductsToCategoryId);
                    // Verifica novamente após mover produtos
                    $check = $this->canDeleteCategory($id);
                }
                
                if (!$check['canDelete']) {
                    // Ainda há dependências que impedem a exclusão
                    throw new \Exception('Não é possível excluir esta categoria: ' . implode(', ', $check['issues']));
                }
            }
        }
        
        // Remove vínculos de banners antes de deletar
        if ($this->tableExists('banner_categoria')) {
            try {
                $sql = "DELETE FROM banner_categoria WHERE id_categoria = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
            } catch (\Throwable $e) {
                // Ignora se a tabela não existir ou houver erro
            }
        }
        
        // Remove vínculos de produto_banner_categoria antes de deletar
        if ($this->tableExists('produto_banner_categoria')) {
            try {
                $sql = "DELETE FROM produto_banner_categoria WHERE id_categoria = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$id]);
            } catch (\Throwable $e) {
                // Ignora se a tabela não existir ou houver erro
            }
        }
        
        // Remove subcategorias filhas (cascata)
        try {
            $sql = "DELETE FROM categoria WHERE parent_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
        } catch (\Throwable $e) {
            // Pode falhar se houver dependências nas subcategorias
        }
        
        // Agora tenta deletar a categoria
        $sql = "DELETE FROM categoria WHERE id_categoria = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
