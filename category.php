<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/controller/CategoryController.php';

$controller = new CategoryController();
$data = $controller->getData();

$msg = $data['msg'] ?? '';
$categories = $data['categories'] ?? [];
$editCategory = $data['editCategory'] ?? null;
$principalCategories = $data['principalCategories'] ?? [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastro de Categoria</title>
    <link rel="stylesheet" href="paineladm.css" />
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .blue-bar {
            background-color: #0056b3;
        }
        .yellow-bar {
            background-color: #f1c40f;
            color: black;
        }
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
        form input[type="text"], form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
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
        a.action-edit {
            color: #0056b3;
            text-decoration: none;
            margin-right: 10px;
        }
        a.action-delete {
            color: red;
            text-decoration: none;
        }
        .msg {
            color: green;
            margin-bottom: 10px;
        }
        .msg.error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 3px;
        }
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .delete-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 500px;
            max-width: 90%;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .delete-modal-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #dc3545;
        }
        .delete-modal-body {
            margin-bottom: 20px;
        }
        .delete-modal-issues {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
        }
        .delete-modal-issues ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .delete-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-confirm-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias" class="active">Categorias</a>
        <a href="produtosadm.php" data-section="produtos">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <div class="form-section">
            <div class="header-bar blue-bar">Criar categoria</div>
            <?php if ($msg): ?>
                <div class="msg <?= strpos($msg, 'Erro') !== false || strpos($msg, 'não é possível') !== false ? 'error' : '' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="../controller/CategoryController.php">
                <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'add' ?>" />
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= $editCategory['id_categoria'] ?>" />
                <?php endif; ?>

                <label for="name">Nome da Categoria</label>
                <input type="text" id="name" name="name" required placeholder="Digite o nome" value="<?= $editCategory ? htmlspecialchars($editCategory['nome']) : '' ?>" />

                <label for="type">Tipo</label>
                <select id="type" name="type" required>
                    <option value="">Selecione</option>
                    <option value="Categoria Principal" <?= $editCategory && $editCategory['tipo'] === 'Categoria Principal' ? 'selected' : '' ?>>Categoria Principal</option>
                    <option value="Subcategoria" <?= $editCategory && $editCategory['tipo'] === 'Subcategoria' ? 'selected' : '' ?>>Subcategoria</option>
                    <option value="Categoria Tipo Banner" <?= $editCategory && $editCategory['tipo'] === 'Categoria Tipo Banner' ? 'selected' : '' ?>>Categoria Tipo Banner</option>
                </select>

                <div id="parent-wrapper" style="display: none;">
                    <label for="parent_id">Categoria Principal (opcional)</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Selecione</option>
                        <?php foreach ($principalCategories as $pc): ?>
                            <option value="<?= (int)$pc['id_categoria'] ?>" <?= $editCategory && !empty($editCategory['parent_id']) && (int)$editCategory['parent_id'] === (int)$pc['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($pc['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label>
                    <input type="checkbox" name="active" <?= !$editCategory || !isset($editCategory['ativo']) || (int)$editCategory['ativo'] === 1 ? 'checked' : '' ?> /> Ativa
                </label>

                <label for="sort_order">Ordem (navbar/destaques)</label>
                <input type="number" id="sort_order" name="sort_order" min="0" placeholder="Opcional" value="<?= $editCategory && isset($editCategory['sort_order']) ? (int)$editCategory['sort_order'] : '' ?>" />

                <button type="submit" class="btn-adicionar"><?= $editCategory ? 'Atualizar' : 'Adicionar' ?></button>
                <?php if ($editCategory): ?>
                    <a href="category.php" style="margin-left: 10px;">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <?php
            // Agrupa categorias por tipo para exibir uma tabela para cada grupo
            $groups = [
                'Categoria Principal' => [],
                'Subcategoria' => [],
                'Categoria Tipo Banner' => [],
            ];
            foreach ($categories as $cat) {
                $tipo = $cat['tipo'] ?? 'Outros';
                if (!isset($groups[$tipo])) {
                    $groups[$tipo] = [];
                }
                $groups[$tipo][] = $cat;
            }

            $labels = [
                'Categoria Principal' => 'Categorias Principais',
                'Subcategoria' => 'Subcategorias',
                'Categoria Tipo Banner' => 'Categorias Tipo Banner',
            ];
        ?>

        <?php foreach ($labels as $tipoKey => $titulo): ?>
            <?php $catsGroup = $groups[$tipoKey] ?? []; ?>
            <div class="list-section">
                <div class="header-bar yellow-bar"><?= $titulo ?></div>
                <?php if (empty($catsGroup)): ?>
                    <p>Nenhuma categoria deste tipo cadastrada.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ativa</th>
                                <th style="width: 180px;">Posição (ordem)</th>
                                <th>Qtd. Produtos</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catsGroup as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['nome']) ?></td>
                                    <td><?= isset($cat['ativo']) ? ((int)$cat['ativo'] === 1 ? 'Sim' : 'Não') : 'Sim' ?></td>
                                    <td>
                                        <form method="post" action="../controller/CategoryController.php" style="display:flex; align-items:center; gap:6px;">
                                            <input type="hidden" name="action" value="update_order" />
                                            <input type="hidden" name="id" value="<?= (int)$cat['id_categoria'] ?>" />
                                            <input
                                                type="number"
                                                name="sort_order"
                                                min="0"
                                                value="<?= isset($cat['sort_order']) ? (int)$cat['sort_order'] : '' ?>"
                                                style="width:80px; padding:4px;"
                                            />
                                            <button type="submit" style="padding:4px 8px; font-size:12px;">Salvar</button>
                                        </form>
                                    </td>
                                    <td><?= htmlspecialchars($cat['quantidade'] ?? '') ?></td>
                                    <td>
                                        <a href="category.php?edit=<?= (int)$cat['id_categoria'] ?>" class="action-edit">Editar</a>
                                        <button type="button" onclick="handleDeleteCategory(<?= (int)$cat['id_categoria'] ?>, '<?= htmlspecialchars(addslashes($cat['nome'])) ?>')" style="background:none;border:none;color:red;cursor:pointer;">Excluir</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
<script>
    (function() {
        var typeSel = document.getElementById('type');
        var parentWrap = document.getElementById('parent-wrapper');
        function toggleParent() {
            if (!typeSel) return;
            parentWrap.style.display = typeSel.value === 'Subcategoria' ? 'block' : 'none';
        }
        if (typeSel && parentWrap) {
            typeSel.addEventListener('change', toggleParent);
            toggleParent();
        }
    })();

    // Modal de exclusão de categoria
    let deleteModal = null;
    let currentDeleteCategoryId = null;
    let allCategoriesForMove = <?= json_encode($categories) ?>;

    function handleDeleteCategory(categoryId, categoryName) {
        currentDeleteCategoryId = categoryId;
        
        // Cria modal se não existir
        if (!deleteModal) {
            deleteModal = document.createElement('div');
            deleteModal.className = 'delete-modal';
            deleteModal.id = 'deleteCategoryModal';
            deleteModal.innerHTML = `
                <div class="delete-modal-content">
                    <div class="delete-modal-header">Excluir Categoria</div>
                    <div class="delete-modal-body">
                        <p>Tem certeza que deseja excluir a categoria "<strong id="deleteCategoryName"></strong>"?</p>
                        <div id="deleteCategoryIssues" class="delete-modal-issues" style="display: none;">
                            <strong>Atenção:</strong> Esta categoria possui dependências:
                            <ul id="deleteCategoryIssuesList"></ul>
                            <p style="margin-top: 10px; margin-bottom: 0;">
                                <strong>Opção:</strong> Selecione uma categoria para mover os produtos antes de excluir:
                            </p>
                            <select id="moveToCategorySelect" style="width: 100%; margin-top: 10px; padding: 8px;">
                                <option value="">-- Selecione uma categoria --</option>
                            </select>
                        </div>
                    </div>
                    <div class="delete-modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancelar</button>
                        <button type="button" class="btn-confirm-delete" onclick="confirmDeleteCategory()">Excluir</button>
                    </div>
                </div>
            `;
            document.body.appendChild(deleteModal);
        }
        
        document.getElementById('deleteCategoryName').textContent = categoryName;
        
        // Verifica dependências via AJAX
        fetch('controller/CategoryController.php?check_delete=' + categoryId)
            .then(response => response.json())
            .then(data => {
                const issuesDiv = document.getElementById('deleteCategoryIssues');
                const issuesList = document.getElementById('deleteCategoryIssuesList');
                const moveSelect = document.getElementById('moveToCategorySelect');
                
                if (data.canDelete) {
                    issuesDiv.style.display = 'none';
                } else {
                    issuesDiv.style.display = 'block';
                    issuesList.innerHTML = '';
                    data.issues.forEach(issue => {
                        const li = document.createElement('li');
                        li.textContent = issue;
                        issuesList.appendChild(li);
                    });
                    
                    // Preenche select com outras categorias (exceto a que será deletada)
                    moveSelect.innerHTML = '<option value="">-- Selecione uma categoria --</option>';
                    if (allCategoriesForMove && Array.isArray(allCategoriesForMove)) {
                        allCategoriesForMove.forEach(cat => {
                            if (parseInt(cat.id_categoria) !== parseInt(categoryId) && cat.tipo !== 'Categoria Tipo Banner') {
                                const option = document.createElement('option');
                                option.value = cat.id_categoria;
                                option.textContent = cat.nome + ' (' + cat.tipo + ')';
                                moveSelect.appendChild(option);
                            }
                        });
                    }
                }
                
                deleteModal.style.display = 'block';
            })
            .catch(error => {
                console.error('Erro ao verificar dependências:', error);
                deleteModal.style.display = 'block';
                document.getElementById('deleteCategoryIssues').style.display = 'none';
            });
    }

    function closeDeleteModal() {
        if (deleteModal) {
            deleteModal.style.display = 'none';
        }
        currentDeleteCategoryId = null;
    }

    function confirmDeleteCategory() {
        if (!currentDeleteCategoryId) return;
        
        const moveToCategoryId = document.getElementById('moveToCategorySelect').value;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../controller/CategoryController.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = currentDeleteCategoryId;
        form.appendChild(idInput);
        
        if (moveToCategoryId) {
            const moveInput = document.createElement('input');
            moveInput.type = 'hidden';
            moveInput.name = 'move_to_category';
            moveInput.value = moveToCategoryId;
            form.appendChild(moveInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }

    // Fecha modal ao clicar fora
    window.onclick = function(event) {
        if (deleteModal && event.target === deleteModal) {
            closeDeleteModal();
        }
    }
</script>
</html>
