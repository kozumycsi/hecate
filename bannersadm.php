<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/controller/BannerController.php';

$controller = new BannerController();
$data = $controller->getData();

$msg = $data['msg'] ?? '';
$banners = $data['banners'] ?? [];
$allCategories = $data['allCategories'] ?? [];
$allProducts = $data['allProducts'] ?? [];

// Banner em edição (se houver)
$editBanner = null;
$editBannerId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($editBannerId > 0) {
    $editBanner = $controller->getBannerForEdit($editBannerId);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Gerenciamento de Banners</title>
    <link rel="stylesheet" href="paineladm.css" />
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .blue-bar { background-color: #0056b3; }
        .yellow-bar { background-color: #f1c40f; color: black; }
        .green-bar { background-color: #28a745; }
        .form-section, .list-section {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        form input[type="text"],
        form input[type="url"],
        form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }
        form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .multiselect-container {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 8px;
            margin-top: 5px;
            max-height: 200px;
            overflow-y: auto;
            background-color: white;
        }
        .multiselect-container label {
            display: block;
            margin: 5px 0;
            font-weight: normal;
        }
        .multiselect-container input[type="checkbox"] {
            margin-right: 8px;
        }
        .products-list {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 10px;
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 8px;
            margin: 5px 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: move;
        }
        .product-item:hover {
            background-color: #f0f0f0;
        }
        .product-item .drag-handle {
            cursor: move;
            margin-right: 10px;
            color: #666;
        }
        .product-item .product-info {
            flex: 1;
        }
        .product-item .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .product-search {
            margin-bottom: 10px;
        }
        .product-search input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .product-select-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
            background-color: white;
        }
        .product-select-item {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .product-select-item:hover {
            background-color: #e7f3ff;
        }
        .product-select-item:last-child {
            border-bottom: none;
        }
        .btn-adicionar {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 8px 15px;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-adicionar:hover {
            background-color: #004494;
        }
        .btn-editar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-editar:hover {
            background-color: #218838;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f9f9f9; 
        }
        .msg { 
            color: green; 
            margin-bottom: 10px; 
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 3px;
        }
        .msg.error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .banner-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
        }
        .banner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .banner-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .banner-card img {
            width: 100%;
            height: auto;
            border-radius: 3px;
        }
        .banner-card-actions {
            margin-top: 10px;
            text-align: center;
        }
        .banner-card-actions form {
            display: inline;
            margin: 0 5px;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 40px;
            text-align: center;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #0056b3;
            background-color: #e7f3ff;
        }
        .upload-area.dragover {
            border-color: #0056b3;
            background-color: #cfe2ff;
        }
        #imagem_arquivo {
            display: none;
        }
        .file-info {
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias">Categorias</a>
        <a href="produtosadm.php" data-section="produtos">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners" class="active">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <div class="form-section">
            <div class="header-bar <?= $editBanner ? 'green-bar' : 'blue-bar' ?>">
                <?= $editBanner ? 'Editar Banner' : 'Cadastrar Novo Banner' ?>
            </div>
            <?php if ($msg): ?>
                <div class="msg <?= strpos($msg, 'Erro') !== false || strpos($msg, 'inválido') !== false ? 'error' : '' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="../controller/BannerController.php" enctype="multipart/form-data" id="bannerForm">
                <input type="hidden" name="action" value="<?= $editBanner ? 'edit' : 'add' ?>" />
                <?php if ($editBanner): ?>
                    <input type="hidden" name="id_banner" value="<?= $editBanner['id_banner'] ?>" />
                <?php endif; ?>

                <label for="titulo">Título do Banner *</label>
                <input type="text" id="titulo" name="titulo" required 
                       value="<?= htmlspecialchars($editBanner['titulo'] ?? '') ?>" 
                       placeholder="Ex: Promoção de Verão" />

                <label for="tipo_banner" style="margin-top: 15px;">Tipo de Banner *</label>
                <select id="tipo_banner" name="tipo_banner" required>
                    <option value="divulgacao" <?= ($editBanner['tipo_banner'] ?? 'divulgacao') === 'divulgacao' ? 'selected' : '' ?>>Divulgação</option>
                    <option value="decoracao" <?= ($editBanner['tipo_banner'] ?? '') === 'decoracao' ? 'selected' : '' ?>>Decoração</option>
                </select>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    Banners de divulgação podem ter produtos associados. Banners de decoração são apenas visuais.
                </small>

                <label for="categorias" style="margin-top: 15px;">Categorias (multiselect)</label>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px; margin-bottom: 10px;">
                    <?php if (($editBanner['tipo_banner'] ?? 'divulgacao') === 'divulgacao'): ?>
                        <strong>Nota:</strong> Uma categoria fantasma (tipo banner) é criada automaticamente para banners de divulgação. 
                        Você pode vincular categorias adicionais abaixo para organizar produtos.
                    <?php else: ?>
                        Selecione categorias para vincular ao banner.
                    <?php endif; ?>
                </small>
                <div class="multiselect-container">
                    <?php if (empty($allCategories)): ?>
                        <p style="color: #dc3545; font-size: 12px;">
                            Nenhuma categoria encontrada. <a href="category.php">Crie categorias primeiro</a>.
                        </p>
                    <?php else: ?>
                        <?php 
                        $selectedCategoryIds = [];
                        $bannerCategoryIds = [];
                        if ($editBanner && isset($editBanner['categorias'])) {
                            $selectedCategoryIds = array_map(function($c) { return (int)$c['id_categoria']; }, $editBanner['categorias']);
                            // Identifica categoria fantasma (tipo banner)
                            foreach ($editBanner['categorias'] as $c) {
                                if ($c['tipo'] === 'Categoria Tipo Banner') {
                                    $bannerCategoryIds[] = (int)$c['id_categoria'];
                                }
                            }
                        }
                        // Separa categorias por tipo para melhor organização
                        $categoriasPrincipais = [];
                        $subcategorias = [];
                        $categoriasBanner = [];
                        foreach ($allCategories as $cat) {
                            if ($cat['tipo'] === 'Categoria Principal') {
                                $categoriasPrincipais[] = $cat;
                            } elseif ($cat['tipo'] === 'Subcategoria') {
                                $subcategorias[] = $cat;
                            } elseif ($cat['tipo'] === 'Categoria Tipo Banner') {
                                $categoriasBanner[] = $cat;
                            }
                        }
                        ?>
                        
                        <?php if (!empty($categoriasBanner) && $editBanner): ?>
                            <div style="background-color: #e7f3ff; padding: 10px; margin-bottom: 10px; border-radius: 3px; border-left: 4px solid #0056b3;">
                                <strong style="color: #0056b3; font-size: 12px;">Categoria Fantasma do Banner (criada automaticamente):</strong>
                                <?php foreach ($categoriasBanner as $cat): ?>
                                    <?php if (in_array((int)$cat['id_categoria'], $bannerCategoryIds)): ?>
                                        <div style="margin-top: 5px; padding: 5px; background: white; border-radius: 3px;">
                                            <input type="checkbox" name="categorias[]" value="<?= (int)$cat['id_categoria'] ?>" 
                                                   checked disabled style="opacity: 0.6;" />
                                            <strong><?= htmlspecialchars($cat['nome']) ?></strong> <span style="color: #0056b3; font-size: 11px;">(Categoria Tipo Banner - vinculada automaticamente)</span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($categoriasPrincipais)): ?>
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 12px; color: #333;">Categorias Principais:</strong>
                                <?php foreach ($categoriasPrincipais as $cat): ?>
                                    <label style="display: block; margin: 5px 0;">
                                        <input type="checkbox" name="categorias[]" value="<?= (int)$cat['id_categoria'] ?>" 
                                               <?= in_array((int)$cat['id_categoria'], $selectedCategoryIds) ? 'checked' : '' ?> />
                                        <?= htmlspecialchars($cat['nome']) ?> <span style="color: #666; font-size: 11px;">(Categoria Principal)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($subcategorias)): ?>
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 12px; color: #333;">Subcategorias:</strong>
                                <?php foreach ($subcategorias as $cat): ?>
                                    <label style="display: block; margin: 5px 0;">
                                        <input type="checkbox" name="categorias[]" value="<?= (int)$cat['id_categoria'] ?>" 
                                               <?= in_array((int)$cat['id_categoria'], $selectedCategoryIds) ? 'checked' : '' ?> />
                                        <?= htmlspecialchars($cat['nome']) ?> <span style="color: #666; font-size: 11px;">(Subcategoria)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($categoriasBanner) && !$editBanner): ?>
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 12px; color: #333;">Categorias Tipo Banner (outras):</strong>
                                <?php foreach ($categoriasBanner as $cat): ?>
                                    <?php if (!in_array((int)$cat['id_categoria'], $bannerCategoryIds)): ?>
                                        <label style="display: block; margin: 5px 0;">
                                            <input type="checkbox" name="categorias[]" value="<?= (int)$cat['id_categoria'] ?>" 
                                                   <?= in_array((int)$cat['id_categoria'], $selectedCategoryIds) ? 'checked' : '' ?> />
                                            <?= htmlspecialchars($cat['nome']) ?> <span style="color: #666; font-size: 11px;">(Categoria Tipo Banner)</span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div id="produtos-section" style="margin-top: 15px; <?= ($editBanner['tipo_banner'] ?? 'divulgacao') !== 'divulgacao' ? 'display: none;' : '' ?>">
                    <label>Produtos Associados (apenas para banners de divulgação)</label>
                    <div class="product-search">
                        <input type="text" id="product-search" placeholder="Buscar produto..." />
                    </div>
                    <div class="product-select-list" id="product-select-list" style="display: none;"></div>
                    <div class="products-list" id="products-list">
                        <?php
                        $selectedProducts = [];
                        if ($editBanner && isset($editBanner['produtos'])) {
                            $selectedProducts = $editBanner['produtos'];
                        }
                        foreach ($selectedProducts as $prod): 
                        ?>
                            <div class="product-item" data-product-id="<?= $prod['id_produto'] ?>">
                                <span class="drag-handle">☰</span>
                                <div class="product-info">
                                    <strong><?= htmlspecialchars($prod['nome'] ?? 'Produto #' . $prod['id_produto']) ?></strong>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeProduct(<?= $prod['id_produto'] ?>)">Remover</button>
                                <input type="hidden" name="produtos[]" value="<?= $prod['id_produto'] ?>" />
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($selectedProducts)): ?>
                            <p style="text-align: center; color: #666; padding: 20px;">
                                Nenhum produto adicionado. Use a busca acima para adicionar produtos.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <label for="ativo" style="margin-top: 15px;">
                    <input type="checkbox" id="ativo" name="ativo" value="1" 
                           <?= ($editBanner['ativo'] ?? 1) ? 'checked' : '' ?> />
                    Banner Ativo
                </label>

                <label for="imagem_arquivo" style="margin-top: 15px;">Imagem do Banner <?= $editBanner ? '(deixe em branco para manter a atual)' : '*' ?></label>
                <?php if ($editBanner && !empty($editBanner['imagem'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= htmlspecialchars($editBanner['imagem']) ?>" alt="Banner atual" class="banner-preview" />
                    </div>
                <?php endif; ?>
                <div class="upload-area" id="uploadArea">
                    <p>Clique aqui ou arraste uma imagem</p>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        Formatos aceitos: JPG, PNG, GIF (máx. 5MB)
                    </p>
                    <input type="file" id="imagem_arquivo" name="imagem_arquivo" accept="image/*" <?= $editBanner ? '' : 'required' ?> />
                    <div class="file-info" id="fileInfo" style="display: none;"></div>
                </div>

                <button type="submit" class="btn-adicionar">
                    <?= $editBanner ? 'Atualizar Banner' : 'Cadastrar Banner' ?>
                </button>
                <?php if ($editBanner): ?>
                    <a href="bannersadm.php" class="btn-editar" style="background-color: #6c757d; margin-left: 10px;">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="list-section">
            <div class="header-bar yellow-bar">Banners Cadastrados (<?= count($banners) ?>)</div>
            <?php if (empty($banners)): ?>
                <p style="text-align: center; padding: 20px; color: #666;">
                    Nenhum banner cadastrado ainda. Use o formulário acima para adicionar banners.
                </p>
            <?php else: ?>
                <div class="banner-grid">
                    <?php foreach ($banners as $banner): ?>
                        <div class="banner-card">
                            <?php if (!empty($banner['imagem'])): ?>
                                <img src="<?= htmlspecialchars($banner['imagem']) ?>" alt="Banner" class="banner-preview" />
                            <?php else: ?>
                                <p>Sem imagem</p>
                            <?php endif; ?>
                            <div style="margin-top: 10px; padding: 8px; background-color: #e7f3ff; border-radius: 3px;">
                                <strong>Título:</strong> <?= htmlspecialchars($banner['titulo'] ?? 'N/A') ?><br>
                                <strong>Tipo:</strong> <?= $banner['tipo_banner'] === 'divulgacao' ? 'Divulgação' : 'Decoração' ?><br>
                                <strong>Status:</strong> <?= $banner['ativo'] ? 'Ativo' : 'Inativo' ?><br>
                                <strong>Categorias:</strong><br>
                                <span style="font-size: 12px;">
                                    <?php
                                    if (!empty($banner['categorias'])) {
                                        $categoriasNomes = [];
                                        $categoriaFantasma = null;
                                        foreach ($banner['categorias'] as $cat) {
                                            if ($cat['tipo'] === 'Categoria Tipo Banner') {
                                                $categoriaFantasma = $cat;
                                            } else {
                                                $categoriasNomes[] = htmlspecialchars($cat['nome']) . ' (' . htmlspecialchars($cat['tipo']) . ')';
                                            }
                                        }
                                        if ($categoriaFantasma) {
                                            echo '<strong style="color: #0056b3;">' . htmlspecialchars($categoriaFantasma['nome']) . '</strong> <span style="color: #28a745;">(Fantasma)</span>';
                                            if (!empty($categoriasNomes)) {
                                                echo '<br><span style="color: #666;">Outras: ' . implode(', ', $categoriasNomes) . '</span>';
                                            }
                                        } else {
                                            echo htmlspecialchars(implode(', ', array_column($banner['categorias'], 'nome')));
                                        }
                                    } else {
                                        echo '<span style="color: #dc3545;">Nenhuma (categoria fantasma será criada ao salvar)</span>';
                                    }
                                    ?>
                                </span>
                                <?php if ($banner['tipo_banner'] === 'divulgacao'): ?>
                                    <?php
                                    // Busca produtos pela categoria fantasma
                                    $categoriaFantasma = null;
                                    if (!empty($banner['categorias'])) {
                                        foreach ($banner['categorias'] as $cat) {
                                            if ($cat['tipo'] === 'Categoria Tipo Banner') {
                                                $categoriaFantasma = $cat;
                                                break;
                                            }
                                        }
                                    }
                                    $produtosCount = 0;
                                    if ($categoriaFantasma) {
                                        require_once __DIR__ . '/model/ProductModel.php';
                                        $productModel = new ProductModel();
                                        $produtosCount = count($productModel->getProductsByCategory($categoriaFantasma['id_categoria']));
                                    } else {
                                        $produtosCount = !empty($banner['produtos']) ? count($banner['produtos']) : 0;
                                    }
                                    ?>
                                    <br><strong>Produtos:</strong> <?= $produtosCount ?> produto(s) na categoria fantasma
                                    <br><a href="banner-produtos.php?id=<?= $banner['id_banner'] ?>" target="_blank" style="font-size: 12px; color: #0056b3;">Ver página do banner →</a>
                                <?php endif; ?>
                            </div>
                            <div class="banner-card-actions">
                                <a href="bannersadm.php?edit=<?= $banner['id_banner'] ?>" class="btn-editar">Editar</a>
                                <form method="post" action="../controller/BannerController.php" style="display:inline;" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este banner?');">
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="hidden" name="id" value="<?= $banner['id_banner'] ?>" />
                                    <button type="submit" style="background:red;color:white;border:none;padding:5px 15px;border-radius:3px;cursor:pointer;">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dados dos produtos para busca
        const allProducts = <?= json_encode($allProducts) ?>;
        let selectedProductIds = new Set();
        
        // Inicializa produtos já selecionados (apenas se estiver editando)
        <?php if ($editBanner): ?>
        document.querySelectorAll('input[name="produtos[]"]').forEach(input => {
            const prodId = parseInt(input.value);
            if (prodId > 0) {
                selectedProductIds.add(prodId);
            }
        });
        <?php else: ?>
        // Limpa produtos selecionados ao criar novo banner
        selectedProductIds.clear();
        <?php endif; ?>

        // Upload area interaction
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('imagem_arquivo');
        const fileInfo = document.getElementById('fileInfo');

        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                fileInfo.style.display = 'block';
                fileInfo.textContent = `Arquivo selecionado: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const file = files[0];
                fileInfo.style.display = 'block';
                fileInfo.textContent = `Arquivo selecionado: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            }
        });

        // Toggle produtos section baseado no tipo de banner
        document.getElementById('tipo_banner').addEventListener('change', function() {
            const produtosSection = document.getElementById('produtos-section');
            if (this.value === 'divulgacao') {
                produtosSection.style.display = 'block';
            } else {
                produtosSection.style.display = 'none';
                // Limpa produtos quando muda para decoração
                document.getElementById('products-list').innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">Nenhum produto adicionado. Use a busca acima para adicionar produtos.</p>';
                selectedProductIds.clear();
            }
        });

        // Busca de produtos
        const productSearch = document.getElementById('product-search');
        const productSelectList = document.getElementById('product-select-list');
        
        productSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            if (term === '') {
                productSelectList.style.display = 'none';
                return;
            }

            const filtered = allProducts.filter(p => {
                const name = (p.nome || '').toLowerCase();
                const desc = (p.descricao || '').toLowerCase();
                return (name.includes(term) || desc.includes(term)) && !selectedProductIds.has(parseInt(p.id_produto));
            });

            if (filtered.length === 0) {
                productSelectList.innerHTML = '<div class="product-select-item">Nenhum produto encontrado</div>';
            } else {
                productSelectList.innerHTML = filtered.map(p => 
                    `<div class="product-select-item" onclick="addProduct(${p.id_produto}, '${(p.nome || '').replace(/'/g, "\\'")}')">
                        ${p.nome || 'Produto #' + p.id_produto}
                    </div>`
                ).join('');
            }
            productSelectList.style.display = 'block';
        });

        function addProduct(productId, productName) {
            if (selectedProductIds.has(productId)) {
                return;
            }

            selectedProductIds.add(productId);
            productSearch.value = '';
            productSelectList.style.display = 'none';

            const productsList = document.getElementById('products-list');
            if (productsList.querySelector('p')) {
                productsList.innerHTML = '';
            }

            const productItem = document.createElement('div');
            productItem.className = 'product-item';
            productItem.setAttribute('data-product-id', productId);
            productItem.innerHTML = `
                <span class="drag-handle">☰</span>
                <div class="product-info">
                    <strong>${productName}</strong>
                </div>
                <button type="button" class="remove-btn" onclick="removeProduct(${productId})">Remover</button>
                <input type="hidden" name="produtos[]" value="${productId}" />
            `;
            productsList.appendChild(productItem);
        }

        function removeProduct(productId) {
            selectedProductIds.delete(productId);
            const productItem = document.querySelector(`[data-product-id="${productId}"]`);
            if (productItem) {
                productItem.remove();
            }
            
            const productsList = document.getElementById('products-list');
            if (productsList.children.length === 0) {
                productsList.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">Nenhum produto adicionado. Use a busca acima para adicionar produtos.</p>';
            }
        }

        // Sortable para produtos (usando HTML5 drag and drop simples)
        let draggedElement = null;
        document.addEventListener('DOMContentLoaded', function() {
            const productsList = document.getElementById('products-list');
            if (!productsList) return;

            productsList.addEventListener('dragstart', function(e) {
                if (e.target.classList.contains('product-item') || e.target.closest('.product-item')) {
                    draggedElement = e.target.classList.contains('product-item') ? e.target : e.target.closest('.product-item');
                    draggedElement.style.opacity = '0.5';
                }
            });

            productsList.addEventListener('dragend', function(e) {
                if (draggedElement) {
                    draggedElement.style.opacity = '1';
                    draggedElement = null;
                }
            });

            productsList.addEventListener('dragover', function(e) {
                e.preventDefault();
                const afterElement = getDragAfterElement(productsList, e.clientY);
                if (draggedElement && afterElement == null) {
                    productsList.appendChild(draggedElement);
                } else if (draggedElement && afterElement) {
                    productsList.insertBefore(draggedElement, afterElement);
                }
            });

            // Torna os itens arrastáveis
            productsList.querySelectorAll('.product-item').forEach(item => {
                item.draggable = true;
            });
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.product-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Atualiza ordem dos produtos ao soltar
        document.addEventListener('drop', function(e) {
            if (draggedElement) {
                const productsList = document.getElementById('products-list');
                const items = productsList.querySelectorAll('.product-item');
                items.forEach((item, index) => {
                    const input = item.querySelector('input[name="produtos[]"]');
                    if (input) {
                        // A ordem é baseada na posição no DOM
                    }
                });
            }
        });

        // Form validation
        document.getElementById('bannerForm').addEventListener('submit', (e) => {
            const titulo = document.getElementById('titulo').value.trim();
            if (!titulo) {
                e.preventDefault();
                alert('Por favor, informe o título do banner.');
                return false;
            }
            
            const tipoBanner = document.getElementById('tipo_banner').value;
            if (tipoBanner === 'divulgacao') {
                const produtos = document.querySelectorAll('input[name="produtos[]"]');
                // Produtos são opcionais mesmo para divulgação
            }
            
            if (!document.getElementById('id_banner')) { // Apenas para novos banners
                const file = fileInput.files[0];
                if (!file) {
                    e.preventDefault();
                    alert('Por favor, selecione uma imagem para o banner.');
                    return false;
                }
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('O arquivo é muito grande. O tamanho máximo é 5MB.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>
