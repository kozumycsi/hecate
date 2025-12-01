<?php
require_once __DIR__ . '/../model/PedidoModel.php';

class PedidoController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new PedidoModel();
    }

    public function handlePost() {
        $action = $_POST['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($action === 'update_status') {
            $id = intval($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            if ($id > 0 && $status !== '') {
                $result = $this->model->updateStatus($id, $status);
                $response = ['success' => $result, 'message' => $result ? 'Status atualizado com sucesso.' : 'Erro ao atualizar status.'];
            } else {
                $response = ['success' => false, 'message' => 'Dados inválidos.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../pedidosadm.php');
                exit;
            }
        }

        if ($action === 'cancel') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = $this->model->cancelPedido($id);
                $response = ['success' => $result, 'message' => $result ? 'Pedido cancelado com sucesso.' : 'Erro ao cancelar pedido.'];
            } else {
                $response = ['success' => false, 'message' => 'ID inválido.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../pedidosadm.php');
                exit;
            }
        }
    }

    public function getData(): array {
        $msg = $_SESSION['msg'] ?? '';
        unset($_SESSION['msg']);

        $statusFilter = $_GET['status'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $valorMin = $_GET['valor_min'] ?? null;
        $valorMax = $_GET['valor_max'] ?? null;
        $cliente = $_GET['cliente'] ?? null;

        $pedidos = $this->model->getPedidos($statusFilter, $dataInicio, $dataFim, $valorMin, $valorMax, $cliente);

        $viewPedido = null;
        if (isset($_GET['view'])) {
            $id = intval($_GET['view']);
            if ($id > 0) {
                $viewPedido = $this->model->getPedidoById($id);
                if ($viewPedido) {
                    $viewPedido['itens'] = $this->model->getItensDoPedido($id);
                }
            }
        }

        return [
            'msg' => $msg,
            'pedidos' => $pedidos,
            'viewPedido' => $viewPedido
        ];
    }

    /**
     * Processa requisições JSON (para finalizar compra)
     */
    public function handleJsonRequest() {
        header('Content-Type: application/json');
        
        if (empty($_SESSION['idusuario'])) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            exit;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['action'])) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            exit;
        }

        if ($data['action'] === 'create_order') {
            try {
                $userId = (int)$_SESSION['idusuario'];
                $produtos = $data['produtos'] ?? [];
                $endereco = $data['endereco'] ?? [];
                $pagamento = $data['pagamento'] ?? [];
                $valores = $data['valores'] ?? [];

                if (empty($produtos)) {
                    echo json_encode(['success' => false, 'message' => 'Nenhum produto no pedido.']);
                    exit;
                }

                if (empty($endereco) || empty($endereco['logradouro']) || empty($endereco['numero'])) {
                    echo json_encode(['success' => false, 'message' => 'Endereço incompleto.']);
                    exit;
                }

                $pedidoId = $this->model->createPedidoCompleto($userId, $produtos, $endereco, $pagamento, $valores);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido criado com sucesso!',
                    'pedido_id' => $pedidoId
                ]);
            } catch (\Throwable $e) {
                error_log("Erro ao criar pedido: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao criar pedido: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        exit;
    }
}

if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new PedidoController();
    
    // Verifica se é requisição JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJsonRequest = strpos($contentType, 'application/json') !== false;
    
    // Também verifica se há dados JSON no input
    if (!$isJsonRequest && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $isJsonRequest = !empty($input) && json_decode($input) !== null;
    }
    
    if ($isJsonRequest) {
        $controller->handleJsonRequest();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->handlePost();
    } else {
        header('Location: ../pedidosadm.php');
        exit;
    }
}
