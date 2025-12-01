<?php
/**
 * Script de debug para verificar produtos recém adicionados
 * Execute este arquivo para verificar se os produtos estão sendo marcados corretamente
 */

require_once __DIR__ . '/model/ProductModel.php';
require_once __DIR__ . '/service/conexao.php';

try {
    $prodModel = new ProductModel();
    $db = new UsePDO();
    $conn = $db->getInstance();
    
    echo "=== DEBUG: Produtos Recém Adicionados ===\n\n";
    
    // 1. Verifica se a coluna existe
    echo "1. Verificando se a coluna 'recem_adicionado' existe...\n";
    $columnExists = $prodModel->columnExists('produto', 'recem_adicionado');
    echo "   Resultado: " . ($columnExists ? "SIM ✓" : "NÃO ✗") . "\n\n";
    
    if (!$columnExists) {
        echo "ERRO: A coluna 'recem_adicionado' não existe no banco de dados!\n";
        echo "Execute a migration: database/migrations/20250116_add_product_auto_sections.sql\n";
        exit(1);
    }
    
    // 2. Lista todos os produtos com recem_adicionado = 1
    echo "2. Produtos com recem_adicionado = 1:\n";
    $stmt = $conn->query("SELECT id_produto, nome, estoque, recem_adicionado FROM produto WHERE recem_adicionado = 1");
    $produtosMarcados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($produtosMarcados)) {
        echo "   Nenhum produto encontrado com recem_adicionado = 1\n";
        echo "   Verifique se você marcou o checkbox no dashboard\n\n";
    } else {
        echo "   Total encontrado: " . count($produtosMarcados) . "\n";
        foreach ($produtosMarcados as $p) {
            $statusEstoque = $p['estoque'] > 0 ? "✓" : "✗ (sem estoque)";
            echo "   - ID: {$p['id_produto']}, Nome: {$p['nome']}, Estoque: {$p['estoque']} $statusEstoque\n";
        }
        echo "\n";
    }
    
    // 3. Testa o método getNovidades
    echo "3. Testando método getNovidades():\n";
    if (method_exists($prodModel, 'getNovidades')) {
        $novidades = $prodModel->getNovidades(18);
        echo "   Produtos retornados: " . count($novidades) . "\n";
        if (empty($novidades)) {
            echo "   AVISO: Nenhum produto retornado!\n";
            echo "   Possíveis causas:\n";
            echo "   - Produtos não têm estoque > 0\n";
            echo "   - Produtos estão vinculados a categorias tipo banner\n";
            echo "   - Campo recem_adicionado não está sendo salvo corretamente\n";
        } else {
            echo "   Produtos que aparecerão na home:\n";
            foreach ($novidades as $n) {
                echo "   - ID: {$n['id_produto']}, Nome: {$n['nome']}\n";
            }
        }
    } else {
        echo "   ERRO: Método getNovidades() não existe!\n";
    }
    echo "\n";
    
    // 4. Verifica produtos com estoque mas sem flag
    echo "4. Produtos com estoque > 0 mas recem_adicionado = 0:\n";
    $stmt = $conn->query("SELECT id_produto, nome, estoque FROM produto WHERE estoque > 0 AND recem_adicionado = 0 LIMIT 5");
    $produtosSemFlag = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   (mostrando apenas 5 primeiros)\n";
    foreach ($produtosSemFlag as $p) {
        echo "   - ID: {$p['id_produto']}, Nome: {$p['nome']}, Estoque: {$p['estoque']}\n";
    }
    echo "\n";
    
    echo "=== FIM DO DEBUG ===\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>

