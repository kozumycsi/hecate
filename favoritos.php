<?php
session_start();

if (empty($_SESSION['idusuario'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/service/path_helper.php';
require_once __DIR__ . '/model/FavoriteModel.php';

$favoriteModel = new FavoriteModel();
$favorites = $favoriteModel->getFavoritesWithProducts((int)$_SESSION['idusuario']);

function favorite_image_url(?string $path): string {
    if (function_exists('resolve_asset_path')) {
        return resolve_asset_path($path);
    }
    if (empty($path)) {
        return '';
    }
    if (preg_match('#^(?:https?:)?//#', $path)) {
        return $path;
    }
    $normalized = preg_replace('#^(\./|\.\./)+#', '', $path);
    return asset_url($normalized);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Favoritos - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>

<main class="favorites-page container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <p class="text-muted mb-1">Meus favoritos</p>
            <h1 class="h3 mb-0"><?= count($favorites) ?> produto<?= count($favorites) === 1 ? '' : 's' ?></h1>
        </div>
        <a class="btn btn-outline-secondary" href="produtos.php">Continuar comprando</a>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="favorites-empty text-center py-5">
            <div class="mb-3">
                <i class="far fa-heart fa-3x text-muted"></i>
            </div>
            <h2 class="h4 mb-2">Você ainda não favoritou nenhum item</h2>
            <p class="text-muted mb-4">Toque no coração dos produtos para salvá-los aqui.</p>
            <a class="btn btn-primary" href="produtos.php">Ver produtos</a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $fav): ?>
                <article class="favorite-card" data-product-id="<?= (int)$fav['product_id'] ?>">
                    <button
                        class="favoritos favorite-card__button"
                        type="button"
                        data-favorite-button
                        data-product-id="<?= (int)$fav['product_id'] ?>"
                        data-is-favorite="1"
                        data-remove-target=".favorite-card"
                        aria-pressed="true"
                        aria-label="Remover dos favoritos"
                    >
                        <i class="fas fa-heart"></i>
                    </button>

                    <a class="favorite-card__link" href="pgdec.php?id=<?= (int)$fav['product_id'] ?>">
                        <?php if (!empty($fav['imagem'])): ?>
                            <img src="<?= htmlspecialchars(favorite_image_url($fav['imagem'])) ?>" alt="<?= htmlspecialchars($fav['nome'] ?? 'Produto favorito') ?>">
                        <?php endif; ?>
                        <div class="favorite-card__body">
                            <h3><?= htmlspecialchars($fav['nome'] ?? 'Produto') ?></h3>
                            <p class="favorite-card__price">R$ <?= number_format((float)($fav['preco'] ?? 0), 2, ',', '.') ?></p>
                            <?php if (!empty($fav['descricao'])): ?>
                                <p class="favorite-card__description"><?= htmlspecialchars(mb_strimwidth(strip_tags($fav['descricao']), 0, 90, '...')) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

