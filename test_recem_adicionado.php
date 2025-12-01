<?php
/**
 * Página de teste rápido - acesse via navegador
 * Testa se o campo recem_adicionado está funcionando
 */

session_start();
if (empty($_SESSION['is_admin'])) {
    die("Acesso negado");
}

require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/service/conexao.php';

$prodModel = new ProductModel();
$db = new UsePDO();
$conn = $db->getInstance();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teste - Recém Adicionado</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .ok { color: green; }
        .erro { color: red; }
        .info { background: #e7f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0056b3; color: white; }
    </style>
</head>
<body>
    <h1>Teste: Campo recem_adicionado</h1>
    
    <?php
    // 1. Verifica coluna
    echo '<div class="info">';
    echo '<h3>1. Verificação da Coluna</h3>';
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM produto LIKE 'recem_adicionado'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($column) {
            echo '<p class="ok">✓ Coluna existe</p>';
            $columnExists = true;
        } else {
            echo '<p class="erro">✗ Coluna NÃO existe - Execute a migration!</p>';
            $columnExists = false;
        }
    } catch (Exception $e) {
        echo '<p class="erro">✗ Erro ao verificar coluna: ' . htmlspecialchars($e->getMessage()) . '</p>';
        $columnExists = false;
    }
    echo '</div>';
    
    if ($columnExists) {
        // 2. Lista produtos marcados
        echo '<div class="info">';
        echo '<h3>2. Produtos Marcados como Recém Adicionado</h3>';
        $stmt = $conn->query("SELECT id_produto, nome, estoque, recem_adicionado FROM produto WHERE recem_adicionado = 1 ORDER BY id_produto DESC");
        $marcados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($marcados)) {
            echo '<p class="erro">⚠ Nenhum produto marcado!</p>';
        } else {
            echo '<p class="ok">✓ ' . count($marcados) . ' produto(s) marcado(s)</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Nome</th><th>Estoque</th><th>Status</th></tr>';
            foreach ($marcados as $p) {
                $estoqueOk = $p['estoque'] > 0 ? '<span class="ok">✓</span>' : '<span class="erro">✗ Sem estoque</span>';
                echo '<tr>';
                echo '<td>' . $p['id_produto'] . '</td>';
                echo '<td>' . htmlspecialchars($p['nome']) . '</td>';
                echo '<td>' . $p['estoque'] . '</td>';
                echo '<td>' . $estoqueOk . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</div>';
        
        // 3. Testa método getNovidades
        echo '<div class="info">';
        echo '<h3>3. Resultado do Método getNovidades()</h3>';
        $novidades = $prodModel->getNovidades(18);
        echo '<p>Produtos retornados: <strong>' . count($novidades) . '</strong></p>';
        
        if (empty($novidades)) {
            echo '<p class="erro">⚠ Nenhum produto retornado!</p>';
            echo '<p><strong>Possíveis causas:</strong></p>';
            echo '<ul>';
            echo '<li>Produtos marcados não têm estoque > 0</li>';
            echo '<li>Produtos estão vinculados a categorias tipo banner</li>';
            echo '<li>Erro na query</li>';
            echo '</ul>';
        } else {
            echo '<p class="ok">✓ Estes produtos aparecerão na home:</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Nome</th><th>Categoria</th><th>Estoque</th></tr>';
            foreach ($novidades as $n) {
                echo '<tr>';
                echo '<td>' . $n['id_produto'] . '</td>';
                echo '<td>' . htmlspecialchars($n['nome']) . '</td>';
                echo '<td>' . htmlspecialchars($n['categoria_nome'] ?? 'N/A') . '</td>';
                echo '<td>' . $n['estoque'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</div>';
        
        // 4. Teste manual de atualização
        if (isset($_GET['test_update']) && isset($_GET['id'])) {
            $testId = (int)$_GET['id'];
            $testValue = isset($_GET['value']) ? (int)$_GET['value'] : 1;
            
            echo '<div class="info">';
            echo '<h3>4. Teste de Atualização Manual</h3>';
            try {
                $stmt = $conn->prepare("UPDATE produto SET recem_adicionado = ? WHERE id_produto = ?");
                $result = $stmt->execute([$testValue, $testId]);
                if ($result) {
                    echo '<p class="ok">✓ Produto ID ' . $testId . ' atualizado com recem_adicionado = ' . $testValue . '</p>';
                    echo '<p><a href="?">Recarregar página</a></p>';
                } else {
                    echo '<p class="erro">✗ Erro ao atualizar</p>';
                }
            } catch (Exception $e) {
                echo '<p class="erro">✗ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';
        }
        
        // 5. Lista últimos produtos para teste
        echo '<div class="info">';
        echo '<h3>5. Últimos Produtos (para teste)</h3>';
        $stmt = $conn->query("SELECT id_produto, nome, estoque, recem_adicionado FROM produto ORDER BY id_produto DESC LIMIT 5");
        $ultimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<table>';
        echo '<tr><th>ID</th><th>Nome</th><th>Estoque</th><th>recem_adicionado</th><th>Ação</th></tr>';
        foreach ($ultimos as $p) {
            $recem = (int)($p['recem_adicionado'] ?? 0);
            $acao = $recem == 1 
                ? '<a href="?test_update=1&id=' . $p['id_produto'] . '&value=0">Desmarcar</a>'
                : '<a href="?test_update=1&id=' . $p['id_produto'] . '&value=1">Marcar</a>';
            echo '<tr>';
            echo '<td>' . $p['id_produto'] . '</td>';
            echo '<td>' . htmlspecialchars($p['nome']) . '</td>';
            echo '<td>' . $p['estoque'] . '</td>';
            echo '<td>' . $recem . '</td>';
            echo '<td>' . $acao . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    ?>
    
    <p><a href="produtosadm.php">← Voltar</a></p>
</body>
</html>

