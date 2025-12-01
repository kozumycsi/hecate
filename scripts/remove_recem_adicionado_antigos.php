<?php
/**
 * Script para remover automaticamente o flag recem_adicionado de produtos antigos
 * Execute este script periodicamente (ex: via cron job) para manter a seção "Novidades" atualizada
 * 
 * Uso: php scripts/remove_recem_adicionado_antigos.php [dias]
 * Exemplo: php scripts/remove_recem_adicionado_antigos.php 30
 */

require_once __DIR__ . '/../model/ProductModel.php';

$dias = isset($argv[1]) ? (int)$argv[1] : 30; // Padrão: 30 dias

try {
    $prodModel = new ProductModel();
    
    if (!method_exists($prodModel, 'removeRecemAdicionadoAntigos')) {
        echo "Erro: Método removeRecemAdicionadoAntigos não encontrado no ProductModel.\n";
        exit(1);
    }
    
    $removidos = $prodModel->removeRecemAdicionadoAntigos($dias);
    
    echo "Processo concluído!\n";
    echo "Produtos removidos da seção 'Novidades': $removidos\n";
    echo "Critério: produtos criados há mais de $dias dias\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>

