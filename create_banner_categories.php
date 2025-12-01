<?php
/**
 * Script para criar as categorias tipo banner necessárias
 * Execute este arquivo uma vez para criar as categorias
 */

require_once __DIR__ . '/model/CategoryModel.php';

try {
    $catModel = new CategoryModel();
    
    // Lista de categorias tipo banner a criar
    $categorias = [
        'Banners de Divulgação' => 'Banners clicáveis para o carousel principal (embaixo da navbar)',
        'Banners Decorativos' => 'Banners não clicáveis que aparecem a cada duas fileiras de produtos'
    ];
    
    // Verifica quais já existem
    $categoriasExistentes = $catModel->getCategoriesByType('Categoria Tipo Banner');
    $nomesExistentes = [];
    foreach ($categoriasExistentes as $cat) {
        $nomesExistentes[] = mb_strtolower(trim($cat['nome']));
    }
    
    $criadas = 0;
    foreach ($categorias as $nome => $descricao) {
        if (!in_array(mb_strtolower(trim($nome)), $nomesExistentes)) {
            $result = $catModel->addCategory($nome, 'Categoria Tipo Banner', null, 1);
            if ($result) {
                $criadas++;
                echo "✓ Categoria criada: $nome\n";
            } else {
                echo "✗ Erro ao criar categoria: $nome\n";
            }
        } else {
            echo "→ Categoria já existe: $nome\n";
        }
    }
    
    echo "\nProcesso concluído! $criadas categoria(s) criada(s).\n";
    echo "\nInstruções:\n";
    echo "1. Banners de Divulgação: Use para banners clicáveis no carousel principal\n";
    echo "   - Preencha o campo URL ao cadastrar\n";
    echo "   - Aparecem no carousel embaixo da navbar\n\n";
    echo "2. Banners Decorativos: Use para banners não clicáveis entre fileiras\n";
    echo "   - Deixe o campo URL vazio ao cadastrar\n";
    echo "   - Aparecem a cada duas fileiras de produtos (subcategorias)\n";
    
} catch (Exception $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
?>

