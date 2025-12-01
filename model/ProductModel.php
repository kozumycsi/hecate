<?php
require_once __DIR__ . '/../service/conexao.php';

class ProductModel {
    private $conn;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
        $this->ensureSizesColumnExists();
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
            $db = $this->getCurrentDatabase();
            if (!$db) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
            $stmt->execute([$db, $table]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool {
        try {
            $db = $this->getCurrentDatabase();
            if (!$db) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $stmt->execute([$db, $table, $column]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Garante que a coluna 'tamanhos' exista na tabela 'produto'.
     * Evita que o cadastro perca os tamanhos quando o schema ainda não foi atualizado.
     */
    private function ensureSizesColumnExists(): void {
        try {
            if ($this->tableExists('produto') && !$this->columnExists('produto', 'tamanhos')) {
                // VARCHAR amplo para lista simples "PP,P,M,..."; permite nulo para produtos sem tamanho
                $this->conn->exec("ALTER TABLE produto ADD COLUMN tamanhos VARCHAR(255) NULL");
            }
        } catch (\Throwable $e) {
            // Silencia para não quebrar a app em ambientes read-only;
            // o restante do código já trata a ausência da coluna.
        }
    }

    // Retorna o ID inserido em caso de sucesso ou false em caso de erro
    public function addProduct($nome, $descricao, $preco, $categoria, $estoque, $imagem = null, $tamanhos = null, $recemAdicionado = 0) {
        $hasSizes = $this->columnExists('produto', 'tamanhos');
        $hasRecemAdicionado = $this->columnExists('produto', 'recem_adicionado');
        
        $fields = ['nome', 'descricao', 'preco', 'categoria', 'estoque'];
        $values = [$nome, $descricao, $preco, $categoria, $estoque];
        
        if ($imagem !== null) {
            $fields[] = 'imagem';
            $values[] = $imagem;
        }
        
        if ($hasSizes) {
            $fields[] = 'tamanhos';
            $values[] = $tamanhos;
        }
        
        if ($hasRecemAdicionado) {
            $fields[] = 'recem_adicionado';
            $values[] = (int)$recemAdicionado;
        }
        
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO produto (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute($values);
        
        if ($ok) {
            $newId = (int)$this->conn->lastInsertId();
            
            // Se foi marcado como recém adicionado, verifica limite de 12
            if ($hasRecemAdicionado && (int)$recemAdicionado === 1) {
                $this->limitarNovidades(12, $newId);
            }
            
            return $newId;
        }
        return false;
    }

    // Filtros opcionais: $categoriaFilter (id), $tipoCategoria ('Categoria Principal'|'Subcategoria'|'Categoria Tipo Banner'), $status ('ativo'|'inativo')
    public function getProducts($categoriaFilter = null, $tipoCategoria = null, $status = null, $limit = null) {
        $params = [];
        $where = [];
        
        // Se a tabela produto_categoria existir, usa ela para listar produtos com suas categorias
        if ($this->tableExists('produto_categoria')) {
            $sql = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT c.nome ORDER BY pc.principal DESC, c.nome SEPARATOR ', ') as categoria_nome,
                    c_principal.tipo as categoria_tipo,
                    c_principal.id_categoria as categoria_principal_id
                    FROM produto p
                    LEFT JOIN produto_categoria pc ON p.id_produto = pc.id_produto
                    LEFT JOIN categoria c ON pc.id_categoria = c.id_categoria
                    LEFT JOIN produto_categoria pc_principal ON p.id_produto = pc_principal.id_produto AND pc_principal.principal = 1
                    LEFT JOIN categoria c_principal ON pc_principal.id_categoria = c_principal.id_categoria";
            
            if ($categoriaFilter !== null && $categoriaFilter !== '') {
                $where[] = 'pc.id_categoria = ?';
                $params[] = $categoriaFilter;
            }
            if ($tipoCategoria !== null && $tipoCategoria !== '') {
                $where[] = 'c.tipo = ?';
                $params[] = $tipoCategoria;
            }
        } else {
            // Fallback para sistema legado
            $sql = "SELECT p.*, c.nome as categoria_nome, c.tipo as categoria_tipo
                    FROM produto p
                    LEFT JOIN categoria c ON p.categoria = c.id_categoria";
            
            if ($categoriaFilter !== null && $categoriaFilter !== '') {
                $where[] = 'p.categoria = ?';
                $params[] = $categoriaFilter;
            }
            if ($tipoCategoria !== null && $tipoCategoria !== '') {
                $where[] = 'c.tipo = ?';
                $params[] = $tipoCategoria;
            }
        }
        
        if ($status === 'ativo') {
            $where[] = 'p.estoque > 0';
        } elseif ($status === 'inativo') {
            $where[] = 'p.estoque <= 0';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        if ($this->tableExists('produto_categoria')) {
            $sql .= ' GROUP BY p.id_produto';
        }

        $sql .= ' ORDER BY p.id_produto DESC';
        
        // Adiciona LIMIT se especificado (útil para evitar queries muito grandes)
        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . intval($limit);
        }

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->conn->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM produto WHERE id_produto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProduct($id, $nome, $descricao, $preco, $categoria, $estoque, $imagem = null, $tamanhos = null, $recemAdicionado = null) {
        $hasSizes = $this->columnExists('produto', 'tamanhos');
        $hasRecemAdicionado = $this->columnExists('produto', 'recem_adicionado');
        $hasEstoqueAtualizado = $this->columnExists('produto', 'estoque_atualizado_em');
        
        // Busca produto atual para verificar mudança de estoque
        $produtoAtual = $this->getProductById($id);
        $estoqueAnterior = $produtoAtual ? (int)($produtoAtual['estoque'] ?? 0) : 0;
        $estoqueNovo = (int)$estoque;
        
        $updates = ['nome = ?', 'descricao = ?', 'preco = ?', 'categoria = ?', 'estoque = ?'];
        $values = [$nome, $descricao, $preco, $categoria, $estoque];
        
        if ($imagem !== null) {
            $updates[] = 'imagem = ?';
            $values[] = $imagem;
        }
        
        if ($hasSizes) {
            $updates[] = 'tamanhos = ?';
            $values[] = $tamanhos;
        }
        
        // Sempre atualiza recem_adicionado se a coluna existir (permite definir como 0 ou 1)
        if ($hasRecemAdicionado) {
            $updates[] = 'recem_adicionado = ?';
            // Garante que seja 0 ou 1, nunca null
            $valorRecem = ($recemAdicionado !== null && $recemAdicionado !== '') ? (int)$recemAdicionado : 0;
            $values[] = $valorRecem;
            // Debug log
            error_log("DEBUG ProductModel::updateProduct: Atualizando recem_adicionado para produto ID $id com valor: $valorRecem");
        }
        
        // Se foi marcado como recém adicionado, verifica limite de 12 após atualizar
        if ($hasRecemAdicionado && isset($valorRecem) && $valorRecem === 1) {
            // Executa a atualização primeiro
            $values[] = $id;
            $sql = "UPDATE produto SET " . implode(', ', $updates) . " WHERE id_produto = ?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($values);
            
            if ($result) {
                // Depois verifica e limita para 12 produtos
                $this->limitarNovidades(12, $id);
            }
            
            return $result;
        }
        
        // Se estoque mudou de 0 para > 0, atualiza estoque_atualizado_em
        if ($hasEstoqueAtualizado && $estoqueAnterior === 0 && $estoqueNovo > 0) {
            $updates[] = 'estoque_atualizado_em = NOW()';
        }
        
        // Se não foi marcado como recém adicionado, executa atualização normal
        if (!isset($valorRecem) || $valorRecem !== 1) {
            $values[] = $id;
            $sql = "UPDATE produto SET " . implode(', ', $updates) . " WHERE id_produto = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($values);
        }
        
        // Se chegou aqui, o código já foi executado acima (quando recem_adicionado = 1)
        return true;
    }
    
    /**
     * Limita o número de produtos marcados como recém adicionados
     * Mantém apenas os N mais recentes (ordenados por id_produto DESC)
     * 
     * @param int $limite Número máximo de produtos (padrão: 12)
     * @param int $novoProdutoId ID do produto que acabou de ser marcado (sempre mantido)
     */
    private function limitarNovidades(int $limite = 12, int $novoProdutoId = 0): void {
        if (!$this->columnExists('produto', 'recem_adicionado')) {
            return;
        }
        
        try {
            // Conta quantos produtos estão marcados como recém adicionados
            $stmt = $this->conn->query("SELECT COUNT(*) FROM produto WHERE recem_adicionado = 1");
            $total = (int)$stmt->fetchColumn();
            
            // Se já tem mais que o limite, desmarca os mais antigos
            if ($total > $limite) {
                // Busca os IDs dos produtos marcados, ordenados do mais novo para o mais antigo
                // Garante que o novo produto sempre esteja na lista
                $sql = "SELECT id_produto 
                        FROM produto 
                        WHERE recem_adicionado = 1";
                if ($novoProdutoId > 0) {
                    $sql .= " AND id_produto != ?";
                }
                $sql .= " ORDER BY id_produto DESC LIMIT ?";
                
                $stmt = $this->conn->prepare($sql);
                $limiteBusca = $limite - 1; // Menos 1 porque vamos adicionar o novo produto
                if ($novoProdutoId > 0) {
                    $stmt->bindValue(1, $novoProdutoId, PDO::PARAM_INT);
                    $stmt->bindValue(2, $limiteBusca, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(1, $limiteBusca, PDO::PARAM_INT);
                }
                $stmt->execute();
                $idsParaManter = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $idsParaManter = array_map('intval', $idsParaManter);
                
                // Adiciona o novo produto à lista (sempre mantém ele)
                if ($novoProdutoId > 0) {
                    $idsParaManter[] = $novoProdutoId;
                }
                
                // Desmarca todos os produtos que não estão na lista de manter
                if (!empty($idsParaManter)) {
                    $placeholders = implode(',', array_fill(0, count($idsParaManter), '?'));
                    $sql = "UPDATE produto 
                            SET recem_adicionado = 0 
                            WHERE recem_adicionado = 1 
                              AND id_produto NOT IN ($placeholders)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute($idsParaManter);
                    
                    $desmarcados = $stmt->rowCount();
                    error_log("DEBUG limitarNovidades: Desmarcados $desmarcados produto(s) antigo(s). Mantidos: " . count($idsParaManter) . " produtos");
                }
            }
        } catch (\Throwable $e) {
            error_log("Erro ao limitar novidades: " . $e->getMessage());
        }
    }

    public function deleteProduct($id) {
        // Remove também vínculos de categorias de banner, se existirem
        if ($this->tableExists('produto_banner_categoria')) {
            try { $this->conn->prepare("DELETE FROM produto_banner_categoria WHERE id_produto = ?")->execute([$id]); } catch (\Throwable $e) {}
        }
        $sql = "DELETE FROM produto WHERE id_produto = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getRecentProducts($limit = 5) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produto p 
                LEFT JOIN categoria c ON p.categoria = c.id_categoria 
                ORDER BY p.id_produto DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countProducts() {
        $sql = "SELECT COUNT(*) as total FROM produto";
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getProductsByCategory($categoryId) {
        // Se a tabela produto_categoria existir, usa ela para buscar produtos
        if ($this->tableExists('produto_categoria')) {
            $sql = "SELECT DISTINCT p.*
                    FROM produto p
                    INNER JOIN produto_categoria pc ON p.id_produto = pc.id_produto
                    WHERE pc.id_categoria = ?";
            
            // Remove produtos que são banners
            if ($this->tableExists('produto_banner_categoria')) {
                $sql .= " AND NOT EXISTS (
                          SELECT 1
                          FROM produto_banner_categoria pbc
                          INNER JOIN categoria c ON c.id_categoria = pbc.id_categoria
                          WHERE pbc.id_produto = p.id_produto
                            AND c.tipo = 'Categoria Tipo Banner'
                      )";
            }
            
            $sql .= " GROUP BY p.id_produto ORDER BY p.id_produto DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Fallback para sistema legado
        if ($this->tableExists('produto_banner_categoria')) {
            $sql = "SELECT p.*
                    FROM produto p
                    WHERE p.categoria = ?
                      AND NOT EXISTS (
                          SELECT 1
                          FROM produto_banner_categoria pbc
                          INNER JOIN categoria c ON c.id_categoria = pbc.id_categoria
                          WHERE pbc.id_produto = p.id_produto
                            AND c.tipo = 'Categoria Tipo Banner'
                      )
                    ORDER BY p.id_produto DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fallback se nenhuma tabela pivot existir
        $sql = "SELECT * FROM produto WHERE categoria = ? ORDER BY id_produto DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Múltiplas Categorias ---
    /**
     * Define as categorias de um produto (relacionamento muitos-para-muitos)
     * @param int $productId ID do produto
     * @param array $categoryIds Array de IDs das categorias
     * @param int|null $principalCategoryId ID da categoria principal (opcional)
     * @return bool
     */
    public function setProductCategories(int $productId, array $categoryIds, ?int $principalCategoryId = null): bool {
        if (!$this->tableExists('produto_categoria')) {
            // Tabela ainda não criada: mantém comportamento legado com produto.categoria
            return true;
        }
        
        // Limpa vínculos atuais
        $this->conn->prepare("DELETE FROM produto_categoria WHERE id_produto = ?")->execute([$productId]);
        
        if (empty($categoryIds)) {
            return true;
        }
        
        // Insere novos vínculos
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        $values = [];
        $placeholders = [];
        
        foreach ($categoryIds as $index => $cid) {
            $placeholders[] = '(?, ?, ?)';
            $values[] = $productId;
            $values[] = $cid;
            // Se especificou categoria principal, marca ela. Senão, marca todas como principal
            if ($principalCategoryId !== null) {
                $values[] = ($cid === $principalCategoryId) ? 1 : 0;
            } else {
                // Todas as categorias são principais
                $values[] = 1;
            }
        }
        
        $sql = 'INSERT INTO produto_categoria (id_produto, id_categoria, principal) VALUES ' . implode(',', $placeholders);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Retorna todas as categorias de um produto
     * @param int $productId ID do produto
     * @return array Array com informações das categorias
     */
    public function getProductCategories(int $productId): array {
        if (!$this->tableExists('produto_categoria')) {
            return [];
        }
        
        $sql = "SELECT c.*, pc.principal 
                FROM produto_categoria pc
                INNER JOIN categoria c ON c.id_categoria = pc.id_categoria
                WHERE pc.id_produto = ?
                ORDER BY pc.principal DESC, c.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retorna a categoria principal de um produto
     * @param int $productId ID do produto
     * @return array|null Informações da categoria principal ou null
     */
    public function getPrincipalCategory(int $productId): ?array {
        if (!$this->tableExists('produto_categoria')) {
            return null;
        }
        
        $sql = "SELECT c.* 
                FROM produto_categoria pc
                INNER JOIN categoria c ON c.id_categoria = pc.id_categoria
                WHERE pc.id_produto = ? AND pc.principal = 1
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // --- Categorias de Banner ---
    public function setProductBannerCategories(int $productId, array $categoryIds): bool {
        if (!$this->tableExists('produto_banner_categoria')) {
            // Pivot ainda não criada: ignora silenciosamente para não quebrar o fluxo do admin
            return true;
        }
        // Limpa vínculos atuais
        $this->conn->prepare("DELETE FROM produto_banner_categoria WHERE id_produto = ?")->execute([$productId]);
        if (empty($categoryIds)) return true;
        // Insere novos vínculos (apenas ids distintos)
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        $values = [];
        $placeholders = [];
        foreach ($categoryIds as $cid) {
            $placeholders[] = '(?, ?)';
            $values[] = $productId;
            $values[] = $cid;
        }
        $sql = 'INSERT INTO produto_banner_categoria (id_produto, id_categoria) VALUES ' . implode(',', $placeholders);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function getBannerCategoriesForProduct(int $productId): array {
        if (!$this->tableExists('produto_banner_categoria')) return [];
        $sql = "SELECT c.* FROM produto_banner_categoria pbc
                INNER JOIN categoria c ON c.id_categoria = pbc.id_categoria
                WHERE pbc.id_produto = ?
                ORDER BY c.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductsByBannerCategory(int $categoryId, int $limit = 12): array {
        if (!$this->tableExists('produto_banner_categoria')) return [];
        $sql = "SELECT p.* FROM produto_banner_categoria pbc
                INNER JOIN produto p ON p.id_produto = pbc.id_produto
                WHERE pbc.id_categoria = ?
                ORDER BY p.id_produto DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna todos os produtos que são banners (vinculados a categorias tipo banner)
     * com informações das categorias de banner
     */
    public function getAllBannerProducts(): array {
        if (!$this->tableExists('produto_banner_categoria')) return [];
        $sql = "SELECT p.id_produto, p.nome, p.descricao, p.preco, p.categoria, p.estoque, p.imagem, p.tamanhos,
                GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as banner_categorias,
                GROUP_CONCAT(DISTINCT c.id_categoria ORDER BY c.id_categoria SEPARATOR ',') as banner_categoria_ids
                FROM produto_banner_categoria pbc
                INNER JOIN produto p ON p.id_produto = pbc.id_produto
                INNER JOIN categoria c ON c.id_categoria = pbc.id_categoria
                WHERE c.tipo = 'Categoria Tipo Banner'
                GROUP BY p.id_produto, p.nome, p.descricao, p.preco, p.categoria, p.estoque, p.imagem, p.tamanhos
                ORDER BY p.id_produto DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se um produto é um banner (está vinculado a alguma categoria tipo banner)
     */
    public function isBannerProduct(int $productId): bool {
        if (!$this->tableExists('produto_banner_categoria')) return false;
        $sql = "SELECT COUNT(*) FROM produto_banner_categoria pbc
                INNER JOIN categoria c ON c.id_categoria = pbc.id_categoria
                WHERE pbc.id_produto = ? AND c.tipo = 'Categoria Tipo Banner'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public function searchProducts(string $term, int $limit = 48): array {
        $term = trim($term);
        if ($term === '') {
            return [];
        }
        $like = '%' . $term . '%';
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produto p
                LEFT JOIN categoria c ON p.categoria = c.id_categoria
                WHERE p.nome LIKE ? OR p.descricao LIKE ?
                ORDER BY p.id_produto DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $like, PDO::PARAM_STR);
        $stmt->bindValue(2, $like, PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca produtos recém adicionados (Novidades)
     * Limite padrão alterado para 12 conforme requisito
     */
    public function getNovidades(int $limit = 12): array {
        if (!$this->columnExists('produto', 'recem_adicionado')) {
            // Se a coluna não existe, retorna vazio silenciosamente
            return [];
        }
        
        // Query simplificada primeiro para verificar se há produtos
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produto p
                LEFT JOIN categoria c ON p.categoria = c.id_categoria
                WHERE p.recem_adicionado = 1
                  AND p.estoque > 0";
        
        // Só adiciona o filtro de banner se a tabela existir
        if ($this->tableExists('produto_banner_categoria')) {
            $sql .= " AND NOT EXISTS (
                      SELECT 1
                      FROM produto_banner_categoria pbc
                      INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                      WHERE pbc.id_produto = p.id_produto
                        AND cb.tipo = 'Categoria Tipo Banner'
                  )";
        }
        
        $sql .= " ORDER BY p.id_produto DESC LIMIT ?";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            // Em caso de erro, retorna vazio
            error_log("Erro em getNovidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca produtos que voltaram ao estoque (Voltaram)
     */
    public function getVoltaram(int $dias = 7, int $limit = 18): array {
        if (!$this->columnExists('produto', 'estoque_atualizado_em')) {
            return [];
        }
        $sql = "SELECT p.*, c.nome AS categoria_nome
                FROM produto p
                LEFT JOIN categoria c ON p.categoria = c.id_categoria
                WHERE p.estoque > 0
                  AND p.estoque_atualizado_em IS NOT NULL
                  AND p.estoque_atualizado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
                  AND NOT EXISTS (
                      SELECT 1
                      FROM produto_banner_categoria pbc
                      INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                      WHERE pbc.id_produto = p.id_produto
                        AND cb.tipo = 'Categoria Tipo Banner'
                  )
                ORDER BY p.estoque_atualizado_em DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$dias, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca produtos mais vendidos (Mais Vendidos)
     */
    public function getMaisVendidos(int $limit = 18): array {
        // Primeiro tenta usar o campo total_vendas se existir
        if ($this->columnExists('produto', 'total_vendas')) {
            $sql = "SELECT p.*, c.nome AS categoria_nome
                    FROM produto p
                    LEFT JOIN categoria c ON p.categoria = c.id_categoria
                    WHERE p.total_vendas > 0
                      AND p.estoque > 0
                      AND NOT EXISTS (
                          SELECT 1
                          FROM produto_banner_categoria pbc
                          INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                          WHERE pbc.id_produto = p.id_produto
                            AND cb.tipo = 'Categoria Tipo Banner'
                      )
                    ORDER BY p.total_vendas DESC, p.id_produto DESC
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Fallback: calcula vendas da tabela item_do_pedido
        if (!$this->tableExists('item_do_pedido')) {
            return [];
        }
        
        $sql = "SELECT p.*, c.nome AS categoria_nome, COALESCE(SUM(ip.quantidade), 0) AS total_vendas
                FROM produto p
                LEFT JOIN categoria c ON p.categoria = c.id_categoria
                LEFT JOIN item_do_pedido ip ON p.id_produto = ip.id_produto
                LEFT JOIN pedido ped ON ip.id_pedido = ped.id_pedido AND ped.status != 'Cancelado'
                WHERE p.estoque > 0
                  AND NOT EXISTS (
                      SELECT 1
                      FROM produto_banner_categoria pbc
                      INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                      WHERE pbc.id_produto = p.id_produto
                        AND cb.tipo = 'Categoria Tipo Banner'
                  )
                GROUP BY p.id_produto
                HAVING total_vendas > 0
                ORDER BY total_vendas DESC, p.id_produto DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza o total_vendas de um produto baseado nas vendas reais
     */
    public function updateTotalVendas(int $productId): bool {
        if (!$this->columnExists('produto', 'total_vendas') || !$this->tableExists('item_do_pedido')) {
            return false;
        }
        
        $sql = "UPDATE produto p
                SET p.total_vendas = (
                    SELECT COALESCE(SUM(ip.quantidade), 0)
                    FROM item_do_pedido ip
                    INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido
                    WHERE ip.id_produto = p.id_produto
                      AND ped.status != 'Cancelado'
                )
                WHERE p.id_produto = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$productId]);
    }

    /**
     * Remove automaticamente o flag recem_adicionado após X dias
     */
    public function removeRecemAdicionadoAntigos(int $dias = 30): int {
        if (!$this->columnExists('produto', 'recem_adicionado')) {
            return 0;
        }
        
        // Assumindo que produtos recém adicionados foram criados recentemente
        // Remove o flag de produtos criados há mais de X dias
        $sql = "UPDATE produto 
                SET recem_adicionado = 0 
                WHERE recem_adicionado = 1 
                  AND DATE_ADD(DATE(created_at), INTERVAL ? DAY) < NOW()";
        
        // Se não houver created_at, usa id_produto como aproximação (não ideal, mas funcional)
        if (!$this->columnExists('produto', 'created_at')) {
            // Não podemos fazer isso sem created_at, então retorna 0
            return 0;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->rowCount();
    }

    /**
     * Busca produtos com estoque menor ou igual a zero
     * Exclui produtos que são banners (vinculados a categorias tipo banner)
     */
    public function getProductsWithoutStock(): array {
        // Se a tabela produto_categoria existir, usa ela para listar produtos com suas categorias
        if ($this->tableExists('produto_categoria')) {
            $sql = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT c.nome ORDER BY pc.principal DESC, c.nome SEPARATOR ', ') as categoria_nome
                    FROM produto p
                    LEFT JOIN produto_categoria pc ON p.id_produto = pc.id_produto
                    LEFT JOIN categoria c ON pc.id_categoria = c.id_categoria
                    WHERE p.estoque <= 0";
            
            // Exclui produtos que são banners
            if ($this->tableExists('produto_banner_categoria')) {
                $sql .= " AND NOT EXISTS (
                          SELECT 1
                          FROM produto_banner_categoria pbc
                          INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                          WHERE pbc.id_produto = p.id_produto
                            AND cb.tipo = 'Categoria Tipo Banner'
                      )";
            }
            
            $sql .= " GROUP BY p.id_produto
                      ORDER BY p.id_produto DESC";
        } else {
            // Fallback para sistema legado
            $sql = "SELECT p.*, c.nome as categoria_nome
                    FROM produto p
                    LEFT JOIN categoria c ON p.categoria = c.id_categoria
                    WHERE p.estoque <= 0";
            
            // Exclui produtos que são banners
            if ($this->tableExists('produto_banner_categoria')) {
                $sql .= " AND NOT EXISTS (
                          SELECT 1
                          FROM produto_banner_categoria pbc
                          INNER JOIN categoria cb ON cb.id_categoria = pbc.id_categoria
                          WHERE pbc.id_produto = p.id_produto
                            AND cb.tipo = 'Categoria Tipo Banner'
                      )";
            }
            
            $sql .= " ORDER BY p.id_produto DESC";
        }
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
