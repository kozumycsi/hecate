<?php
session_start();

require_once __DIR__ . '/service/path_helper.php';
require_once __DIR__ . '/model/CategoryModel.php';
require_once __DIR__ . '/model/ProductModel.php';

$categoryModel = new CategoryModel();
$productModel = new ProductModel();

$bannerCategories = $categoryModel->getCategoriesByType('Categoria Tipo Banner');

$requestedCategoryId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$requestedSlug = isset($_GET['slug']) ? strtolower(trim($_GET['slug'])) : null;
$requestedSlot = isset($_GET['slot']) ? (int)$_GET['slot'] : null;

if (isset($forcedBannerSlot) && $forcedBannerSlot) {
    $requestedSlot = (int)$forcedBannerSlot;
}

$slugify = function(?string $value): string {
    $value = strtolower(trim((string)$value));
    $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
};

$targetCategory = null;

if (!empty($bannerCategories)) {
    if ($requestedCategoryId) {
        foreach ($bannerCategories as $cat) {
            if ((int)$cat['id_categoria'] === $requestedCategoryId) {
                $targetCategory = $cat;
                break;
            }
        }
    }

    if (!$targetCategory && $requestedSlug) {
        foreach ($bannerCategories as $cat) {
            if ($slugify($cat['nome'] ?? '') === $requestedSlug) {
                $targetCategory = $cat;
                break;
            }
        }
    }

    if (!$targetCategory && $requestedSlot) {
        $index = max(0, $requestedSlot - 1);
        if (isset($bannerCategories[$index])) {
            $targetCategory = $bannerCategories[$index];
        }
    }

    if (!$targetCategory) {
        $targetCategory = $bannerCategories[0];
    }
}

$products = [];
$categoryName = 'Produtos';
$categoryDescription = '';

if ($targetCategory) {
    $categoryName = $targetCategory['nome'] ?? $categoryName;
    if (!empty($targetCategory['descricao'])) {
        $categoryDescription = $targetCategory['descricao'];
    }
    $rawProducts = $productModel->getProductsByBannerCategory((int)$targetCategory['id_categoria'], 60);
    $products = array_values(array_filter($rawProducts, function ($product) {
        $price = isset($product['preco']) ? (float)$product['preco'] : 0;
        $name = strtolower($product['nome'] ?? '');
        $description = trim($product['descricao'] ?? '');
        $looksLikeBannerAsset = ($price <= 0.1 && strpos($name, 'banner') === 0)
            || preg_match('#^https?://#i', $description)
            || stripos($description, 'banner decorativo') !== false;
        return !$looksLikeBannerAsset;
    }));
} else {
    $categoryName = 'Banners especiais';
}

function formatPrice($value): string {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categoryName) ?> - Hécate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="user-profile.css">
    <style>
        .banner-products-header {
            margin-top: 30px;
            text-align: center;
        }
        .banner-products-header h1 {
            font-size: 2.5rem;
            text-transform: capitalize;
        }
        .banner-products-header p {
            color: #6c757d;
            max-width: 720px;
            margin: 10px auto 0;
        }
        .banner-product-slider {
            gap: 16px;
            overflow: hidden;
        }
        .banner-slider-prev,
        .banner-slider-next {
            position: absolute;
            top: 40%;
            transform: translateY(-50%);
            z-index: 2;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.9);
        }
        .banner-slider-prev { left: 15px; }
        .banner-slider-next { right: 15px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<div class="container-fluid py-4">
    <div class="banner-products-header">
        <h1><?= htmlspecialchars($categoryName) ?></h1>
        <?php if (!empty($categoryDescription)): ?>
            <p><?= htmlspecialchars($categoryDescription) ?></p>
        <?php endif; ?>
        <?php if ($targetCategory): ?>
            <small style="color:#999;">
                Vincule produtos a esta coleção marcando a categoria
                "<?= htmlspecialchars($categoryName) ?>" em Produtos &gt; Categorias de Banner.
            </small>
        <?php endif; ?>
    </div>

    <?php if (!$targetCategory): ?>
        <div class="alert alert-warning text-center mt-4">
            Nenhuma categoria do tipo banner foi encontrada. Crie categorias em
            <strong>Admin &gt; Categorias</strong> marcando o tipo "Categoria Tipo Banner".
        </div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-info text-center mt-4">
            Nenhum produto vinculado a esta categoria especial ainda.
            Cadastre ou edite um produto e marque a categoria de banner correspondente.
        </div>
    <?php else: ?>
        <div class="position-relative mt-4">
            <button class="btn btn-light border banner-slider-prev" aria-label="Anterior"><span>&lt;</span></button>
            <button class="btn btn-light border banner-slider-next" aria-label="Próximo"><span>&gt;</span></button>
            <div class="banner-product-slider d-flex" data-slider="banner">
                <?php foreach ($products as $product): ?>
                    <div class="flex-shrink-0" style="width: calc(20% - 16px); min-width: 240px;">
                        <div class="card h-100">
                            <?php if (!empty($product['imagem'])): ?>
                                <img class="card-img-top" src="<?= htmlspecialchars(resolve_asset_path($product['imagem'])) ?>" alt="<?= htmlspecialchars($product['nome']) ?>">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($product['nome']) ?></h5>
                                <?php if (!empty($product['descricao'])): ?>
                                    <p class="card-text mb-1" style="font-size:13px; color:#6c757d;">
                                        <?= htmlspecialchars(mb_substr(strip_tags($product['descricao']), 0, 80)) ?><?= mb_strlen(strip_tags($product['descricao'])) > 80 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text mb-1"><strong><?= formatPrice($product['preco']) ?></strong></p>
                                <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">
                                    em até 3x sem juros
                                </p>
                                <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">
                                    Por <?= formatPrice($product['preco'] * 0.97) ?> no PIX
                                </p>
                                <?php
                                    $sizes = [];
                                    if (!empty($product['tamanhos'])) {
                                        $sizes = array_map('trim', explode(',', strtoupper($product['tamanhos'])));
                                    }
                                ?>
                                <?php if (!empty($sizes)): ?>
                                    <div class="mb-2">
                                        <?php foreach ($sizes as $size): ?>
                                            <?php $label = ($size === 'UNICO' || $size === 'ÚNICO') ? 'único' : strtolower($size); ?>
                                            <button type="button" class="btn btn-sm btn-secondary btn-custom"><?= $label ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php $detailUrl = !empty($product['id_produto']) ? 'pgdec.php?id=' . (int)$product['id_produto'] : '#'; ?>
                                <a href="<?= $detailUrl ?>" class="btn btn-primary mt-auto">Ver opções</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    (function() {
        var slider = document.querySelector('.banner-product-slider');
        if (!slider) return;
        var cards = slider.querySelectorAll('.card');
        var prev = document.querySelector('.banner-slider-prev');
        var next = document.querySelector('.banner-slider-next');
        var visible = 5;
        var index = 0;

        function updateButtons() {
            var maxIndex = Math.max(0, cards.length - visible);
            if (prev) prev.style.visibility = index > 0 ? 'visible' : 'hidden';
            if (next) next.style.visibility = index < maxIndex ? 'visible' : 'hidden';
        }

        function slideTo(newIndex) {
            index = Math.max(0, Math.min(newIndex, Math.max(0, cards.length - visible)));
            var cardWidth = cards[0] ? cards[0].getBoundingClientRect().width + 16 : 0;
            slider.scrollTo({ left: index * cardWidth, behavior: 'smooth' });
            updateButtons();
        }

        if (cards.length <= visible) {
            if (prev) prev.style.display = 'none';
            if (next) next.style.display = 'none';
        } else {
            updateButtons();
            if (prev) prev.addEventListener('click', function() { slideTo(index - 1); });
            if (next) next.addEventListener('click', function() { slideTo(index + 1); });
            window.addEventListener('resize', updateButtons);
        }
    })();
</script>
</body>
</html>

