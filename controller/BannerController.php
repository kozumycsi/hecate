<?php
require_once __DIR__ . '/../model/BannerModel.php';
require_once __DIR__ . '/../model/CategoryModel.php';
require_once __DIR__ . '/../model/ProductModel.php';

class BannerController {
    private $bannerModel;
    private $categoryModel;
    private $productModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->bannerModel = new BannerModel();
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
    }

    public function handlePost() {
        $action = $_POST['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($action === 'add') {
            $this->handleAdd($isAjax);
        } elseif ($action === 'edit') {
            $this->handleEdit($isAjax);
        } elseif ($action === 'delete') {
            $this->handleDelete($isAjax);
        } elseif ($action === 'update_product_order') {
            $this->handleUpdateProductOrder($isAjax);
        }
    }

    private function handleAdd($isAjax) {
        // Validações básicas
        $titulo = trim($_POST['titulo'] ?? '');
        if (empty($titulo)) {
            $this->sendResponse($isAjax, false, 'Por favor, informe o título do banner.');
            return;
        }

        $tipoBanner = $_POST['tipo_banner'] ?? 'divulgacao';
        if (!in_array($tipoBanner, ['divulgacao', 'decoracao'])) {
            $tipoBanner = 'divulgacao';
        }

        // Valida imagem
        if (!isset($_FILES['imagem_arquivo']) || $_FILES['imagem_arquivo']['error'] !== UPLOAD_ERR_OK) {
            $this->sendResponse($isAjax, false, 'Por favor, selecione uma imagem para o banner.');
            return;
        }

        $file = $_FILES['imagem_arquivo'];
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (($file['size'] ?? 0) > $maxSize || !isset($allowedTypes[$file['type'] ?? ''])) {
            $this->sendResponse($isAjax, false, 'Arquivo inválido. Use JPG, PNG ou GIF até 5MB.');
            return;
        }

        // Faz upload da imagem
        $ext = $allowedTypes[$file['type']];
        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'banners';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'] ?? ('banner_' . time()), PATHINFO_FILENAME));
        $filename = $safeBase . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        
        if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->sendResponse($isAjax, false, 'Erro ao fazer upload da imagem.');
            return;
        }

        $imagem = '../uploads/banners/' . $filename;
        $ativo = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;

        // Cria o banner
        $bannerId = $this->bannerModel->addBanner($titulo, $imagem, $tipoBanner, $ativo);
        
        if (!$bannerId) {
            $this->sendResponse($isAjax, false, 'Erro ao cadastrar banner. Verifique se as tabelas foram criadas.');
            return;
        }

        // Para banners de divulgação, cria categoria fantasma automaticamente
        $categoriaFantasmaId = null;
        if ($tipoBanner === 'divulgacao') {
            $categoriaFantasmaId = $this->bannerModel->createBannerCategory($bannerId, $titulo);
        }

        // Processa categorias (multiselect) - adiciona às categorias existentes
        $categoriasIds = [];
        if ($categoriaFantasmaId) {
            $categoriasIds[] = $categoriaFantasmaId; // Sempre inclui a categoria fantasma
        }
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categoriasAdicionais = array_map('intval', $_POST['categorias']);
            $categoriasAdicionais = array_filter($categoriasAdicionais, function($id) { return $id > 0; });
            // Adiciona categorias adicionais sem duplicar a fantasma
            foreach ($categoriasAdicionais as $catId) {
                if (!in_array($catId, $categoriasIds)) {
                    $categoriasIds[] = $catId;
                }
            }
        }
        $this->bannerModel->setBannerCategories($bannerId, $categoriasIds);

        // Processa produtos (apenas para banners de divulgação)
        if ($tipoBanner === 'divulgacao' && isset($_POST['produtos']) && is_array($_POST['produtos'])) {
            $produtos = [];
            $produtosIds = []; // Para evitar duplicatas
            foreach ($_POST['produtos'] as $index => $produtoId) {
                $produtoId = (int)$produtoId;
                // Evita duplicatas
                if ($produtoId > 0 && !in_array($produtoId, $produtosIds)) {
                    $produtosIds[] = $produtoId;
                    $produtos[] = [
                        'id_produto' => $produtoId,
                        'ordem' => count($produtos) // Ordem sequencial baseada na posição
                    ];
                }
            }
            $this->bannerModel->setBannerProducts($bannerId, $produtos);
        } else {
            // Remove produtos se não for banner de divulgação
            $this->bannerModel->setBannerProducts($bannerId, []);
        }

        $this->sendResponse($isAjax, true, 'Banner cadastrado com sucesso!');
    }

    private function handleEdit($isAjax) {
        $bannerId = intval($_POST['id_banner'] ?? 0);
        if ($bannerId <= 0) {
            $this->sendResponse($isAjax, false, 'ID do banner inválido.');
            return;
        }

        $banner = $this->bannerModel->getBannerById($bannerId);
        if (!$banner) {
            $this->sendResponse($isAjax, false, 'Banner não encontrado.');
            return;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        if (empty($titulo)) {
            $this->sendResponse($isAjax, false, 'Por favor, informe o título do banner.');
            return;
        }

        $tipoBanner = $_POST['tipo_banner'] ?? 'divulgacao';
        if (!in_array($tipoBanner, ['divulgacao', 'decoracao'])) {
            $tipoBanner = 'divulgacao';
        }

        $ativo = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;
        $imagem = null;

        // Processa upload de nova imagem (opcional)
        if (isset($_FILES['imagem_arquivo']) && $_FILES['imagem_arquivo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagem_arquivo'];
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            $maxSize = 5 * 1024 * 1024;

            if (($file['size'] ?? 0) <= $maxSize && isset($allowedTypes[$file['type'] ?? ''])) {
                $ext = $allowedTypes[$file['type']];
                $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'banners';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'] ?? ('banner_' . time()), PATHINFO_FILENAME));
                $filename = $safeBase . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                
                if (@move_uploaded_file($file['tmp_name'], $destPath)) {
                    $imagem = '../uploads/banners/' . $filename;
                }
            }
        }

        // Atualiza o banner
        $success = $this->bannerModel->updateBanner($bannerId, $titulo, $imagem, $tipoBanner, $ativo);
        
        if (!$success) {
            $this->sendResponse($isAjax, false, 'Erro ao atualizar banner.');
            return;
        }

        // Para banners de divulgação, garante que existe categoria fantasma
        $categoriaFantasmaId = null;
        if ($tipoBanner === 'divulgacao') {
            $categoriaFantasma = $this->bannerModel->getBannerMainCategory($bannerId);
            if (!$categoriaFantasma) {
                // Cria categoria fantasma se não existir
                $categoriaFantasmaId = $this->bannerModel->createBannerCategory($bannerId, $titulo);
            } else {
                $categoriaFantasmaId = (int)$categoriaFantasma['id_categoria'];
            }
        }

        // Atualiza categorias - mantém categoria fantasma e adiciona outras
        $categoriasIds = [];
        if ($categoriaFantasmaId) {
            $categoriasIds[] = $categoriaFantasmaId; // Sempre inclui a categoria fantasma
        }
        if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categoriasAdicionais = array_map('intval', $_POST['categorias']);
            $categoriasAdicionais = array_filter($categoriasAdicionais, function($id) { return $id > 0; });
            // Adiciona categorias adicionais sem duplicar a fantasma
            foreach ($categoriasAdicionais as $catId) {
                if (!in_array($catId, $categoriasIds)) {
                    $categoriasIds[] = $catId;
                }
            }
        }
        $this->bannerModel->setBannerCategories($bannerId, $categoriasIds);

        // Atualiza produtos (apenas para banners de divulgação)
        if ($tipoBanner === 'divulgacao') {
            $produtos = [];
            $produtosIds = []; // Para evitar duplicatas
            if (isset($_POST['produtos']) && is_array($_POST['produtos'])) {
                foreach ($_POST['produtos'] as $index => $produtoId) {
                    $produtoId = (int)$produtoId;
                    // Evita duplicatas
                    if ($produtoId > 0 && !in_array($produtoId, $produtosIds)) {
                        $produtosIds[] = $produtoId;
                        $produtos[] = [
                            'id_produto' => $produtoId,
                            'ordem' => count($produtos) // Ordem sequencial baseada na posição
                        ];
                    }
                }
            }
            $this->bannerModel->setBannerProducts($bannerId, $produtos);
        } else {
            // Remove produtos se mudou para decoração
            $this->bannerModel->setBannerProducts($bannerId, []);
        }

        $this->sendResponse($isAjax, true, 'Banner atualizado com sucesso!');
    }

    private function handleDelete($isAjax) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $result = $this->bannerModel->deleteBanner($id);
            $this->sendResponse($isAjax, $result, $result ? 'Banner excluído com sucesso.' : 'Erro ao excluir banner.');
        } else {
            $this->sendResponse($isAjax, false, 'ID inválido.');
        }
    }

    private function handleUpdateProductOrder($isAjax) {
        $bannerId = intval($_POST['banner_id'] ?? 0);
        if ($bannerId <= 0) {
            $this->sendResponse($isAjax, false, 'ID do banner inválido.');
            return;
        }

        if (!isset($_POST['produtos']) || !is_array($_POST['produtos'])) {
            $this->sendResponse($isAjax, false, 'Lista de produtos inválida.');
            return;
        }

        $produtos = [];
        foreach ($_POST['produtos'] as $index => $produtoId) {
            $produtos[] = [
                'id_produto' => (int)$produtoId,
                'ordem' => (int)$index
            ];
        }

        $success = $this->bannerModel->setBannerProducts($bannerId, $produtos);
        $this->sendResponse($isAjax, $success, $success ? 'Ordem atualizada com sucesso.' : 'Erro ao atualizar ordem.');
    }

    private function sendResponse($isAjax, $success, $message) {
        $response = ['success' => $success, 'message' => $message];
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $_SESSION['msg'] = $message;
            header('Location: ../bannersadm.php');
            exit;
        }
    }

    public function getData(): array {
        $msg = $_SESSION['msg'] ?? '';
        unset($_SESSION['msg']);

        // Busca todas as categorias (para multiselect)
        $allCategories = $this->categoryModel->getCategories();
        
        // Busca todos os produtos (para seleção)
        $allProducts = $this->productModel->getProducts();
        
        // Busca todos os banners com relações
        $banners = $this->bannerModel->getAllBannersWithRelations();

        return [
            'msg' => $msg,
            'banners' => $banners,
            'allCategories' => $allCategories,
            'allProducts' => $allProducts,
        ];
    }

    public function getBannerForEdit(int $id): ?array {
        return $this->bannerModel->getBannerWithRelations($id);
    }

    public function getBannerProducts(int $id): array {
        return $this->bannerModel->getBannerProducts($id);
    }
}

if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new BannerController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->handlePost();
    } else {
        header('Location: ../bannersadm.php');
        exit;
    }
}
