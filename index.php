<?php
// Ativar exibição de erros para debug (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/service/path_helper.php';
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
    <link rel="stylesheet" type="text/css" href="<?= asset_url('style.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('user-profile.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <?php
    if (!function_exists('resolveAssetPath')) {
        function resolveAssetPath(?string $path): string {
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
    }

    // Carrega subcategorias para a vitrine de marketing
    $subcategorias = [];
    $bannerCategorias = [];
    $bannerModel = null;
    try {
        require_once __DIR__ . '/model/CategoryModel.php';
        require_once __DIR__ . '/model/ProductModel.php';
        require_once __DIR__ . '/model/BannerModel.php';
        $catModel = new CategoryModel();
        $prodModel = new ProductModel();
        $bannerModel = new BannerModel();
        if (method_exists($catModel, 'getCategoriesByType')) {
            $subcategorias = $catModel->getCategoriesByType('Subcategoria');
            $bannerCategorias = $catModel->getCategoriesByType('Categoria Tipo Banner');
        }
    } catch (Throwable $e) {
        $subcategorias = [];
        $bannerCategorias = [];
        $bannerModel = null;
    }
    ?>

    <!-- Carousel - Banners de Divulgação -->
    <?php
        // Busca banners de divulgação ativos usando o novo sistema
        $bannersDivulgacao = [];
        try {
            if (isset($bannerModel)) {
                $allBanners = $bannerModel->getActiveBanners('divulgacao');
                // Filtra apenas banners com imagem válida
                foreach ($allBanners as $banner) {
                    if (!empty($banner['imagem']) && $banner['ativo'] == 1) {
                        $bannersDivulgacao[] = $banner;
                    }
                }
            }
        } catch (Throwable $e) {
            $bannersDivulgacao = [];
        }
    ?>
    <section class="banner-carousel-wrapper">
        <div class="container-fluid px-0">
            <div id="bannerCarousel" class="carousel slide banner-carousel" data-ride="carousel" data-interval="6000">
                <?php if (!empty($bannersDivulgacao)): ?>
                    <?php if (count($bannersDivulgacao) > 1): ?>
                        <ol class="carousel-indicators">
                            <?php foreach ($bannersDivulgacao as $idx => $banner): ?>
                                <li data-target="#bannerCarousel" data-slide-to="<?= $idx ?>" <?= $idx === 0 ? 'class="active"' : '' ?>></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                    <div class="carousel-inner">
                        <?php foreach ($bannersDivulgacao as $idx => $banner): ?>
                            <?php 
                                // Para banners de divulgação, sempre cria link para página de produtos do banner
                                $url = '#';
                                if ($banner['tipo_banner'] === 'divulgacao') {
                                    // Sempre cria link para a página do banner (mesmo sem produtos ainda)
                                        $url = url_to('banner-produtos.php?id=' . $banner['id_banner']);
                                }
                                $label = !empty($banner['titulo']) ? $banner['titulo'] : 'Banner de divulgação';
                                $imgTag = '<img src="' . htmlspecialchars(resolveAssetPath($banner['imagem'])) . '" alt="' . htmlspecialchars($label) . '" class="d-block w-100 banner-carousel-img">';
                            ?>
                            <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                <?php if ($url !== '#'): ?>
                                    <a href="<?= htmlspecialchars($url) ?>" aria-label="<?= htmlspecialchars($label) ?>" style="display: block; cursor: pointer;">
                                        <?= $imgTag ?>
                                    </a>
                                <?php else: ?>
                                    <?= $imgTag ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($bannersDivulgacao) > 1): ?>
                        <a class="carousel-control-prev" href="#bannerCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#bannerCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Próximo</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Nenhum banner cadastrado - exibe mensagem ou deixa vazio -->
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div style="background-color: #f8f9fa; height: 400px; display: flex; align-items: center; justify-content: center;">
                                <p style="color: #6c757d; font-size: 18px;">Nenhum banner cadastrado</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    

    <!-- Subcategorias removidas da página inicial -->

    <!-- Novidades (produtos com flag recem_adicionado) -->
    <?php
        $recent = [];
        try {
            // Busca produtos recém adicionados usando o novo sistema
            if (method_exists($prodModel, 'getNovidades')) {
                $recent = $prodModel->getNovidades(12);
                // Debug temporário - descomente para ver no log
                if (empty($recent)) {
                    error_log("DEBUG Novidades: Nenhum produto encontrado. Verifique se há produtos com recem_adicionado=1 e estoque>0");
                } else {
                    error_log("DEBUG Novidades: " . count($recent) . " produtos encontrados");
                }
            } else {
                // Fallback: busca por categoria se o método não existir
                $novidadesCatId = null;
                foreach ($subcategorias as $catTmp) {
                    if (isset($catTmp['nome']) && mb_strtolower(trim($catTmp['nome'])) === 'novidades') {
                        $novidadesCatId = (int)$catTmp['id_categoria'];
                        break;
                    }
                }
                if ($novidadesCatId !== null) {
                    $recent = $prodModel->getProductsByCategory($novidadesCatId);
                    if (!empty($recent)) {
                        $recent = array_slice($recent, 0, 18);
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("Erro ao buscar novidades: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            $recent = [];
        }
    ?>
    <?php if (!empty($recent)): ?>
    <section class="py-4 bg-light">
        <div class="container-fluid position-relative">
            <h2 class="text-center">novidades</h2>
            <button class="btn btn-light border home-prev" data-target="novidades" aria-label="Anterior" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&lt;</span></button>
            <button class="btn btn-light border home-next" data-target="novidades" aria-label="Próximo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&gt;</span></button>
            <div class="home-slider d-flex" data-slider="novidades" style="gap: 16px; overflow: hidden;">
                <?php foreach ($recent as $p): ?>
                    <div class="flex-shrink-0" style="width: calc(16.666% - 16px); min-width: 220px;">
                        <div class="card h-100">
                <?php if (!empty($p['imagem'])): ?>
                    <img class="card-img-top" src="<?= htmlspecialchars(resolveAssetPath($p['imagem'])) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($p['nome']) ?></h5>
                                <p class="card-text mb-1" style="font-size:13px; color:#6c757d;"><?= htmlspecialchars($p['categoria_nome'] ?? '') ?></p>
                                <p class="card-text mb-1"><strong>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></strong></p>
                                <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">em até 3x sem juros</p>
                                <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">Por R$ <?= number_format((float)$p['preco'] * 0.97, 2, ',', '.') ?> no PIX</p>
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
                                <a href="<?= $detailUrl ?>" class="btn btn-primary mt-auto">Ver opções</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Voltaram (produtos com estoque reposto recentemente) -->
    <?php
        $voltaram = [];
        try {
            if (method_exists($prodModel, 'getVoltaram')) {
                $voltaram = $prodModel->getVoltaram(7, 18); // Últimos 7 dias, máximo 18 produtos
            } else {
                // Fallback: busca por categoria se o método não existir
                foreach ($subcategorias as $catTmp) {
                    if (isset($catTmp['nome']) && mb_strtolower(trim($catTmp['nome'])) === 'voltaram') {
                        $voltaram = $prodModel->getProductsByCategory((int)$catTmp['id_categoria']);
                        if (!empty($voltaram)) {
                            $voltaram = array_slice($voltaram, 0, 18);
                        }
                        break;
                    }
                }
            }
        } catch (Throwable $e) {
            $voltaram = [];
        }
    ?>
    <?php if (!empty($voltaram)): ?>
    <section class="py-4 bg-light">
        <div class="container-fluid position-relative">
            <h2 class="text-center">voltaram</h2>
            <button class="btn btn-light border home-prev" data-target="voltaram" aria-label="Anterior" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&lt;</span></button>
            <button class="btn btn-light border home-next" data-target="voltaram" aria-label="Próximo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&gt;</span></button>
            <div class="home-slider d-flex" data-slider="voltaram" style="gap: 16px; overflow: hidden;">
                <?php foreach ($voltaram as $p): ?>
                    <div class="flex-shrink-0" style="width: calc(16.666% - 16px); min-width: 220px;">
                        <div class="card h-100">
                <?php if (!empty($p['imagem'])): ?>
                    <img class="card-img-top" src="<?= htmlspecialchars(resolveAssetPath($p['imagem'])) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($p['nome']) ?></h5>
                                <p class="card-text mb-1" style="font-size:13px; color:#6c757d;"><?= htmlspecialchars($p['categoria_nome'] ?? '') ?></p>
                                <p class="card-text mb-1"><strong>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></strong></p>
                                <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">em até 3x sem juros</p>
                                <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">Por R$ <?= number_format((float)$p['preco'] * 0.97, 2, ',', '.') ?> no PIX</p>
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
                                <a href="<?= $detailUrl ?>" class="btn btn-primary mt-auto">Ver opções</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php
    // Exibe banners decorativos logo abaixo da seção "voltaram"
    $bannersDecorativos = [];
    try {
        if (isset($bannerModel)) {
            $allDecorativos = $bannerModel->getActiveBanners('decoracao');
            foreach ($allDecorativos as $banner) {
                if (!empty($banner['imagem']) && $banner['ativo'] == 1) {
                    $bannersDecorativos[] = $banner;
                }
            }
        }
    } catch (Throwable $e) {
        $bannersDecorativos = [];
    }
    
    if (!empty($bannersDecorativos)):
    ?>
    <!-- Banners Decorativos abaixo da seção "voltaram" -->
    <section class="py-4" style="background-color: #fff;">
        <div class="container-fluid">
            <div class="banner-container" style="justify-content: center;">
                <?php foreach ($bannersDecorativos as $banner): ?>
                    <?php if (!empty($banner['imagem'])): ?>
                        <div class="banner-full-width" style="background-image: url('<?= htmlspecialchars(resolveAssetPath($banner['imagem'])) ?>'); cursor: default; pointer-events: none;"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Mais Vendidos (produtos ordenados por total de vendas) -->
    <?php
        $maisVendidos = [];
        try {
            if (method_exists($prodModel, 'getMaisVendidos')) {
                $maisVendidos = $prodModel->getMaisVendidos(18);
            }
        } catch (Throwable $e) {
            $maisVendidos = [];
        }
    ?>
    <?php if (!empty($maisVendidos)): ?>
    <section class="py-4 bg-light">
        <div class="container-fluid position-relative">
            <h2 class="text-center">mais vendidos</h2>
            <button class="btn btn-light border home-prev" data-target="mais-vendidos" aria-label="Anterior" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&lt;</span></button>
            <button class="btn btn-light border home-next" data-target="mais-vendidos" aria-label="Próximo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&gt;</span></button>
            <div class="home-slider d-flex" data-slider="mais-vendidos" style="gap: 16px; overflow: hidden;">
                <?php foreach ($maisVendidos as $p): ?>
                    <div class="flex-shrink-0" style="width: calc(16.666% - 16px); min-width: 220px;">
                        <div class="card h-100">
                <?php if (!empty($p['imagem'])): ?>
                    <img class="card-img-top" src="<?= htmlspecialchars(resolveAssetPath($p['imagem'])) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($p['nome']) ?></h5>
                                <p class="card-text mb-1" style="font-size:13px; color:#6c757d;"><?= htmlspecialchars($p['categoria_nome'] ?? '') ?></p>
                                <p class="card-text mb-1"><strong>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></strong></p>
                                <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">em até 3x sem juros</p>
                                <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">Por R$ <?= number_format((float)$p['preco'] * 0.97, 2, ',', '.') ?> no PIX</p>
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
                                <a href="<?= $detailUrl ?>" class="btn btn-primary mt-auto">Ver opções</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Seções por Subcategoria (outras subcategorias, excluindo Novidades, Voltaram e Mais Vendidos) -->
    <?php if (!empty($subcategorias)): ?>
        <?php foreach ($subcategorias as $sc): ?>
            <?php
                // Evita duplicar seções automáticas
                $nomeLower = mb_strtolower(trim($sc['nome'] ?? ''));
                if (in_array($nomeLower, ['novidades', 'voltaram', 'mais vendidos'])) {
                    continue;
                }
                $prodsSub = [];
                try {
                    $prodsSub = $prodModel->getProductsByCategory((int)$sc['id_categoria']);
                } catch (Throwable $e) {
                    $prodsSub = [];
                }
                if (!empty($prodsSub)) {
                    // limita para manter o carrossel leve
                    $prodsSub = array_slice($prodsSub, 0, 18);
                }
                if (empty($prodsSub)) continue;
                $sliderId = 'subcat-' . (int)$sc['id_categoria'];
            ?>
            <section class="py-4 bg-light">
                <div class="container-fluid position-relative">
                    <h2 class="text-center"><?= htmlspecialchars($sc['nome']) ?></h2>
                    <button class="btn btn-light border home-prev" data-target="<?= $sliderId ?>" aria-label="Anterior" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&lt;</span></button>
                    <button class="btn btn-light border home-next" data-target="<?= $sliderId ?>" aria-label="Próximo" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);z-index:2;"><span>&gt;</span></button>
                    <div class="home-slider d-flex" data-slider="<?= $sliderId ?>" style="gap: 16px; overflow: hidden;">
                        <?php foreach ($prodsSub as $p): ?>
                            <div class="flex-shrink-0" style="width: calc(16.666% - 16px); min-width: 220px;">
                                <div class="card h-100">
                <?php if (!empty($p['imagem'])): ?>
                    <img class="card-img-top" src="<?= htmlspecialchars(resolveAssetPath($p['imagem'])) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-1" style="font-size:16px;"><?= htmlspecialchars($p['nome']) ?></h5>
                                        <p class="card-text mb-1" style="font-size:13px; color:#6c757d;"><?= htmlspecialchars($p['categoria_nome'] ?? '') ?></p>
                                        <p class="card-text mb-1"><strong>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></strong></p>
                                        <p class="card-text mb-1" style="font-size:12px; color:#6c757d;">em até 3x sem juros</p>
                                        <p class="card-text mb-2" style="font-size:12px; color:#6c757d;">Por R$ <?= number_format((float)$p['preco'] * 0.97, 2, ',', '.') ?> no PIX</p>
                                        <?php
                                            $sizes = [];
                                            if (!empty($p['tamanhos'])) {
                                                $sizes = array_map('trim', explode(',', strtoupper($p['tamanhos'])));
                                            }
                                        ?>
                                        <?php if (!empty($sizes)): ?>
                                            <div class="mb-2">
                                                <?php foreach ($sizes as $sz): ?>
                                                    <?php $label = ($sz === 'UNICO' || $sz === 'ÚNICO') ? 'único' : strtolower($sz); ?>
                                                    <button type="button" class="btn btn-sm btn-secondary btn-custom"><?= $label ?></button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                <?php $detailUrl = !empty($p['id_produto']) ? url_to('pgdec.php') . '?id=' . (int)$p['id_produto'] : '#'; ?>
                                        <a href="<?= $detailUrl ?>" class="btn btn-primary mt-auto">Ver opções</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

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
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/instagram-new.png"/></a>
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
    <script>
        $(document).ready(function() {
            // Inicializa todos os dropdowns
            $('.dropdown-toggle').dropdown();
            
            // Garante que o dropdown permaneça aberto ao clicar dentro dele
            $('.dropdown-menu').click(function(e) {
                e.stopPropagation();
            });
        });

        (function() {
            var slider = document.querySelector('.sub-slider');
            if (!slider) return;
            var cards = slider.children;
            var prev = document.querySelector('.sub-prev');
            var next = document.querySelector('.sub-next');
            var visible = 6;
            var index = 0;
            function updateButtons() {
                var maxIndex = Math.max(0, cards.length - visible);
                prev.style.visibility = index > 0 ? 'visible' : 'hidden';
                next.style.visibility = index < maxIndex ? 'visible' : 'hidden';
            }
            function slideTo(n) {
                index = Math.max(0, Math.min(n, Math.max(0, cards.length - visible)));
                var cardWidth = cards[0] ? cards[0].getBoundingClientRect().width + 12 : 0;
                slider.scrollTo({ left: index * cardWidth, behavior: 'smooth' });
                updateButtons();
            }
            if (cards.length <= visible) {
                if (prev) prev.style.display = 'none';
                if (next) next.style.display = 'none';
            } else {
                updateButtons();
                if (prev) prev.addEventListener('click', function(){ slideTo(index - 1); });
                if (next) next.addEventListener('click', function(){ slideTo(index + 1); });
            }
        })();

        // Inicializa todos os carrosséis de produtos (home). Cada slider possui data-slider="ID"
        (function() {
            var sliders = document.querySelectorAll('.home-slider[data-slider]');
            if (!sliders.length) return;
            sliders.forEach(function(slider) {
                var sliderId = slider.getAttribute('data-slider');
                var cards = slider.children;
                var prev = document.querySelector('.home-prev[data-target="' + sliderId + '"]');
                var next = document.querySelector('.home-next[data-target="' + sliderId + '"]');
                var visible = 6;
                var index = 0;
                function updateButtons() {
                    var maxIndex = Math.max(0, cards.length - visible);
                    if (prev) prev.style.visibility = index > 0 ? 'visible' : 'hidden';
                    if (next) next.style.visibility = index < maxIndex ? 'visible' : 'hidden';
                }
                function slideTo(n) {
                    index = Math.max(0, Math.min(n, Math.max(0, cards.length - visible)));
                    var cardWidth = cards[0] ? cards[0].getBoundingClientRect().width + 16 : 0;
                    slider.scrollTo({ left: index * cardWidth, behavior: 'smooth' });
                    updateButtons();
                }
                if (cards.length <= visible) {
                    if (prev) prev.style.display = 'none';
                    if (next) next.style.display = 'none';
                } else {
                    updateButtons();
                    if (prev) prev.addEventListener('click', function(){ slideTo(index - 1); });
                    if (next) next.addEventListener('click', function(){ slideTo(index + 1); });
                    window.addEventListener('resize', updateButtons);
                }
            });
        })();
    </script>
</body>
</html>
