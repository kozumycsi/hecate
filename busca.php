<?php
session_start();

require_once __DIR__ . '/service/path_helper.php';
require_once __DIR__ . '/model/ProductModel.php';

$query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$products = [];

if ($query !== '') {
    try {
        $productModel = new ProductModel();
        $products = $productModel->searchProducts($query);
    } catch (Throwable $e) {
        $products = [];
    }
}

function search_resolve_image(?string $path): string
{
    if (function_exists('resolve_asset_path')) {
        return resolve_asset_path($path);
    }
    if (empty($path)) {
        return '';
    }
    if (preg_match('#^(?:https?:)?//#', $path)) {
        return $path;
    }
    $normalized = preg_replace('#^(\./|\.\./)+#', '', (string)$path);
    return asset_url($normalized);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
<?php include 'components/navbar.php'; ?>

<main class="container py-5">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-muted mb-1">Resultados da pesquisa</p>
            <h1 class="h4 mb-0">
                <?= $query !== '' ? htmlspecialchars($query) : 'Digite algo para buscar' ?>
            </h1>
        </div>
        <form class="search-page-form" action="busca.php" method="get">
            <input type="search" name="q" class="form-control" placeholder="Buscar produtos..." value="<?= htmlspecialchars($query) ?>" required>
            <button class="btn btn-dark ml-2" type="submit">Buscar</button>
        </form>
    </div>

    <?php if ($query === ''): ?>
        <div class="alert alert-info">Use a busca para encontrar produtos.</div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-warning">Nenhum produto encontrado para "<?= htmlspecialchars($query) ?>".</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                    <div class="card h-100 search-card">
                        <?php if (!empty($product['imagem'])): ?>
                            <img class="card-img-top" src="<?= htmlspecialchars(search_resolve_image($product['imagem'])) ?>" alt="<?= htmlspecialchars($product['nome']) ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1" style="font-size:15px;"><?= htmlspecialchars($product['nome']) ?></h5>
                            <?php if (!empty($product['categoria_nome'])): ?>
                                <p class="text-muted mb-1" style="font-size:12px;"><?= htmlspecialchars($product['categoria_nome']) ?></p>
                            <?php endif; ?>
                            <p class="mb-2"><strong>R$ <?= number_format((float)$product['preco'], 2, ',', '.') ?></strong></p>
                            <a class="btn btn-primary mt-auto" href="pgdec.php?id=<?= (int)$product['id_produto'] ?>">Ver produto</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

