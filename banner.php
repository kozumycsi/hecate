<?php
/**
 * Roteador para banners
 * Suporta: /banner.php?id=X&action=produtos
 * Ou: /banner.php?banner_id=X&action=produtos
 */

$bannerId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['banner_id']) ? (int)$_GET['banner_id'] : 0);
$action = $_GET['action'] ?? 'produtos';

if ($bannerId <= 0) {
    header('HTTP/1.0 404 Not Found');
    die('Banner não encontrado');
}

// Redireciona para a página apropriada
if ($action === 'produtos') {
    header('Location: banner-produtos.php?id=' . $bannerId);
    exit;
}

// Se não houver ação específica, também mostra produtos
header('Location: banner-produtos.php?id=' . $bannerId);
exit;

