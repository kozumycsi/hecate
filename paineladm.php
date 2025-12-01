<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/service/path_helper.php';

require_once __DIR__ . '/model/CategoryModel.php';
require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/model/PedidoModel.php';
require_once __DIR__ . '/model/RelatorioModel.php';

$categoryModel = new CategoryModel();
$productModel = new ProductModel();
$pedidoModel = new PedidoModel();
$relatorioModel = new RelatorioModel();

// Buscar dados para os indicadores
$totalUsuarios = $relatorioModel->getTotalUsuarios();
$totalProdutos = $productModel->countProducts();
$totalCategorias = count($categoryModel->getCategories());
$totalPedidos = $pedidoModel->countPedidos();

// Buscar produtos recentes
$produtosRecentes = $productModel->getRecentProducts(5);

$categories = $categoryModel->getCategories();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="paineladm.css">
  <script src="paineladm.js"></script>
</head>
<body>

  <div class="sidebar">
    <a href="paineladm.php" data-section="indicadores" class="active">Indicadores</a>
    <a href="category.php" data-section="categorias">Categorias</a>
    <a href="produtosadm.php" data-section="produtos">Produtos</a>
    <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
    <a href="bannersadm.php" data-section="banners">Banners</a>
    <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
    <a href="relatorios.php" data-section="relatorios">Relatórios</a>
    <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
  </div>

  <div class="main" id="indicadores-content">
    <?php if (!empty($_SESSION['is_admin'])): ?>
        <div style="margin-bottom: 20px;">
            <a href="index.php" class="btn" style="background-color: #69110c; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;">
                <i class="fas fa-home"></i> Voltar para Home
            </a>
        </div>
    <?php endif; ?>
    <h1>INDICADORES</h1>

    <div class="cards">
      <div class="card blue-card">
        <h2><?= $totalUsuarios ?></h2>
        <p>Usuários</p>
        <div class="card-icon user-icon"></div>
      </div>
      <div class="card green-card">
        <h2><?= $totalProdutos ?></h2>
        <p>Produtos</p>
        <div class="card-icon product-icon"></div>
      </div>
      <div class="card yellow-card">
        <h2><?= $totalCategorias ?></h2>
        <p>Categorias</p>
        <div class="card-icon category-icon"></div>
      </div>
      <div class="card red-card">
        <h2><?= $totalPedidos ?></h2>
        <p>Pedidos</p>
        <div class="card-icon purchase-icon"></div>
      </div>
    </div>

    <div class="content-row">
      <div class="produtos">
        <div class="produtos-header">
          <h3>Produtos recém adicionados</h3>
          <button class="btn-adicionar">Adicionar</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Nome</th>
              <th>Categorias</th>
              <th>Estoque</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produtosRecentes as $prod): ?>
              <tr>
                <td><?= htmlspecialchars($prod['nome']) ?></td>
                <td><?= htmlspecialchars($prod['categoria_nome'] ?? 'N/A') ?></td>
                <td><?= $prod['estoque'] ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($produtosRecentes)): ?>
              <tr><td colspan="3">Nenhum produto cadastrado.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="categorias">
        <h2>Lista de Categorias</h2>
        <table>
          <thead>
            <tr>
              <th>Nome</th>
              <th>Tipo</th>
              <th>Quantidade</th>
              <th>Descrição</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td><?= htmlspecialchars($cat['nome']) ?></td>
                <td><?= htmlspecialchars($cat['tipo']) ?></td>
                <td><?= htmlspecialchars($cat['quantidade'] ?? '') ?></td>
                <td><?= htmlspecialchars($cat['descricao'] ?? '') ?></td>
                <td>
                  <button class="btn editar">Editar</button>
                  <button class="btn excluir">Remover</button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
              <tr><td colspan="5">Nenhuma categoria cadastrada.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>
