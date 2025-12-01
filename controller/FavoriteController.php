<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

if (empty($_SESSION['idusuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Faça login para usar favoritos.']);
    exit();
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Produto inválido.']);
    exit();
}

try {
    require_once __DIR__ . '/../model/FavoriteModel.php';
    $model = new FavoriteModel();
    $isFavorite = $model->toggleFavorite((int)$_SESSION['idusuario'], $productId);
    $count = $model->countFavorites((int)$_SESSION['idusuario']);

    echo json_encode([
        'success' => true,
        'isFavorite' => $isFavorite,
        'favoritesCount' => $count
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar favoritos.']);
}

