<?php
session_start();

if (empty($_SESSION['idusuario'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/controller/CartController.php';
require_once __DIR__ . '/service/path_helper.php';

$cartController = new CartController();
$cartItems = $cartController->getCartItems();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hecate - Roupas Alternativas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="carrinho.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div id="conteudoCarrinho">
        <?php if (empty($cartItems)): ?>
            <div style="text-align: center; padding: 40px;">
                <p>O carrinho está vazio.</p>
            </div>
        <?php else: ?>
            <?php foreach ($cartItems as $index => $item): ?>
                <div class="carrinho-item" data-item-id="<?= (int)$item['id'] ?>">
                    <input type="checkbox" class="checkbox" id="checkbox<?= $index ?>" onclick="atualizarTotal()" checked>
                    <div class="produto-info">
                        <img src="<?= htmlspecialchars(!empty($item['imagem']) ? resolve_asset_path($item['imagem']) : asset_url('img/logo.png')) ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="imagem-carrinho" />
                        <div>
                            <p><strong><?= htmlspecialchars($item['nome']) ?></strong></p>
                            <p>Preço: R$ <?= number_format((float)$item['preco'], 2, ',', '.') ?></p>
                            <?php 
                            $variacoes = [];
                            if (!empty($item['tamanho'])) $variacoes[] = 'Tamanho: ' . htmlspecialchars($item['tamanho']);
                            if (!empty($item['cor'])) $variacoes[] = 'Cor: ' . htmlspecialchars($item['cor']);
                            if (!empty($variacoes)): ?>
                                <p class="variacoes"><?= implode(' | ', $variacoes) ?></p>
                            <?php endif; ?>
                            <div class="quantity-controls">
                                <button onclick="alterarQuantidade(<?= (int)$item['id'] ?>, -1, <?= (int)$item['estoque'] ?>)">-</button>
                                <input type="text" value="<?= (int)$item['quantidade'] ?>" readonly />
                                <button onclick="alterarQuantidade(<?= (int)$item['id'] ?>, 1, <?= (int)$item['estoque'] ?>)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="barraFixa">
        <div class="selecao">
            <input type="checkbox" id="selecionarTudo" onclick="selecionarTodosItens()">
            <label for="selecionarTudo"><strong>Selecionar tudo</strong></label>
            <button onclick="excluirSelecionados()">Excluir</button>
        </div>
        <div class="resumo">
            <p>Total (<span id="quantidadeItens">0</span> Itens): R$ <span id="totalBarra">0,00</span></p>
            <button onclick="finalizarCompra()">Continuar</button>
        </div>
    </div>

    <script>
        const cartItems = <?= json_encode($cartItems) ?>;
    </script>
    <script src="carrinho.js"></script>
</body>
</html> 