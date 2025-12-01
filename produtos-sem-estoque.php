<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/service/path_helper.php';
require_once __DIR__ . '/model/ProductModel.php';

$productModel = new ProductModel();
$produtosSemEstoque = $productModel->getProductsWithoutStock();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Produtos sem Estoque</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="paineladm.css" />
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .red-bar { background-color: #dc3545; }
        .list-section {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        .product-image { max-width: 50px; max-height: 50px; }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-sem-estoque { background-color: #dc3545; color: white; }
        a.action-edit { 
            color: #0056b3; 
            text-decoration: none; 
            margin-right: 10px;
            padding: 4px 8px;
            background-color: #0056b3;
            color: white;
            border-radius: 3px;
            display: inline-block;
        }
        a.action-edit:hover {
            background-color: #004085;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias">Categorias</a>
        <a href="produtosadm.php" data-section="produtos">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque" class="active">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <h1>Produtos sem Estoque</h1>
        <p style="color: #6c757d; margin-bottom: 20px;">
            Esta página lista automaticamente todos os produtos cujo estoque está igual a zero ou menor que zero.
            Quando um produto for reposto e seu estoque ficar maior que zero, ele desaparecerá automaticamente desta lista.
        </p>

        <div class="list-section">
            <div class="header-bar red-bar">
                Lista de Produtos sem Estoque (<?= count($produtosSemEstoque) ?>)
            </div>
            
            <?php if (empty($produtosSemEstoque)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Nenhum produto sem estoque</h3>
                    <p>Todos os produtos estão com estoque disponível!</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Estoque Atual</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtosSemEstoque as $prod): ?>
                            <tr>
                                <td><?= $prod['id_produto'] ?></td>
                                <td>
                                    <?php if (!empty($prod['imagem'])): ?>
                                        <img src="<?= htmlspecialchars(resolve_asset_path($prod['imagem'])) ?>" alt="Produto" class="product-image" />
                                    <?php else: ?>
                                        <span style="color: #999;">Sem imagem</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($prod['nome']) ?></td>
                                <td>
                                    <?php if (!empty($prod['categoria_nome'])): ?>
                                        <?php 
                                        // Se há múltiplas categorias (separadas por vírgula), exibe com quebras
                                        $cats = explode(', ', $prod['categoria_nome']);
                                        if (count($cats) > 1): 
                                        ?>
                                            <div style="line-height: 1.6;">
                                                <?php foreach ($cats as $index => $catNome): ?>
                                                    <span style="<?= $index === 0 ? 'font-weight: bold;' : '' ?>"><?= htmlspecialchars($catNome) ?></span><?= $index < count($cats) - 1 ? '<br>' : '' ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <?= htmlspecialchars($prod['categoria_nome']) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-sem-estoque">
                                        <?= $prod['estoque'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="produtosadm.php?edit=<?= $prod['id_produto'] ?>" class="action-edit">
                                        <i class="fas fa-edit"></i> Editar/Repor Estoque
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

