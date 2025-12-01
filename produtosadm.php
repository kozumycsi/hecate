<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/controller/ProductController.php';

$controller = new ProductController();
$data = $controller->getData();

$msg = $data['msg'] ?? '';
$products = $data['products'] ?? [];
$editProduct = $data['editProduct'] ?? null;
$categories = $data['categories'] ?? [];
$bannerCategories = $data['bannerCategories'] ?? [];
$editBannerCategories = $data['editBannerCategories'] ?? [];
$editProductCategories = $data['editProductCategories'] ?? [];
$selectedBannerIds = array_map('intval', array_column($editBannerCategories, 'id_categoria'));
$selectedCategoryIds = array_map('intval', array_column($editProductCategories, 'id_categoria'));
$principalCategoryId = null;
if (!empty($editProductCategories)) {
    foreach ($editProductCategories as $cat) {
        if (isset($cat['principal']) && (int)$cat['principal'] === 1) {
            $principalCategoryId = (int)$cat['id_categoria'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Gerenciamento de Produtos</title>
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
        .form-section, .list-section {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-bar {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        form input[type="text"], form input[type="number"], form select, form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        form textarea {
            resize: vertical;
            min-height: 80px;
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
        .btn-filtrar {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 3px;
        }
        .checkbox-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 6px 12px;
            margin-top: 6px;
        }
        .checkbox-list label { font-weight: normal; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        a.action-edit { color: #0056b3; text-decoration: none; margin-right: 10px; }
        a.action-delete { color: red; text-decoration: none; }
        .msg { color: green; margin-bottom: 10px; }
        .product-image { max-width: 50px; max-height: 50px; }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-ativo { background-color: #28a745; color: white; }
        .status-inativo { background-color: #dc3545; color: white; }
        #dropZone.drag-over {
            background-color: #e3f2fd;
            border-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('imagem_arquivo');
            const dropZoneContent = document.getElementById('dropZoneContent');
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const fileName = document.getElementById('fileName');
            const removeButton = document.getElementById('removeImage');

            // Clique na zona de drop abre o seletor de arquivos
            dropZone.addEventListener('click', function(e) {
                if (e.target !== removeButton && !removeButton.contains(e.target)) {
                    fileInput.click();
                }
            });

            // Previne comportamento padrão do navegador
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Efeito visual quando arrastar sobre a área
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, function() {
                    dropZone.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, function() {
                    dropZone.classList.remove('drag-over');
                }, false);
            });

            // Manipula o drop da imagem
            dropZone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }, false);

            // Quando selecionar arquivo via input
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            // Processa os arquivos
            function handleFiles(files) {
                if (files.length === 0) return;
                
                const file = files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                // Validação
                if (!validTypes.includes(file.type)) {
                    alert('Tipo de arquivo não permitido. Use JPG, JPEG, PNG, GIF ou WEBP.');
                    return;
                }

                if (file.size > maxSize) {
                    alert('Arquivo muito grande. O tamanho máximo é 5MB.');
                    return;
                }

                // Mostra preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    fileName.textContent = file.name;
                    dropZoneContent.style.display = 'none';
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            // Botão remover imagem
            removeButton.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.value = '';
                imagePreview.src = '';
                fileName.textContent = '';
                dropZoneContent.style.display = 'block';
                previewContainer.style.display = 'none';
            });
            
            // ===== Validação de Categorias =====
            const categoriasContainer = document.getElementById('categorias-container');
            
            if (categoriasContainer) {
                // Validação no submit do formulário
                const form = categoriasContainer.closest('form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const checkboxes = categoriasContainer.querySelectorAll('input[type="checkbox"]:checked');
                        if (checkboxes.length === 0) {
                            e.preventDefault();
                            alert('Por favor, selecione pelo menos uma categoria para o produto.');
                            return false;
                        }
                    });
                }
            }
        });
    </script>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias">Categorias</a>
        <a href="produtosadm.php" data-section="produtos" class="active">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <div class="form-section">
            <div class="header-bar blue-bar"><?= $editProduct ? 'Editar Produto' : 'Adicionar Produto' ?></div>
            <?php if ($msg): ?>
                <div class="msg"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <form method="post" action="../controller/ProductController.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $editProduct ? 'update' : 'add' ?>" />
                <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?= $editProduct['id_produto'] ?>" />
                <?php endif; ?>

                <label for="nome">Nome do Produto</label>
                <input type="text" id="nome" name="nome" required placeholder="Digite o nome" value="<?= $editProduct ? htmlspecialchars($editProduct['nome']) : '' ?>" />

                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" placeholder="Descrição do produto"><?= $editProduct ? htmlspecialchars($editProduct['descricao']) : '' ?></textarea>

                <label for="preco">Preço (R$)</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0" required placeholder="0.00" value="<?= $editProduct ? $editProduct['preco'] : '' ?>" />

                <label for="categorias">Categorias (selecione uma ou mais)</label>
                <div class="checkbox-list" id="categorias-container">
                    <?php foreach ($categories as $cat):
                        $isSelected = in_array((int)$cat['id_categoria'], $selectedCategoryIds, true);
                    ?>
                        <label style="margin-right:10px;">
                            <input type="checkbox" name="categorias[]" value="<?= $cat['id_categoria'] ?>" <?= $isSelected ? 'checked' : '' ?> />
                            <?= htmlspecialchars($cat['nome']) ?> (<?= htmlspecialchars($cat['tipo']) ?>)
                        </label>
                    <?php endforeach; ?>
                </div>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    O produto aparecerá em todas as categorias selecionadas.
                </small>

                <label for="estoque" style="margin-top: 15px;">Estoque</label>
                <input type="number" id="estoque" name="estoque" min="0" required placeholder="0" value="<?= $editProduct ? $editProduct['estoque'] : '' ?>" />
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    <strong>Nota:</strong> Quando o estoque mudar de 0 para um valor maior, o sistema automaticamente marcará o produto como "Voltaram".
                </small>
                <?php if ($editProduct && !empty($editProduct['estoque_atualizado_em'])): ?>
                    <small style="color: #28a745; font-size: 12px; display: block; margin-top: 5px;">
                        <strong>Última reposição de estoque:</strong> <?= date('d/m/Y H:i', strtotime($editProduct['estoque_atualizado_em'])) ?>
                    </small>
                <?php endif; ?>

                <label>Tamanhos disponíveis</label>
                <div class="checkbox-list">
                    <?php
                        // Tamanhos padrão de roupa + numéricos para calçados (33 ao 44)
                        $sizeOptions = [
                            'PP','P','M','G','GG','XG','XXG','XXXG','4XL','UNICO',
                            '33','34','35','36','37','38','39','40','41','42','43','44'
                        ];
                        $selectedSizes = [];
                        if (!empty($editProduct['tamanhos'])) {
                            $selectedSizes = array_map('trim', explode(',', strtoupper($editProduct['tamanhos'])));
                        }
                    ?>
                    <?php foreach ($sizeOptions as $sz): ?>
                        <label style="margin-right:10px;">
                            <input type="checkbox" name="tamanhos[]" value="<?= $sz ?>" <?= in_array($sz, $selectedSizes, true) ? 'checked' : '' ?> /> <?= $sz === 'UNICO' ? 'Único' : $sz ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <label style="margin-top: 15px;">
                    <input type="checkbox" name="recem_adicionado" value="1" <?= ($editProduct && isset($editProduct['recem_adicionado']) && (int)$editProduct['recem_adicionado'] === 1) ? 'checked' : '' ?> />
                    <strong>Recém Adicionado</strong> (aparece na seção "Novidades" da home)
                </label>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px; margin-left: 20px;">
                    Marque esta opção para que o produto apareça automaticamente na seção "Novidades" da página inicial.
                </small>

                <?php if ($editProduct): ?>
                    <?php
                    // Calcula total de vendas se o campo não existir
                    $totalVendas = 0;
                    if (isset($editProduct['total_vendas'])) {
                        $totalVendas = (int)$editProduct['total_vendas'];
                    } else {
                        // Tenta calcular das vendas reais
                        try {
                            require_once __DIR__ . '/model/PedidoModel.php';
                            $pedidoModel = new PedidoModel();
                            $itens = $pedidoModel->getItensDoPedido(0); // Não funciona assim, precisa de outra query
                            // Vamos fazer uma query direta
                            require_once __DIR__ . '/service/conexao.php';
                            $db = new UsePDO();
                            $conn = $db->getInstance();
                            $stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade), 0) as total 
                                                    FROM item_do_pedido ip 
                                                    INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido 
                                                    WHERE ip.id_produto = ? AND ped.status != 'Cancelado'");
                            $stmt->execute([$editProduct['id_produto']]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            $totalVendas = (int)($result['total'] ?? 0);
                        } catch (Throwable $e) {
                            $totalVendas = 0;
                        }
                    }
                    ?>
                    <div style="margin-top: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 3px;">
                        <strong>Estatísticas do Produto:</strong><br>
                        <span style="font-size: 14px; color: #333;">
                            <strong>Total de vendas:</strong> <?= $totalVendas ?> unidade(s)
                        </span>
                    </div>
                <?php endif; ?>

                <label for="imagem" style="margin-top: 15px;">Imagem do Produto</label>
                <div style="display: grid; gap: 8px;">
                    <input type="text" id="imagem" name="imagem" placeholder="URL da imagem (opcional)" value="<?= $editProduct ? htmlspecialchars($editProduct['imagem']) : '' ?>" />
                    <div>
                        <div id="dropZone" style="border: 2px dashed #ccc; border-radius: 5px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s; background-color: #f9f9f9;">
                            <div id="dropZoneContent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#999" viewBox="0 0 16 16" style="margin-bottom: 10px;">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                </svg>
                                <p style="margin: 10px 0; color: #666; font-size: 16px;"><strong>Arraste a imagem aqui</strong> ou clique para selecionar</p>
                                <p style="margin: 0; color: #999; font-size: 13px;">JPG, JPEG, PNG, GIF ou WEBP (máx. 5MB)</p>
                            </div>
                            <div id="previewContainer" style="display: none; position: relative;">
                                <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 5px;" />
                                <p id="fileName" style="margin-top: 10px; color: #333; font-weight: bold;"></p>
                                <button type="button" id="removeImage" style="margin-top: 10px; background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer;">Remover</button>
                            </div>
                        </div>
                        <input type="file" id="imagem_arquivo" name="imagem_arquivo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="display: none;" />
                        <small style="color:#666; display: block; margin-top: 8px;">Você pode informar uma URL OU enviar um arquivo (JPG, JPEG, PNG, GIF ou WEBP, até 5MB). Se um arquivo for enviado, ele terá prioridade sobre a URL.</small>
                    </div>
                </div>

                <button type="submit" class="btn-adicionar"><?= $editProduct ? 'Atualizar' : 'Adicionar' ?></button>
                <?php if ($editProduct): ?>
                    <a href="produtosadm.php" style="margin-left: 10px;">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="filter-bar">
            <form method="get" action="produtosadm.php" style="display: flex; gap: 10px; align-items: center; width: 100%;">
                <label for="categoria-filter" style="margin: 0;">Categoria:</label>
                <select id="categoria-filter" name="categoria" style="width: auto; margin: 0;">
                    <option value="">Todas</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= isset($_GET['categoria']) && $_GET['categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="tipo-filter" style="margin: 0;">Tipo:</label>
                <select id="tipo-filter" name="tipo" style="width: auto; margin: 0;">
                    <option value="">Todos</option>
                    <option value="Categoria Principal" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Categoria Principal' ? 'selected' : '' ?>>Categoria Principal</option>
                    <option value="Subcategoria" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Subcategoria' ? 'selected' : '' ?>>Subcategoria</option>
                    <option value="Categoria Tipo Banner" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Categoria Tipo Banner' ? 'selected' : '' ?>>Categoria Tipo Banner</option>
                </select>

                <label for="status-filter" style="margin: 0;">Status:</label>
                <select id="status-filter" name="status" style="width: auto; margin: 0;">
                    <option value="">Todos</option>
                    <option value="ativo" <?= isset($_GET['status']) && $_GET['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= isset($_GET['status']) && $_GET['status'] == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>

                <button type="submit" class="btn-filtrar">Filtrar</button>
                <a href="produtosadm.php" class="btn-filtrar" style="text-decoration: none; display: inline-block;">Limpar</a>
            </form>
        </div>

        <div class="list-section">
            <div class="header-bar yellow-bar">Lista de Produtos</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Status</th>
                        <th>Novidades</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><?= $prod['id_produto'] ?></td>
                            <td>
                                <?php if (!empty($prod['imagem'])): ?>
                                    <img src="<?= htmlspecialchars($prod['imagem']) ?>" alt="Produto" class="product-image" />
                                <?php else: ?>
                                    Sem imagem
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
                            <td>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                            <td><?= $prod['estoque'] ?></td>
                            <td>
                                <span class="status-badge <?= $prod['estoque'] > 0 ? 'status-ativo' : 'status-inativo' ?>">
                                    <?= $prod['estoque'] > 0 ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($prod['recem_adicionado']) && (int)$prod['recem_adicionado'] === 1): ?>
                                    <span style="color: #28a745; font-weight: bold;">✓</span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="produtosadm.php?edit=<?= $prod['id_produto'] ?>" class="action-edit">Editar</a>
                                <form method="post" action="../controller/ProductController.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="hidden" name="id" value="<?= $prod['id_produto'] ?>" />
                                    <button type="submit" style="background:none;border:none;color:red;cursor:pointer;">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="9">Nenhum produto cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>