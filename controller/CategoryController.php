<?php
require_once __DIR__ . '/../model/CategoryModel.php';

class CategoryController {
    private $model;
    
    public function getModel() {
        return $this->model;
    }

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new CategoryModel();
    }

    public function handlePost() {
        $action = $_POST['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;
            $active = isset($_POST['active']) ? 1 : 0;
            $sortOrder = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? intval($_POST['sort_order']) : null;
            if ($name !== '' && $type !== '') {
                $result = $this->model->addCategory($name, $type, $parentId, $active, $sortOrder);
                $response = ['success' => $result, 'message' => $result ? 'Categoria adicionada com sucesso.' : 'Erro ao adicionar categoria.'];
            } else {
                $response = ['success' => false, 'message' => 'Por favor, preencha todos os campos.'];
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../category.php');
                exit;
            }
        }

        if ($action === 'update') {
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;
            $active = isset($_POST['active']) ? 1 : 0;
            $sortOrder = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? intval($_POST['sort_order']) : null;
            if ($id > 0 && $name !== '' && $type !== '') {
                $result = $this->model->updateCategory($id, $name, $type, $parentId, $active, $sortOrder);
                $response = ['success' => $result, 'message' => $result ? 'Categoria atualizada com sucesso.' : 'Erro ao atualizar categoria.'];
            } else {
                $response = ['success' => false, 'message' => 'Por favor, preencha todos os campos.'];
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../category.php');
                exit;
            }
        }

        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            $moveToCategoryId = isset($_POST['move_to_category']) && $_POST['move_to_category'] !== '' ? intval($_POST['move_to_category']) : null;
            $force = isset($_POST['force']) && $_POST['force'] === '1';
            
            if ($id > 0) {
                try {
                    // Verifica se pode deletar
                    $check = $this->model->canDeleteCategory($id);
                    
                    if (!$check['canDelete']) {
                        // Se houver produtos e uma categoria destino foi fornecida, move os produtos
                        if ($moveToCategoryId !== null && $moveToCategoryId > 0) {
                            $this->model->moveProductsToCategory($id, $moveToCategoryId);
                            // Verifica novamente
                            $check = $this->model->canDeleteCategory($id);
                        }
                        
                        if (!$check['canDelete'] && !$force) {
                            $issuesText = implode(', ', $check['issues']);
                            $response = [
                                'success' => false, 
                                'message' => 'Não é possível excluir esta categoria: ' . $issuesText . '. ' .
                                           'Mova ou exclua os itens vinculados primeiro, ou selecione uma categoria para mover os produtos.'
                            ];
                        } else {
                            // Força a exclusão mesmo com dependências
                            $result = $this->model->deleteCategory($id, $moveToCategoryId, $force);
                            $response = ['success' => $result, 'message' => $result ? 'Categoria excluída com sucesso.' : 'Erro ao excluir categoria.'];
                        }
                    } else {
                        // Pode deletar sem problemas
                        $result = $this->model->deleteCategory($id);
                        $response = ['success' => $result, 'message' => $result ? 'Categoria excluída com sucesso.' : 'Erro ao excluir categoria.'];
                    }
                } catch (\Exception $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                } catch (\Throwable $e) {
                    $response = ['success' => false, 'message' => 'Erro ao excluir categoria: ' . $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID inválido.'];
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../category.php');
                exit;
            }
        }

        if ($action === 'update_order') {
            $id = intval($_POST['id'] ?? 0);
            $sortOrder = isset($_POST['sort_order']) && $_POST['sort_order'] !== '' ? intval($_POST['sort_order']) : null;

            if ($id > 0) {
                $result = $this->model->updateCategoryOrder($id, $sortOrder);
                $response = ['success' => $result, 'message' => $result ? 'Ordem atualizada com sucesso.' : 'Erro ao atualizar ordem.'];
            } else {
                $response = ['success' => false, 'message' => 'ID inválido.'];
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else {
                $_SESSION['msg'] = $response['message'];
                header('Location: ../category.php');
                exit;
            }
        }
    }

    public function getCategoryByIdAjax($id) {
        $category = $this->model->getCategoryById($id);
        header('Content-Type: application/json');
        if ($category) {
            echo json_encode(['success' => true, 'category' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Categoria não encontrada.']);
        }
        exit;
    }

 
    public function getData(): array {
        $msg = $_SESSION['msg'] ?? '';
        unset($_SESSION['msg']);

        $editCategory = null;
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            if ($id > 0) {
                $editCategory = $this->model->getCategoryById($id);
            }
        }

        // Load with product counts for admin listing
        if (method_exists($this->model, 'getCategoriesWithProductCounts')) {
            $categories = $this->model->getCategoriesWithProductCounts();
        } else {
            $categories = $this->model->getCategories();
        }

        // Principal categories for parent selection
        $principalCategories = method_exists($this->model, 'getCategoriesByType')
            ? $this->model->getCategoriesByType('Categoria Principal')
            : [];

        return ['msg' => $msg, 'editCategory' => $editCategory, 'categories' => $categories, 'principalCategories' => $principalCategories];
    }
}

// Se o arquivo for acessado diretamente e houver POST, trata aqui (form action aponta pra este arquivo)
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new CategoryController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->handlePost();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['id']) && !empty($_GET['ajax'])) {
        $id = intval($_GET['id']);
        $controller->getCategoryByIdAjax($id);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['check_delete'])) {
        // Verifica se pode deletar categoria
        $id = intval($_GET['check_delete']);
        $model = new CategoryModel();
        if (method_exists($model, 'canDeleteCategory')) {
            $check = $model->canDeleteCategory($id);
        } else {
            $check = ['canDelete' => false, 'issues' => ['Método de verificação não disponível']];
        }
        header('Content-Type: application/json');
        echo json_encode($check);
        exit;
    } else {
        header('Location: ../category.php');
        exit;
    }
}
