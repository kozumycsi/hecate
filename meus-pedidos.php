<?php
session_start();

if (empty($_SESSION['idusuario'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/PedidoModel.php';
require_once __DIR__ . '/service/path_helper.php';

$pedidoModel = new PedidoModel();
$pedidos = $pedidoModel->getPedidosByUsuario($_SESSION['idusuario']);

// Buscar itens de cada pedido e resolver caminhos das imagens
$pedidosCompletos = [];
foreach ($pedidos as $pedido) {
    $itens = $pedidoModel->getItensDoPedido($pedido['id_pedido']);
    // Resolve caminhos das imagens
    foreach ($itens as &$item) {
        if (!empty($item['imagem'])) {
            $item['imagem'] = resolve_asset_path($item['imagem']);
        } else {
            $item['imagem'] = asset_url('img/logo.png');
        }
    }
    unset($item);
    $pedido['itens'] = $itens;
    $pedidosCompletos[] = $pedido;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="meus-pedidos.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-shopping-bag"></i> Meus Pedidos</h4>
                    </div>
                    <div class="card-body">
                        <div id="lista-pedidos">
                            <!-- Os pedidos serão carregados aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Passa os pedidos do PHP para o JavaScript
        const pedidosData = <?= json_encode($pedidosCompletos) ?>;
    </script>
    <script src="meus-pedidos.js"></script>
</body>
</html> 