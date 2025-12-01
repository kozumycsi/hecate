<?php
require_once __DIR__ . '/../model/CartModel.php';

class CartController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new CartModel();
    }

    /**
     * Processa requisições AJAX
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'add_item':
                $this->addItem();
                break;
            case 'update_quantity':
                $this->updateQuantity();
                break;
            case 'remove_item':
                $this->removeItem();
                break;
            case 'remove_items':
                $this->removeItems();
                break;
            case 'clear_cart':
                $this->clearCart();
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
                exit;
        }
    }

    /**
     * Adiciona um item ao carrinho
     */
    public function addItem() {
        if (empty($_SESSION['idusuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para adicionar produtos ao carrinho.']);
            exit;
        }

        $userId = (int)$_SESSION['idusuario'];
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $tamanho = isset($_POST['tamanho']) ? trim($_POST['tamanho']) : null;
        $cor = isset($_POST['cor']) ? trim($_POST['cor']) : null;

        if ($productId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID do produto inválido.']);
            exit;
        }

        // Verifica o estoque disponível
        require_once __DIR__ . '/../model/ProductModel.php';
        $productModel = new ProductModel();
        $product = $productModel->getProductById($productId);
        
        if (!$product) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
            exit;
        }

        $estoque = (int)($product['estoque'] ?? 0);
        
        // Verifica se há estoque suficiente
        if ($estoque < $quantity) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Estoque insuficiente. Disponível: ' . $estoque]);
            exit;
        }

        // Verifica quantidade atual no carrinho
        $cartItems = $this->model->getCartItems($userId);
        $currentQuantity = 0;
        foreach ($cartItems as $item) {
            if ((int)$item['id_produto'] === $productId && 
                ($item['tamanho'] ?? null) === $tamanho && 
                ($item['cor'] ?? null) === $cor) {
                $currentQuantity = (int)$item['quantidade'];
                break;
            }
        }

        if ($currentQuantity + $quantity > $estoque) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Quantidade excede o estoque disponível. Disponível: ' . ($estoque - $currentQuantity)]);
            exit;
        }

        $result = $this->model->addItem($userId, $productId, $quantity, $tamanho, $cor);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Produto adicionado ao carrinho!' : 'Erro ao adicionar produto ao carrinho.'
        ]);
        exit;
    }

    /**
     * Atualiza a quantidade de um item
     */
    public function updateQuantity() {
        if (empty($_SESSION['idusuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado.']);
            exit;
        }

        $userId = (int)$_SESSION['idusuario'];
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        if ($itemId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID do item inválido.']);
            exit;
        }

        // Busca o item para verificar o estoque
        $cartItems = $this->model->getCartItems($userId);
        $item = null;
        foreach ($cartItems as $cartItem) {
            if ((int)$cartItem['id'] === $itemId) {
                $item = $cartItem;
                break;
            }
        }

        if (!$item) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Item não encontrado no carrinho.']);
            exit;
        }

        $estoque = (int)($item['estoque'] ?? 0);
        
        if ($quantity > $estoque) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Quantidade máxima disponível no estoque: ' . $estoque]);
            exit;
        }

        $result = $this->model->updateQuantity($userId, $itemId, $quantity);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Quantidade atualizada!' : 'Erro ao atualizar quantidade.'
        ]);
        exit;
    }

    /**
     * Remove um item do carrinho
     */
    public function removeItem() {
        if (empty($_SESSION['idusuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado.']);
            exit;
        }

        $userId = (int)$_SESSION['idusuario'];
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

        if ($itemId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID do item inválido.']);
            exit;
        }

        $result = $this->model->removeItem($userId, $itemId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Item removido do carrinho!' : 'Erro ao remover item.'
        ]);
        exit;
    }

    /**
     * Remove múltiplos itens
     */
    public function removeItems() {
        if (empty($_SESSION['idusuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado.']);
            exit;
        }

        $userId = (int)$_SESSION['idusuario'];
        $itemIds = isset($_POST['item_ids']) && is_array($_POST['item_ids']) 
            ? array_map('intval', $_POST['item_ids']) 
            : [];

        if (empty($itemIds)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Nenhum item selecionado.']);
            exit;
        }

        $result = $this->model->removeItems($userId, $itemIds);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Itens removidos do carrinho!' : 'Erro ao remover itens.'
        ]);
        exit;
    }

    /**
     * Retorna os itens do carrinho
     */
    public function getCartItems(): array {
        if (empty($_SESSION['idusuario'])) {
            return [];
        }

        $userId = (int)$_SESSION['idusuario'];
        return $this->model->getCartItems($userId);
    }

    /**
     * Retorna a contagem de itens
     */
    public function getCartCount(): int {
        if (empty($_SESSION['idusuario'])) {
            return 0;
        }

        $userId = (int)$_SESSION['idusuario'];
        return $this->model->countItems($userId);
    }

    /**
     * Limpa todo o carrinho do usuário
     */
    public function clearCart() {
        if (empty($_SESSION['idusuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Você precisa estar logado.']);
            exit;
        }

        $userId = (int)$_SESSION['idusuario'];
        $result = $this->model->clearCart($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Carrinho limpo com sucesso!' : 'Erro ao limpar carrinho.'
        ]);
        exit;
    }
}

// Se o arquivo for acessado diretamente via AJAX
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new CartController();
    $controller->handleRequest();
}

