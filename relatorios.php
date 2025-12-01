<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/model/RelatorioModel.php';
require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/model/PedidoModel.php';
require_once __DIR__ . '/model/CategoryModel.php';

$relatorioModel = new RelatorioModel();
$productModel = new ProductModel();
$pedidoModel = new PedidoModel();
$categoryModel = new CategoryModel();

// Dados para gráficos
$produtosMaisVendidos = $relatorioModel->getProdutosMaisVendidos(5);
$clientesTop = $relatorioModel->getClientesTopCompradores(5);
$faturamentoMensal = $relatorioModel->getFaturamentoMensal();
$vendasPorCategoria = $relatorioModel->getVendasPorCategoria();
$statusPedidos = $relatorioModel->getStatusPedidos();

// Totalizadores
$totalUsuarios = $relatorioModel->getTotalUsuarios();
$totalProdutos = $productModel->countProducts();
$totalPedidos = $pedidoModel->countPedidos();
$totalCategorias = count($categoryModel->getCategories());
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Relatórios</title>
    <link rel="stylesheet" href="paineladm.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
            background-color: #0056b3;
        }
        .report-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        .chart-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
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
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios" class="active">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <div class="header-bar">Relatórios e Estatísticas</div>

        <!-- Cards de Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3>Total de Usuários</h3>
                <div class="value"><?= $totalUsuarios ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Total de Produtos</h3>
                <div class="value"><?= $totalProdutos ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Total de Pedidos</h3>
                <div class="value"><?= $totalPedidos ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3>Total de Categorias</h3>
                <div class="value"><?= $totalCategorias ?></div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="chart-row">
            <div class="report-section">
                <h3>Produtos Mais Vendidos</h3>
                <div class="chart-container">
                    <canvas id="produtosChart"></canvas>
                </div>
            </div>

            <div class="report-section">
                <h3>Status dos Pedidos</h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h3>Faturamento Mensal (<?= date('Y') ?>)</h3>
            <div class="chart-container" style="height: 400px;">
                <canvas id="faturamentoChart"></canvas>
            </div>
        </div>

        <div class="chart-row">
            <div class="report-section">
                <h3>Vendas por Categoria</h3>
                <div class="chart-container">
                    <canvas id="categoriasChart"></canvas>
                </div>
            </div>

            <div class="report-section">
                <h3>Top 5 Clientes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Pedidos</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientesTop as $cliente): ?>
                            <tr>
                                <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                <td><?= $cliente['total_pedidos'] ?></td>
                                <td>R$ <?= number_format($cliente['valor_total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($clientesTop)): ?>
                            <tr><td colspan="3">Nenhum dado disponível.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Produtos Mais Vendidos
        const produtosData = {
            labels: [<?php echo implode(',', array_map(function($p) { return '"' . addslashes($p['nome']) . '"'; }, $produtosMaisVendidos)); ?>],
            datasets: [{
                label: 'Quantidade Vendida',
                data: [<?php echo implode(',', array_column($produtosMaisVendidos, 'total_vendido')); ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        };
        new Chart(document.getElementById('produtosChart'), {
            type: 'bar',
            data: produtosData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Status dos Pedidos
        const statusData = {
            labels: [<?php echo implode(',', array_map(function($s) { return '"' . addslashes($s['status']) . '"'; }, $statusPedidos)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($statusPedidos, 'quantidade')); ?>],
                backgroundColor: ['#ffc107', '#28a745', '#17a2b8', '#6c757d', '#dc3545'],
            }]
        };
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Faturamento Mensal
        const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        const faturamentoArray = Array(12).fill(0);
        <?php foreach ($faturamentoMensal as $f): ?>
            faturamentoArray[<?= $f['mes'] - 1 ?>] = <?= $f['faturamento'] ?>;
        <?php endforeach; ?>
        
        const faturamentoData = {
            labels: meses,
            datasets: [{
                label: 'Faturamento (R$)',
                data: faturamentoArray,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
        new Chart(document.getElementById('faturamentoChart'), {
            type: 'line',
            data: faturamentoData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Vendas por Categoria
        const categoriasData = {
            labels: [<?php echo implode(',', array_map(function($c) { return '"' . addslashes($c['categoria']) . '"'; }, $vendasPorCategoria)); ?>],
            datasets: [{
                label: 'Receita (R$)',
                data: [<?php echo implode(',', array_column($vendasPorCategoria, 'receita')); ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ]
            }]
        };
        new Chart(document.getElementById('categoriasChart'), {
            type: 'pie',
            data: categoriasData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
