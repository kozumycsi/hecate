<?php
require_once __DIR__ . '/../service/conexao.php';

class BannerModel {
    private $conn;

    public function __construct() {
        $db = new UsePDO();
        $this->conn = $db->getInstance();
    }

    private function tableExists(string $table): bool {
        try {
            $stmt = $this->conn->query('SELECT DATABASE()');
            $dbName = $stmt->fetchColumn();
            if (!$dbName) return false;
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
            $stmt->execute([$dbName, $table]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Cria uma categoria fantasma para um banner
     */
    public function createBannerCategory(int $bannerId, string $bannerTitulo): ?int {
        if (!$this->tableExists('categoria')) {
            return null;
        }
        
        require_once __DIR__ . '/CategoryModel.php';
        $categoryModel = new CategoryModel();
        
        // Nome da categoria fantasma baseado no título do banner
        $categoryName = 'Banner: ' . $bannerTitulo;
        
        // Cria categoria tipo banner (fantasma)
        $categoryId = $categoryModel->addCategory($categoryName, 'Categoria Tipo Banner', null, 1);
        
        if ($categoryId) {
            // Vincula a categoria ao banner
            $this->setBannerCategories($bannerId, [(int)$categoryId]);
            return (int)$categoryId;
        }
        
        return null;
    }

    /**
     * Obtém a categoria fantasma principal de um banner (primeira categoria tipo banner vinculada)
     */
    public function getBannerMainCategory(int $bannerId): ?array {
        $categories = $this->getBannerCategories($bannerId);
        foreach ($categories as $cat) {
            if ($cat['tipo'] === 'Categoria Tipo Banner') {
                return $cat;
            }
        }
        return null;
    }

    /**
     * Adiciona um novo banner
     */
    public function addBanner(string $titulo, string $imagem, string $tipoBanner = 'divulgacao', int $ativo = 1): ?int {
        if (!$this->tableExists('banner')) {
            return null;
        }
        $sql = "INSERT INTO banner (titulo, imagem, tipo_banner, ativo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([$titulo, $imagem, $tipoBanner, $ativo])) {
            return (int)$this->conn->lastInsertId();
        }
        return null;
    }

    /**
     * Atualiza um banner existente
     */
    public function updateBanner(int $id, string $titulo, string $imagem = null, string $tipoBanner = null, int $ativo = null): bool {
        if (!$this->tableExists('banner')) {
            return false;
        }
        $updates = ['titulo = ?'];
        $params = [$titulo];
        
        if ($imagem !== null) {
            $updates[] = 'imagem = ?';
            $params[] = $imagem;
        }
        if ($tipoBanner !== null) {
            $updates[] = 'tipo_banner = ?';
            $params[] = $tipoBanner;
        }
        if ($ativo !== null) {
            $updates[] = 'ativo = ?';
            $params[] = $ativo;
        }
        
        $params[] = $id;
        $sql = "UPDATE banner SET " . implode(', ', $updates) . " WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obtém um banner por ID
     */
    public function getBannerById(int $id): ?array {
        if (!$this->tableExists('banner')) {
            return null;
        }
        $sql = "SELECT * FROM banner WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Obtém todos os banners
     */
    public function getAllBanners(): array {
        if (!$this->tableExists('banner')) {
            return [];
        }
        $sql = "SELECT * FROM banner ORDER BY id_banner DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém banners ativos
     */
    public function getActiveBanners(string $tipoBanner = null): array {
        if (!$this->tableExists('banner')) {
            return [];
        }
        $sql = "SELECT * FROM banner WHERE ativo = 1";
        $params = [];
        if ($tipoBanner !== null) {
            $sql .= " AND tipo_banner = ?";
            $params[] = $tipoBanner;
        }
        $sql .= " ORDER BY id_banner DESC";
        
        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->conn->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Exclui um banner
     */
    public function deleteBanner(int $id): bool {
        if (!$this->tableExists('banner')) {
            return false;
        }
        $sql = "DELETE FROM banner WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Define as categorias de um banner (substitui as existentes)
     */
    public function setBannerCategories(int $bannerId, array $categoryIds): bool {
        if (!$this->tableExists('banner_categoria')) {
            return true; // Tabela ainda não existe, não quebra o fluxo
        }
        
        // Remove categorias existentes
        $sql = "DELETE FROM banner_categoria WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bannerId]);
        
        if (empty($categoryIds)) {
            return true;
        }
        
        // Insere novas categorias
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        $placeholders = [];
        $values = [];
        foreach ($categoryIds as $catId) {
            $placeholders[] = '(?, ?)';
            $values[] = $bannerId;
            $values[] = $catId;
        }
        
        $sql = "INSERT INTO banner_categoria (id_banner, id_categoria) VALUES " . implode(',', $placeholders);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Obtém as categorias de um banner
     */
    public function getBannerCategories(int $bannerId): array {
        if (!$this->tableExists('banner_categoria')) {
            return [];
        }
        $sql = "SELECT c.* FROM banner_categoria bc
                INNER JOIN categoria c ON c.id_categoria = bc.id_categoria
                WHERE bc.id_banner = ?
                ORDER BY c.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bannerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Define os produtos de um banner (substitui os existentes)
     */
    public function setBannerProducts(int $bannerId, array $products): bool {
        if (!$this->tableExists('banner_produto')) {
            return true; // Tabela ainda não existe, não quebra o fluxo
        }
        
        // Remove produtos existentes
        $sql = "DELETE FROM banner_produto WHERE id_banner = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bannerId]);
        
        if (empty($products)) {
            return true;
        }
        
        // Insere novos produtos com ordem
        $placeholders = [];
        $values = [];
        foreach ($products as $product) {
            $productId = is_array($product) ? (int)$product['id_produto'] : (int)$product;
            $ordem = is_array($product) && isset($product['ordem']) ? (int)$product['ordem'] : 0;
            
            $placeholders[] = '(?, ?, ?)';
            $values[] = $bannerId;
            $values[] = $productId;
            $values[] = $ordem;
        }
        
        $sql = "INSERT INTO banner_produto (id_banner, id_produto, ordem) VALUES " . implode(',', $placeholders);
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Obtém os produtos de um banner ordenados
     */
    public function getBannerProducts(int $bannerId): array {
        if (!$this->tableExists('banner_produto')) {
            return [];
        }
        $sql = "SELECT p.*, bp.ordem, bp.id_banner_produto
                FROM banner_produto bp
                INNER JOIN produto p ON p.id_produto = bp.id_produto
                WHERE bp.id_banner = ?
                ORDER BY bp.ordem ASC, p.id_produto DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bannerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém banner completo com categorias e produtos
     */
    public function getBannerWithRelations(int $id): ?array {
        $banner = $this->getBannerById($id);
        if (!$banner) {
            return null;
        }
        
        $banner['categorias'] = $this->getBannerCategories($id);
        if ($banner['tipo_banner'] === 'divulgacao') {
            $banner['produtos'] = $this->getBannerProducts($id);
        } else {
            $banner['produtos'] = [];
        }
        
        return $banner;
    }

    /**
     * Obtém todos os banners com suas relações
     */
    public function getAllBannersWithRelations(): array {
        $banners = $this->getAllBanners();
        foreach ($banners as &$banner) {
            $banner['categorias'] = $this->getBannerCategories($banner['id_banner']);
            if ($banner['tipo_banner'] === 'divulgacao') {
                $banner['produtos'] = $this->getBannerProducts($banner['id_banner']);
            } else {
                $banner['produtos'] = [];
            }
        }
        return $banners;
    }
}
?>

