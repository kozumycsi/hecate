<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/controller/PedidoController.php';

$controller = new PedidoController();
$data = $controller->getData();

$msg = $data['msg'] ?? '';
$pedidos = $data['pedidos'] ?? [];
$viewPedido = $data['viewPedido'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Gerenciamento de Pedidos</title>
    <link rel="stylesheet" href="paineladm.css" />
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .blue-bar { background-color: #0056b3; }
        .yellow-bar { background-color: #f1c40f; color: black; }
        .list-section {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-bar {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-filtrar {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 3px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        .msg { color: green; margin-bottom: 10px; }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-aguardando { background-color: #ffc107; color: black; }
        .status-pago { background-color: #28a745; color: white; }
        .status-em-transporte { background-color: #17a2b8; color: white; }
        .status-entregue { background-color: #6c757d; color: white; }
        .status-cancelado { background-color: #dc3545; color: white; }
        .btn-action {
            padding: 4px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .btn-view { background-color: #17a2b8; color: white; }
        .btn-cancel { background-color: #dc3545; color: white; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
        .detail-section {
            margin-bottom: 15px;
        }
        .detail-section h3 {
            border-bottom: 2px solid #0056b3;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias">Categorias</a>
        <a href="produtosadm.php" data-section="produtos">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos" class="active">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="filter-bar">
            <form method="get" action="pedidosadm.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; width: 100%;">
                <label for="status-filter" style="margin: 0;">Status:</label>
                <select id="status-filter" name="status" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px;">
                    <option value="">Todos</option>
                    <option value="Aguardando pagamento" <?= isset($_GET['status']) && $_GET['status'] == 'Aguardando pagamento' ? 'selected' : '' ?>>Aguardando pagamento</option>
                    <option value="Pago" <?= isset($_GET['status']) && $_GET['status'] == 'Pago' ? 'selected' : '' ?>>Pago</option>
                    <option value="Em transporte" <?= isset($_GET['status']) && $_GET['status'] == 'Em transporte' ? 'selected' : '' ?>>Em transporte</option>
                    <option value="Entregue" <?= isset($_GET['status']) && $_GET['status'] == 'Entregue' ? 'selected' : '' ?>>Entregue</option>
                    <option value="Cancelado" <?= isset($_GET['status']) && $_GET['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
                
                <label for="data-inicio" style="margin: 0;">De:</label>
                <input type="date" id="data-inicio" name="data_inicio" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px;" />
                
                <label for="data-fim" style="margin: 0;">Até:</label>
                <input type="date" id="data-fim" name="data_fim" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px;" />

                <label for="valor-min" style="margin: 0;">Valor mín.:</label>
                <input type="number" step="0.01" id="valor-min" name="valor_min" value="<?= htmlspecialchars($_GET['valor_min'] ?? '') ?>" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 110px;" />

                <label for="valor-max" style="margin: 0;">Valor máx.:</label>
                <input type="number" step="0.01" id="valor-max" name="valor_max" value="<?= htmlspecialchars($_GET['valor_max'] ?? '') ?>" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 110px;" />

                <label for="cliente" style="margin: 0;">Cliente/CPF:</label>
                <input type="text" id="cliente" name="cliente" value="<?= htmlspecialchars($_GET['cliente'] ?? '') ?>" placeholder="Nome ou CPF" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px; min-width: 200px;" />
                
                <button type="submit" class="btn-filtrar">Filtrar</button>
                <a href="pedidosadm.php" class="btn-filtrar" style="text-decoration: none; display: inline-block;">Limpar</a>
            </form>
        </div>

        <div class="list-section">
            <div class="header-bar yellow-bar">Lista de Pedidos</div>
            <table>
                <thead>
                    <tr>
                        <th>Nº Pedido</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $ped): ?>
                        <?php
                            $statusClass = '';
                            $statusNorm = strtolower(str_replace(' ', '-', $ped['status']));
                            $statusClass = 'status-' . $statusNorm;
                        ?>
                        <tr>
                            <td>#<?= $ped['id_pedido'] ?></td>
                            <td><?= htmlspecialchars($ped['cliente_nome'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ped['data_pedido'])) ?></td>
                            <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($ped['status']) ?></span></td>
                            <td>R$ <?= number_format($ped['total'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($ped['metodo_pagamento'] ?? 'N/A') ?></td>
                            <td>
                                <a href="pedidosadm.php?view=<?= $ped['id_pedido'] ?>" class="btn-action btn-view">Ver Detalhes</a>
                                
                                <form method="post" action="../controller/PedidoController.php" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status" />
                                    <input type="hidden" name="id" value="<?= $ped['id_pedido'] ?>" />
                                    <select name="status" style="padding: 3px; font-size: 11px;" onchange="this.form.submit()">
                                        <option value="">Alterar Status</option>
                                        <option value="Aguardando pagamento">Aguardando pagamento</option>
                                        <option value="Pago">Pago</option>
                                        <option value="Em transporte">Em transporte</option>
                                        <option value="Entregue">Entregue</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pedidos)): ?>
                        <tr><td colspan="7">Nenhum pedido encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($viewPedido): ?>
    <div id="pedidoModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" onclick="window.location.href='pedidosadm.php'">&times;</span>
            <h2>Detalhes do Pedido #<?= $viewPedido['id_pedido'] ?></h2>
            
            <div class="detail-section">
                <h3>Informações do Cliente</h3>
                <p><strong>Nome:</strong> <?= htmlspecialchars($viewPedido['cliente_nome'] ?? 'N/A') ?></p>
                <p><strong>CPF:</strong> <?= htmlspecialchars($viewPedido['cliente_cpf'] ?? 'N/A') ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($viewPedido['cliente_telefone'] ?? 'N/A') ?></p>
            </div>

            <div class="detail-section">
                <h3>Endereço de Entrega</h3>
                <p><?= htmlspecialchars($viewPedido['logradouro'] ?? 'N/A') ?></p>
                <p><?= htmlspecialchars($viewPedido['cidade'] ?? 'N/A') ?> - <?= htmlspecialchars($viewPedido['estado'] ?? 'N/A') ?></p>
                <p>CEP: <?= htmlspecialchars($viewPedido['cep'] ?? 'N/A') ?></p>
            </div>

            <div class="detail-section">
                <h3>Itens do Pedido</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewPedido['itens'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['produto_nome'] ?? 'N/A') ?></td>
                                <td><?= $item['quantidade'] ?></td>
                                <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="detail-section">
                <h3>Pagamento</h3>
                <p><strong>Método:</strong> <?= htmlspecialchars($viewPedido['metodo_pagamento'] ?? 'N/A') ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($viewPedido['status_pagamento'] ?? 'N/A') ?></p>
                <p><strong>Total:</strong> R$ <?= number_format($viewPedido['total'], 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
