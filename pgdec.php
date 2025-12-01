<?php
session_start();

require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/model/CategoryModel.php';
require_once __DIR__ . '/model/FavoriteModel.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$categoryName = '';
$relatedProducts = [];
$isFavorite = false;

try {
    $productModel = new ProductModel();
    if ($productId > 0) {
        $product = $productModel->getProductById($productId);
    }

    if ($product && !empty($product['categoria'])) {
        $categoryModel = new CategoryModel();
        $category = $categoryModel->getCategoryById((int)$product['categoria']);
        if (!empty($category['nome'])) {
            $categoryName = $category['nome'];
        }

        $related = $productModel->getProductsByCategory((int)$product['categoria']);
        if (!empty($related)) {
            foreach ($related as $candidate) {
                if ((int)$candidate['id_produto'] === $productId) {
                    continue;
                }
                $relatedProducts[] = $candidate;
                if (count($relatedProducts) >= 10) {
                    break;
                }
            }
        }
    }
    if (!empty($_SESSION['idusuario']) && $product) {
        $favoriteModel = new FavoriteModel();
        $isFavorite = $favoriteModel->isFavorite((int)$_SESSION['idusuario'], (int)$product['id_produto']);
    }
} catch (Throwable $e) {
    $product = null;
    $isFavorite = false;
}

if (!$product) {
    header('Location: produtos.php');
    exit();
}

require_once __DIR__ . '/service/path_helper.php';
$imagePath = !empty($product['imagem']) ? resolve_asset_path($product['imagem']) : asset_url('img/logo.png');
$price = number_format((float)$product['preco'], 2, ',', '.');
$pixPrice = number_format((float)$product['preco'] * 0.97, 2, ',', '.');
$sizesRaw = $product['tamanhos'] ?? '';
$sizes = [];
if (!empty($sizesRaw)) {
    $sizes = array_values(array_filter(array_map('trim', explode(',', $sizesRaw))));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['nome']) ?> | Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="user-profile.css">
    <link rel="stylesheet" type="text/css" href="pgdec.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>

<main class="produto-page">
    <section class="produto-container">
        <div class="imagens-produto">
            <div class="imagem-principal">
                <img id="imagem-atual" src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['nome']) ?>">
            </div>
        </div>

        <div class="detalhes-produto"
             data-product-id="<?= (int)$product['id_produto'] ?>"
             data-product-name="<?= htmlspecialchars($product['nome']) ?>"
             data-product-price="<?= htmlspecialchars($product['preco']) ?>"
             data-product-image="<?= htmlspecialchars($imagePath) ?>"
             data-product-estoque="<?= (int)($product['estoque'] ?? 0) ?>">
            <h1><?= htmlspecialchars($product['nome']) ?></h1>
            <p>ID: <?= (int)$product['id_produto'] ?></p>
            <?php if ($categoryName): ?>
                <p>Categoria: <?= htmlspecialchars($categoryName) ?></p>
            <?php endif; ?>
            <p class="preco">R$ <?= $price ?></p>
            <p>Por R$ <?= $pixPrice ?> no PIX</p>
            <?php if (!empty($product['descricao'])): ?>
                <p><?= nl2br(htmlspecialchars($product['descricao'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($sizes)): ?>
                <div class="escolha-tamanho">
                    <p>Tamanho:</p>
                    <div class="tamanhos">
                        <?php foreach ($sizes as $size): ?>
                            <?php $label = strtoupper($size); ?>
                            <button type="button" data-size="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <p id="tamanho-selecionado">Tamanho selecionado: <span>Nenhum</span></p>
                </div>
            <?php else: ?>
                <p id="tamanho-selecionado">Tamanho selecionado: <span>Único</span></p>
            <?php endif; ?>

            <div class="acoes-produto">
                <button id="btn-adicionar" class="adicionar-carrinho" <?= !empty($sizes) ? 'disabled' : '' ?>>
                    <?= !empty($sizes) ? 'Selecione um tamanho' : 'Adicionar ao carrinho' ?>
                </button>
                <button
                    class="favoritos"
                    type="button"
                    data-favorite-button
                    data-product-id="<?= (int)$product['id_produto'] ?>"
                    data-is-favorite="<?= $isFavorite ? '1' : '0' ?>"
                    aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>"
                    aria-label="<?= $isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>"
                >
                    <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                </button>
            </div>
        </div>
    </section>

    <?php if (!empty($relatedProducts)): ?>
        <section class="produtos-visitados">
            <h2>Clientes também viram</h2>
            <div class="grid-produtos">
                <?php foreach ($relatedProducts as $item): ?>
                    <a class="produto" href="pgdec.php?id=<?= (int)$item['id_produto'] ?>">
                        <?php if (!empty($item['imagem'])): ?>
                            <img src="<?= htmlspecialchars(resolve_asset_path($item['imagem'])) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                        <?php endif; ?>
                        <p><?= htmlspecialchars($item['nome']) ?></p>
                        <p>R$ <?= number_format((float)$item['preco'], 2, ',', '.') ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<div class="footer-container">
    <div class="footer-column">
        <h4>Informações da loja</h4>
        <ul>
            <li><a href="#">Hecate no Remessa</a></li>
            <li><a href="#">Conforme</a></li>
            <li><a href="#">Sobre Hecate</a></li>
            <li><a href="#">Venda na Hecate</a></li>
            <li><a href="#">Blogueiros de moda</a></li>
            <li><a href="#">Carreiras</a></li>
            <li><a href="#">Sala de Imprensa</a></li>
        </ul>
    </div>

    <div class="footer-column">
        <h4>Ajuda e suporte</h4>
        <ul>
            <li><a href="#">Política de Frete</a></li>
            <li><a href="#">Devolução</a></li>
            <li><a href="#">Reembolso</a></li>
            <li><a href="#">Como Pedir</a></li>
            <li><a href="#">Como Rastrear</a></li>
            <li><a href="#">Guia de Tamanhos</a></li>
            <li><a href="#">Hecate VIP</a></li>
        </ul>
    </div>

    <div class="footer-column">
        <h4>Atendimento ao cliente</h4>
        <ul>
            <li><a href="#">Contate-Nos</a></li>
            <li><a href="#">Método de Pagamento</a></li>
            <li><a href="#">Pontos Bônus</a></li>
        </ul>
    </div>

    <div class="footer-column">
        <h4>Encontre-nos em</h4>
        <div class="social-icons">
            <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/facebook.png" alt="Facebook"/></a>
            <a href="https://www.instagram.com/hecatealternativa/profilecard/?igsh=MXB0dmV3bGN5NDlhag=="><img src="https://img.icons8.com/ios-filled/24/000000/instagram-new.png" alt="Instagram"/></a>
            <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/twitter.png" alt="Twitter"/></a>
            <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/youtube-play.png" alt="YouTube"/></a>
        </div>
        <div class="subscribe-section">
            <h4>Cadastre-se para receber notícias sobre Hecate</h4>
            <input type="email" placeholder="Endereço do Seu Email">
            <input type="text" placeholder="Conta WhatsApp">
            <button>Inscreva-se</button>
        </div>
        <h4>Pagamento</h4>
        <div class="payment-icons">
            <img src="https://img.icons8.com/ios-filled/35/000000/visa.png" alt="Visa"/>
            <img src="https://img.icons8.com/ios-filled/35/000000/mastercard.png" alt="Mastercard"/>
            <img src="https://img.icons8.com/ios-filled/35/000000/paypal.png" alt="Paypal"/>
        </div>
    </div>
</div>

<script>
(function() {
    const detalhes = document.querySelector('.detalhes-produto');
    const addBtn = document.getElementById('btn-adicionar');
    const sizeButtons = document.querySelectorAll('.tamanhos button[data-size]');
    const sizeLabel = document.querySelector('#tamanho-selecionado span');
    let selectedSize = sizeButtons.length ? null : 'Único';

    sizeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            sizeButtons.forEach(btn => btn.classList.remove('selecionado'));
            button.classList.add('selecionado');
            selectedSize = button.dataset.size;
            if (sizeLabel) {
                sizeLabel.textContent = selectedSize;
            }
            updateButtonState();
        });
    });

    function updateButtonState() {
        if (!addBtn) return;
        if (!sizeButtons.length || selectedSize) {
            addBtn.disabled = false;
            addBtn.textContent = 'Adicionar ao carrinho';
        } else {
            addBtn.disabled = true;
            addBtn.textContent = 'Selecione um tamanho';
        }
    }

    if (addBtn && detalhes) {
        addBtn.addEventListener('click', function() {
            if (addBtn.disabled) {
                return;
            }

            const productId = detalhes.dataset.productId;
            const tamanho = selectedSize || 'Único';

            // Adiciona via AJAX
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('tamanho', tamanho);

            fetch('controller/CartController.php?action=add_item', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produto adicionado ao carrinho!');
                } else {
                    alert(data.message || 'Erro ao adicionar produto ao carrinho.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao adicionar produto ao carrinho.');
            });
        });
    }

    updateButtonState();
})();
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializa todos os dropdowns
        $('.dropdown-toggle').dropdown();
        
        // Garante que o dropdown permaneça aberto ao clicar dentro dele
        $('.dropdown-menu').click(function(e) {
            e.stopPropagation();
        });
    });
</script>
</body>
</html>

