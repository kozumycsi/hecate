<?php
require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/CategoryModel.php';

class ProductController {
    private $model;
    private $categoryModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Busca a categoria "Todos" e retorna seu ID, ou null se não existir
     */
    private function getTodosCategoryId(): ?int {
        $todosCategory = $this->categoryModel->getCategoryByName('Todos');
        return $todosCategory ? (int)$todosCategory['id_categoria'] : null;
    }

    /**
     * Adiciona automaticamente a categoria "Todos" à lista de categorias
     */
    private function ensureTodosCategory(array $categorias): array {
        $todosId = $this->getTodosCategoryId();
        if ($todosId !== null && !in_array($todosId, $categorias, true)) {
            $categorias[] = $todosId;
        }
        return $categorias;
    }

    public function handlePost() {
        $action = $_POST['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($action === 'add') {
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            
            // Suporte para múltiplas categorias
            $categorias = isset($_POST['categorias']) && is_array($_POST['categorias']) ? array_map('intval', $_POST['categorias']) : [];
            
            // Se não selecionou múltiplas categorias, usa a categoria antiga (compatibilidade)
            if (empty($categorias)) {
                $categoria = intval($_POST['categoria'] ?? 0);
                $categorias = $categoria > 0 ? [$categoria] : [];
            }
            
            // Adiciona automaticamente a categoria "Todos" se ela existir
            $categorias = $this->ensureTodosCategory($categorias);
            
            // Para compatibilidade com o modelo antigo, usa a primeira categoria
            $categoria = !empty($categorias) ? $categorias[0] : 0;
            
            $estoque = intval($_POST['estoque'] ?? 0);
            $imagem = $_POST['imagem'] ?? null;
            $tamanhosSelecionados = isset($_POST['tamanhos']) && is_array($_POST['tamanhos']) ? $_POST['tamanhos'] : [];
            $tamanhos = !empty($tamanhosSelecionados) ? implode(',', array_map('strtoupper', array_map('trim', $tamanhosSelecionados))) : null;
            $recemAdicionado = isset($_POST['recem_adicionado']) && $_POST['recem_adicionado'] === '1' ? 1 : 0;

            // Se um arquivo de imagem foi enviado, ele tem prioridade sobre a URL
            if (isset($_FILES['imagem_arquivo']) && is_array($_FILES['imagem_arquivo']) && ($_FILES['imagem_arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $file = $_FILES['imagem_arquivo'];
                $allowedTypes = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $fileType = strtolower($file['type'] ?? '');
                if (($file['size'] ?? 0) > 0 && ($file['size'] ?? 0) <= $maxSize && isset($allowedTypes[$fileType])) {
                    $ext = $allowedTypes[$fileType];
                    $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'] ?? ('img_' . time()), PATHINFO_FILENAME));
                    $filename = $safeBase . '_' . time() . '.' . $ext;
                    $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                    if (@move_uploaded_file($file['tmp_name'], $destPath)) {
                        // Caminho acessível a partir das páginas em /view
                        $imagem = '../uploads/products/' . $filename;
                    }
                }
            }
            $bannerCats = isset($_POST['banner_categorias']) && is_array($_POST['banner_categorias']) ? array_map('intval', $_POST['banner_categorias']) : [];

            if ($nome !== '' && $preco > 0 && !empty($categorias)) {
                $newId = $this->model->addProduct($nome, $descricao, $preco, $categoria, $estoque, $imagem, $tamanhos, $recemAdicionado);
                if ($newId !== false) {
                    // Salva múltiplas categorias (todas como principais)
                    if (!empty($categorias)) {
                        $this->model->setProductCategories((int)$newId, $categorias, null);
                    }
                    
                    // Salva vínculos com categorias de banner (opcional)
                    if (!empty($bannerCats)) {
                        $this->model->setProductBannerCategories((int)$newId, $bannerCats);
                    }
                    $response = ['success' => true, 'message' => 'Produto adicionado com sucesso.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erro ao adicionar produto.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../produtosadm.php');
                exit;
            }
        }

        if ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            
            // Suporte para múltiplas categorias
            $categorias = isset($_POST['categorias']) && is_array($_POST['categorias']) ? array_map('intval', $_POST['categorias']) : [];
            
            // Se não selecionou múltiplas categorias, usa a categoria antiga (compatibilidade)
            if (empty($categorias)) {
                $categoria = intval($_POST['categoria'] ?? 0);
                $categorias = $categoria > 0 ? [$categoria] : [];
            }
            
            // Adiciona automaticamente a categoria "Todos" se ela existir
            $categorias = $this->ensureTodosCategory($categorias);
            
            // Para compatibilidade com o modelo antigo, usa a primeira categoria
            $categoria = !empty($categorias) ? $categorias[0] : 0;
            
            $estoque = intval($_POST['estoque'] ?? 0);
            $imagem = $_POST['imagem'] ?? null;
            $tamanhosSelecionados = isset($_POST['tamanhos']) && is_array($_POST['tamanhos']) ? $_POST['tamanhos'] : [];
            $tamanhos = !empty($tamanhosSelecionados) ? implode(',', array_map('strtoupper', array_map('trim', $tamanhosSelecionados))) : null;
            $recemAdicionado = isset($_POST['recem_adicionado']) && $_POST['recem_adicionado'] === '1' ? 1 : 0;

            // Upload prioritário no update também
            if (isset($_FILES['imagem_arquivo']) && is_array($_FILES['imagem_arquivo']) && ($_FILES['imagem_arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $file = $_FILES['imagem_arquivo'];
                $allowedTypes = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $fileType = strtolower($file['type'] ?? '');
                if (($file['size'] ?? 0) > 0 && ($file['size'] ?? 0) <= $maxSize && isset($allowedTypes[$fileType])) {
                    $ext = $allowedTypes[$fileType];
                    $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'] ?? ('img_' . time()), PATHINFO_FILENAME));
                    $filename = $safeBase . '_' . time() . '.' . $ext;
                    $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                    if (@move_uploaded_file($file['tmp_name'], $destPath)) {
                        $imagem = '../uploads/products/' . $filename;
                    }
                }
            }
            $bannerCats = isset($_POST['banner_categorias']) && is_array($_POST['banner_categorias']) ? array_map('intval', $_POST['banner_categorias']) : [];

            if ($id > 0 && $nome !== '' && $preco > 0 && !empty($categorias)) {
                // Debug: log do valor recebido
                error_log("DEBUG ProductController: recemAdicionado = " . var_export($recemAdicionado, true) . " (tipo: " . gettype($recemAdicionado) . ")");
                
                $result = $this->model->updateProduct($id, $nome, $descricao, $preco, $categoria, $estoque, $imagem, $tamanhos, $recemAdicionado);
                if ($result !== false) {
                    // Atualiza múltiplas categorias (todas como principais)
                    if (!empty($categorias)) {
                        $this->model->setProductCategories((int)$id, $categorias, null);
                    }
                    
                    // Atualiza vínculos com categorias de banner (substitui completamente)
                    $this->model->setProductBannerCategories((int)$id, $bannerCats);
                }
                $response = ['success' => $result, 'message' => $result ? 'Produto atualizado com sucesso.' : 'Erro ao atualizar produto.'];
            } else {
                $response = ['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../produtosadm.php');
                exit;
            }
        }

        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = $this->model->deleteProduct($id);
                $response = ['success' => $result, 'message' => $result ? 'Produto excluído com sucesso.' : 'Erro ao excluir produto.'];
            } else {
                $response = ['success' => false, 'message' => 'ID inválido.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../produtosadm.php');
                exit;
            }
        }
    }

    public function getData(): array {
        $msg = $_SESSION['msg'] ?? '';
        unset($_SESSION['msg']);

        $editProduct = null;
        $editBannerCategories = [];
        $editProductCategories = [];
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            if ($id > 0) {
                $editProduct = $this->model->getProductById($id);
                // Pré-carrega categorias do produto (múltiplas)
                if ($editProduct) {
                    $editProductCategories = $this->model->getProductCategories((int)$id);
                    $editBannerCategories = $this->model->getBannerCategoriesForProduct((int)$id);
                }
            }
        }

        // Filtros
        $categoryFilter = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? intval($_GET['categoria']) : null;
        $tipoCategoria = isset($_GET['tipo']) ? trim($_GET['tipo']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null; // 'ativo' | 'inativo' | null

        $products = $this->model->getProducts($categoryFilter, $tipoCategoria, $status);
        
        // Remove produtos que são banners (vinculados a categorias tipo banner)
        $products = array_filter($products, function($prod) {
            return !$this->model->isBannerProduct((int)$prod['id_produto']);
        });
        $products = array_values($products); // Reindexa o array

        require_once __DIR__ . '/../model/CategoryModel.php';
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->getCategories();
        $bannerCategories = method_exists($categoryModel, 'getCategoriesByType')
            ? $categoryModel->getCategoriesByType('Categoria Tipo Banner')
            : [];

        return [
            'msg' => $msg,
            'editProduct' => $editProduct,
            'editProductCategories' => $editProductCategories,
            'editBannerCategories' => $editBannerCategories,
            'products' => $products,
            'categories' => $categories,
            'bannerCategories' => $bannerCategories,
        ];
    }
}

if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new ProductController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->handlePost();
    } else {
        header('Location: ../produtosadm.php');
        exit;
    }
}
