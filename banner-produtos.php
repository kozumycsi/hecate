<?php
session_start();
require_once __DIR__ . '/controller/BannerController.php';
require_once __DIR__ . '/model/BannerModel.php';
require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/service/path_helper.php';

$bannerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bannerId <= 0) {
    header('HTTP/1.0 404 Not Found');
    die('Banner não encontrado');
}

$bannerModel = new BannerModel();
$productModel = new ProductModel();
$banner = $bannerModel->getBannerWithRelations($bannerId);

if (!$banner) {
    header('HTTP/1.0 404 Not Found');
    die('Banner não encontrado');
}

// Apenas banners de divulgação têm produtos
if ($banner['tipo_banner'] !== 'divulgacao') {
    header('HTTP/1.0 400 Bad Request');
    die('Este banner não possui produtos associados (tipo: decoração)');
}

// Busca produtos pela categoria fantasma vinculada ao banner
$produtos = [];
$categoriaFantasma = $bannerModel->getBannerMainCategory($bannerId);
if ($categoriaFantasma) {
    // Busca produtos cadastrados na categoria fantasma do banner
    $produtos = $productModel->getProductsByCategory($categoriaFantasma['id_categoria']);
} else {
    // Fallback: usa produtos diretamente associados ao banner (sistema antigo)
    $produtos = $banner['produtos'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($banner['titulo']) ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="user-profile.css">
    <style>
        .btn-custom {
            font-size: 11px;
            padding: 4px 8px;
            margin: 2px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<?php include 'components/navbar.php'; ?>

<div class="container-fluid py-4">
    <h3 class="mb-3 text-center"><?= htmlspecialchars($banner['titulo']) ?></h3>

    <?php if (empty($produtos)): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhum produto cadastrado ainda</h4>
            <p>Este banner ainda não possui produtos cadastrados na sua categoria fantasma.</p>
            <?php if ($categoriaFantasma): ?>
                <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                    <strong>Categoria fantasma:</strong> <?= htmlspecialchars($categoriaFantasma['nome']) ?><br>
                    Para adicionar produtos, cadastre-os no dashboard e selecione esta categoria.
                </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Grid de produtos: 6 itens por linha -->
        <div class="row">
            <?php foreach ($produtos as $p): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($p['imagem'])): ?>
                            <img class="card-img-top" src="<?= htmlspecialchars(resolve_asset_path($p['imagem'])) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" style="object-fit: cover; height: 250px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 250px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #999;">
                                Sem imagem
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($p['nome']) ?></h5>
                            <p class="card-text mb-1" style="font-size:13px; color:#6c757d;"><?= htmlspecialchars($p['categoria_nome'] ?? '') ?></p>
                            <p class="card-text mb-1"><strong>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></strong></p>
                            <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">
                                em até 3x sem juros
                            </p>
                            <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">
                                Por R$ <?= number_format((float)$p['preco'] * 0.97, 2, ',', '.') ?> no PIX
                            </p>
                            <?php
                                $sizes = [];
                                if (!empty($p['tamanhos'])) {
                                    $sizes = array_map('trim', explode(',', strtoupper($p['tamanhos'])));
                                }
                            ?>
                            <?php if (!empty($sizes)): ?>
                                <?php $countSizes = count($sizes); ?>
                                <div class="mb-2 tamanhos-container" style="display: flex; flex-wrap: wrap; gap: 4px; <?= $countSizes === 1 ? 'justify-content: center;' : 'justify-content: flex-start;' ?>">
                                    <?php foreach ($sizes as $sz): ?>
                                        <?php $label = ($sz === 'UNICO' || $sz === 'ÚNICO') ? 'único' : strtolower($sz); ?>
                                        <button type="button" class="btn btn-sm btn-secondary btn-custom" style="margin: 0;"><?= $label ?></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php $detailUrl = !empty($p['id_produto']) ? url_to('pgdec.php') . '?id=' . (int)$p['id_produto'] : '#'; ?>
                            <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn btn-primary mt-auto">Ver opções</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <!-- Informações -->
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

        <!-- Ajuda e Suporte -->
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

        <!-- Atendimento ao Cliente -->
        <div class="footer-column">
            <h4>Atendimento ao cliente</h4>
            <ul>
                <li><a href="#">Contate-Nos</a></li>
                <li><a href="#">Método de Pagamento</a></li>
                <li><a href="#">Pontos Bônus</a></li>
            </ul>
        </div>

        <!-- Redes sociais e cadastro -->
        <div class="footer-column">
            <h4>Encontre-nos em</h4>
            <div class="social-icons">
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/facebook.png"/></a>
                <a href="https://www.instagram.com/hecatealternativa/profilecard/?igsh=MXB0dmV3bGN5NDlhag=="><img src="https://img.icons8.com/ios-filled/24/000000/instagram-new.png"/></a>
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/twitter.png"/></a>
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/youtube-play.png"/></a>
            </div>
            <div class="subscribe-section">
                <h4>Cadastre-se para receber noticias sobre Hecate</h4>
                <input type="email" placeholder="Endereço do Seu Email">
                <input type="text" placeholder="Conta WhatsApp">
                <button>Inscreva-se</button>
            </div>
            
            <!-- Pagamento -->
            <h4>Pagamento</h4>
            <div class="payment-icons">
                <img src="https://img.icons8.com/ios-filled/35/000000/visa.png"/>
                <img src="https://img.icons8.com/ios-filled/35/000000/mastercard.png"/>
                <img src="https://img.icons8.com/ios-filled/35/000000/paypal.png"/>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

