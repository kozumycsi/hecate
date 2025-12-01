<?php
/**
 * Teste direto para verificar se recem_adicionado está sendo salvo
 */

require_once __DIR__ . '/service/conexao.php';

$db = new UsePDO();
$conn = $db->getInstance();

echo "=== TESTE: Verificação de recem_adicionado ===\n\n";

// 1. Verifica se a coluna existe
echo "1. Verificando coluna...\n";
try {
    $stmt = $conn->query("SHOW COLUMNS FROM produto LIKE 'recem_adicionado'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($coluna) {
        echo "   ✓ Coluna existe: " . print_r($coluna, true) . "\n";
    } else {
        echo "   ✗ Coluna NÃO existe!\n";
        echo "   Execute a migration primeiro!\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Lista produtos e seus valores de recem_adicionado
echo "\n2. Produtos e seus valores de recem_adicionado:\n";
try {
    $stmt = $conn->query("SELECT id_produto, nome, estoque, recem_adicionado FROM produto ORDER BY id_produto DESC LIMIT 10");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($produtos)) {
        echo "   Nenhum produto encontrado.\n";
    } else {
        echo "   Total de produtos (últimos 10):\n";
        foreach ($produtos as $p) {
            $recem = isset($p['recem_adicionado']) ? (int)$p['recem_adicionado'] : 'NULL';
            $estoque = (int)$p['estoque'];
            $status = $recem == 1 ? "✓ MARCADO" : "✗ não marcado";
            $estoqueOk = $estoque > 0 ? "✓" : "✗ sem estoque";
            echo "   ID {$p['id_produto']}: {$p['nome']} | recem_adicionado = {$recem} ({$status}) | estoque = {$estoque} ({$estoqueOk})\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
}

// 3. Testa query direta
echo "\n3. Testando query direta (produtos que DEVERIAM aparecer):\n";
try {
    $sql = "SELECT p.id_produto, p.nome, p.estoque, p.recem_adicionado
            FROM produto p
            WHERE p.recem_adicionado = 1
              AND p.estoque > 0
            ORDER BY p.id_produto DESC
            LIMIT 10";
    $stmt = $conn->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($resultados)) {
        echo "   ✗ Nenhum produto encontrado com recem_adicionado = 1 E estoque > 0\n";
        echo "   Isso significa que:\n";
        echo "   - Ou nenhum produto está marcado como recém adicionado\n";
        echo "   - Ou os produtos marcados não têm estoque\n";
    } else {
        echo "   ✓ Encontrados " . count($resultados) . " produtos:\n";
        foreach ($resultados as $r) {
            echo "   - ID {$r['id_produto']}: {$r['nome']} (estoque: {$r['estoque']})\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Erro na query: " . $e->getMessage() . "\n";
}

// 4. Testa atualização manual
echo "\n4. Para testar manualmente, execute no banco:\n";
echo "   UPDATE produto SET recem_adicionado = 1 WHERE id_produto = [ID_DO_PRODUTO];\n";
echo "   (Substitua [ID_DO_PRODUTO] pelo ID de um produto que você quer testar)\n";

echo "\n=== FIM DO TESTE ===\n";
?>

