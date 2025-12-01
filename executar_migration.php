<?php
/**
 * Script para executar a migration automaticamente
 * Acesse: http://localhost/hecate/executar_migration.php
 */

session_start();
if (empty($_SESSION['is_admin'])) {
    die("Acesso negado. Você precisa estar logado como administrador.");
}

require_once __DIR__ . '/service/conexao.php';

$db = new UsePDO();
$conn = $db->getInstance();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Executar Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 3px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 3px; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 10px; border-radius: 3px; margin: 10px 0; }
        button { background: #0056b3; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #004085; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Executar Migration - Adicionar Colunas</h1>
        
        <?php
        if (isset($_POST['executar'])) {
            echo '<div class="info">';
            echo '<h2>Executando Migration...</h2>';
            
            $sqls = [
                "ALTER TABLE produto ADD COLUMN recem_adicionado TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag para produtos que aparecem na seção Novidades'",
                "ALTER TABLE produto ADD COLUMN estoque_atualizado_em DATETIME NULL COMMENT 'Timestamp de quando o estoque foi reposto (para seção Voltaram)'",
                "ALTER TABLE produto ADD COLUMN total_vendas INT NOT NULL DEFAULT 0 COMMENT 'Contador de total de unidades vendidas (para seção Mais Vendidos)'",
                "CREATE INDEX idx_produto_recem_adicionado ON produto(recem_adicionado)",
                "CREATE INDEX idx_produto_estoque_atualizado ON produto(estoque_atualizado_em)",
                "CREATE INDEX idx_produto_total_vendas ON produto(total_vendas)"
            ];
            
            $erros = [];
            $sucessos = [];
            
            foreach ($sqls as $index => $sql) {
                try {
                    // Verifica se a coluna/index já existe antes de criar
                    if (strpos($sql, 'ADD COLUMN recem_adicionado') !== false) {
                        $check = $conn->query("SHOW COLUMNS FROM produto LIKE 'recem_adicionado'");
                        if ($check->fetch()) {
                            $sucessos[] = "Coluna 'recem_adicionado' já existe, pulando...";
                            continue;
                        }
                    }
                    if (strpos($sql, 'ADD COLUMN estoque_atualizado_em') !== false) {
                        $check = $conn->query("SHOW COLUMNS FROM produto LIKE 'estoque_atualizado_em'");
                        if ($check->fetch()) {
                            $sucessos[] = "Coluna 'estoque_atualizado_em' já existe, pulando...";
                            continue;
                        }
                    }
                    if (strpos($sql, 'ADD COLUMN total_vendas') !== false) {
                        $check = $conn->query("SHOW COLUMNS FROM produto LIKE 'total_vendas'");
                        if ($check->fetch()) {
                            $sucessos[] = "Coluna 'total_vendas' já existe, pulando...";
                            continue;
                        }
                    }
                    if (strpos($sql, 'CREATE INDEX') !== false) {
                        $indexName = preg_match("/idx_produto_(\w+)/", $sql, $matches) ? $matches[1] : '';
                        $check = $conn->query("SHOW INDEX FROM produto WHERE Key_name LIKE 'idx_produto_%'");
                        $indexes = $check->fetchAll(PDO::FETCH_ASSOC);
                        $exists = false;
                        foreach ($indexes as $idx) {
                            if ($idx['Key_name'] === 'idx_produto_' . $indexName) {
                                $exists = true;
                                break;
                            }
                        }
                        if ($exists) {
                            $sucessos[] = "Índice 'idx_produto_$indexName' já existe, pulando...";
                            continue;
                        }
                    }
                    
                    $conn->exec($sql);
                    $sucessos[] = "✓ Comando " . ($index + 1) . " executado com sucesso";
                } catch (PDOException $e) {
                    // Se o erro for "Duplicate column" ou "Duplicate key", ignora
                    if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                        strpos($e->getMessage(), 'Duplicate key') !== false ||
                        strpos($e->getMessage(), 'already exists') !== false) {
                        $sucessos[] = "⚠ Comando " . ($index + 1) . " já foi executado anteriormente";
                    } else {
                        $erros[] = "✗ Erro no comando " . ($index + 1) . ": " . $e->getMessage();
                    }
                }
            }
            
            if (!empty($sucessos)) {
                echo '<div class="success">';
                echo '<h3>Sucessos:</h3><ul>';
                foreach ($sucessos as $msg) {
                    echo '<li>' . htmlspecialchars($msg) . '</li>';
                }
                echo '</ul></div>';
            }
            
            if (!empty($erros)) {
                echo '<div class="error">';
                echo '<h3>Erros:</h3><ul>';
                foreach ($erros as $msg) {
                    echo '<li>' . htmlspecialchars($msg) . '</li>';
                }
                echo '</ul></div>';
            }
            
            // Verifica se as colunas foram criadas
            echo '<h3>Verificação Final:</h3>';
            $colunas = ['recem_adicionado', 'estoque_atualizado_em', 'total_vendas'];
            foreach ($colunas as $col) {
                $check = $conn->query("SHOW COLUMNS FROM produto LIKE '$col'");
                if ($check->fetch()) {
                    echo '<p class="success">✓ Coluna "' . $col . '" existe</p>';
                } else {
                    echo '<p class="error">✗ Coluna "' . $col . '" NÃO existe</p>';
                }
            }
            
            echo '</div>';
            echo '<p><a href="test_recem_adicionado.php">→ Ir para página de teste</a></p>';
        } else {
            ?>
            <div class="info">
                <h2>Instruções</h2>
                <p>Este script irá adicionar as seguintes colunas na tabela <code>produto</code>:</p>
                <ul>
                    <li><strong>recem_adicionado</strong> - Flag para produtos recém adicionados</li>
                    <li><strong>estoque_atualizado_em</strong> - Timestamp de reposição de estoque</li>
                    <li><strong>total_vendas</strong> - Contador de vendas</li>
                </ul>
                <p>Também criará índices para melhorar a performance.</p>
            </div>
            
            <form method="post">
                <button type="submit" name="executar" onclick="return confirm('Tem certeza que deseja executar a migration?');">
                    Executar Migration
                </button>
            </form>
            
            <p style="margin-top: 20px;">
                <strong>Alternativa:</strong> Se preferir, você pode executar o arquivo SQL diretamente no phpMyAdmin:<br>
                <code>add_recem_adicionado_column.sql</code>
            </p>
            <?php
        }
        ?>
        
        <p style="margin-top: 20px;"><a href="produtosadm.php">← Voltar para Dashboard</a></p>
    </div>
</body>
</html>

