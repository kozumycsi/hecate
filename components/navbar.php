<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../service/path_helper.php';

// Carregar categorias principais dinamicamente
$principalCategories = [];
try {
    require_once __DIR__ . '/../model/CategoryModel.php';
    $catModel = new CategoryModel();
    if (method_exists($catModel, 'getCategoriesByType')) {
        $principalCategories = $catModel->getCategoriesByType('Categoria Principal');
    }
} catch (Throwable $e) {
    // Silencioso na navbar; mantém links estáticos mínimos em caso de erro
}

$favoritesCount = 0;
$cartCount = 0;
if (!empty($_SESSION['idusuario'])) {
    try {
        require_once __DIR__ . '/../model/FavoriteModel.php';
        $favoriteNavbarModel = new FavoriteModel();
        $favoritesCount = $favoriteNavbarModel->countFavorites((int)$_SESSION['idusuario']);
    } catch (Throwable $e) {
        $favoritesCount = 0;
    }
    
    try {
        require_once __DIR__ . '/../controller/CartController.php';
        $cartController = new CartController();
        $cartCount = $cartController->getCartCount();
    } catch (Throwable $e) {
        $cartCount = 0;
    }
}
?>
<!-- Top Fixed Bar -->
<div class="fixed-top-bar">
    <div class="left-side">
        <span>FRETE GRÁTIS A PARTIR DE R$150</span>
    </div>
    <div class="right-side">
        <span>ONDE ENCONTRAR</span>
        <span>RASTREAR PEDIDO</span>
        <span>CONTATO</span>
        <div class="social-icons">
            <a href="#"><i class="fab fa-whatsapp"></i></a>
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/hecatealternativa/profilecard/?igsh=MXB0dmV3bGN5NDlhag=="><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-white bg-white">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="<?= url_to('index.php') ?>">
        <img src="<?= asset_url('img/6.png') ?>" class="logo" alt="Logo Hecate" onerror="this.onerror=null; this.src='<?= asset_url('img/6.png') ?>';">
    </a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item search-nav-item">
                <button class="nav-link search-toggle" type="button" aria-label="Abrir busca" aria-expanded="false">
                    <svg class="search-toggle-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6" stroke-width="2" fill="none" stroke="currentColor"></circle>
                        <line x1="16" y1="16" x2="21" y2="21" stroke-width="2" stroke="currentColor" stroke-linecap="round"></line>
                    </svg>
                </button>
                <form class="nav-search-form twitter-search" action="<?= url_to('busca.php') ?>" method="get" role="search">
                    <label class="sr-only" for="navbar-search-input">Pesquisar produtos</label>
                    <div class="search-field">
                        <span class="fancy-bg"></span>
                        <svg class="search" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                        </svg>
                        <input id="navbar-search-input" class="input" name="q" type="search" placeholder="Buscar produtos" required>
                        <button class="close-btn" type="button" aria-label="Limpar busca">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 1 0 5.7 7.11L10.59 12l-4.9 4.89a1 1 0 0 0 1.41 1.42L12 13.41l4.89 4.9a1 1 0 0 0 1.42-1.41L13.41 12l4.9-4.89a1 1 0 0 0-.01-1.4z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </li>
            <li class="nav-item favorite-nav-item">
                <a class="nav-link" href="<?= url_to('favoritos.php') ?>" aria-label="Favoritos">
                    <i class="fas fa-heart"></i>
                    <span class="favorites-badge" data-favorites-badge <?= $favoritesCount > 0 ? '' : 'hidden' ?>><?= (int)$favoritesCount ?></span>
                </a>
            </li>
            <li class="nav-item cart-nav-item">
                <a class="nav-link" href="<?= url_to('carrinho.php') ?>" aria-label="Carrinho">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= (int)$cartCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if (!empty($_SESSION['is_admin'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= url_to('paineladm.php') ?>" aria-label="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
            </li>
            <?php endif; ?>
            <?php include __DIR__ . '/user-profile.php'; ?>
        </ul>
    </div>
</nav>

<!-- Secondary Navbar -->
<nav class="navbar secondary-nav navbar-expand-lg navbar-light">
    <div class="collapse navbar-collapse justify-content-center" id="secondaryNav">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="<?= url_to('index.php') ?>">Home</a></li>
            <?php if (!empty($principalCategories)): ?>
                <?php foreach ($principalCategories as $pc): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url_to('produtos.php') . '?categoria=' . (int)$pc['id_categoria'] ?>"><?= htmlspecialchars($pc['nome']) ?></a></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="#">Categorias</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 

<?php
if (!defined('HECATE_FAVORITES_SCRIPT_LOADED')) {
    define('HECATE_FAVORITES_SCRIPT_LOADED', true);
    $favoritesConfig = [
        'ajaxUrl' => url_to('controller/FavoriteController.php'),
        'loginUrl' => url_to('login.php'),
        'favoritesUrl' => url_to('favoritos.php'),
        'isAuthenticated' => !empty($_SESSION['idusuario']),
    ];
    ?>
    <script>
        window.HecateFavoritesConfig = <?= json_encode($favoritesConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="<?= asset_url('js/favorites.js') ?>" defer></script>
<?php } ?>

<script>
    (function () {
        var searchItem = document.querySelector('.search-nav-item');
        if (!searchItem) return;
        var toggleBtn = searchItem.querySelector('.search-toggle');
        var form = searchItem.querySelector('.nav-search-form');
        var input = searchItem.querySelector('input[type="search"]');
        var clearBtn = searchItem.querySelector('.close-btn');
        function closeSearch() {
            searchItem.classList.remove('active');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
        toggleBtn.addEventListener('click', function (event) {
            event.preventDefault();
            var isActive = searchItem.classList.toggle('active');
            toggleBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            if (isActive && input) {
                setTimeout(function () { input.focus(); }, 100);
            }
        });
        document.addEventListener('click', function (event) {
            if (!searchItem.contains(event.target)) {
                closeSearch();
            }
        });
        form.addEventListener('submit', function () {
            closeSearch();
        });
        if (clearBtn && input) {
            clearBtn.addEventListener('click', function (event) {
                event.preventDefault();
                input.value = '';
                input.focus();
            });
        }
    })();
</script>

