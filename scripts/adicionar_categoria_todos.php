<?php
/**
 * Script para adicionar a categoria "Todos" a todos os produtos existentes
 * Execute este script uma vez para atualizar todos os produtos já cadastrados
 */

require_once __DIR__ . '/../service/conexao.php';
require_once __DIR__ . '/../model/CategoryModel.php';
require_once __DIR__ . '/../model/ProductModel.php';

$db = new UsePDO();
$conn = $db->getInstance();

$categoryModel = new CategoryModel();
$productModel = new ProductModel();

// Busca a categoria "Todos"
$todosCategory = $categoryModel->getCategoryByName('Todos');

if (!$todosCategory) {
    die("ERRO: A categoria 'Todos' não foi encontrada no banco de dados. Por favor, crie-a primeiro.\n");
}

$todosId = (int)$todosCategory['id_categoria'];
echo "Categoria 'Todos' encontrada (ID: $todosId)\n\n";

// Verifica se a tabela produto_categoria existe
$checkTable = $conn->query("SHOW TABLES LIKE 'produto_categoria'");
$tabelaExiste = $checkTable->rowCount() > 0;

if (!$tabelaExiste) {
    die("ERRO: A tabela 'produto_categoria' não existe. Execute as migrations primeiro.\n");
}

// Busca todos os produtos
$stmt = $conn->query("SELECT id_produto FROM produto ORDER BY id_produto");
$produtos = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($produtos)) {
    die("Nenhum produto encontrado no banco de dados.\n");
}

echo "Encontrados " . count($produtos) . " produto(s)\n\n";

$adicionados = 0;
$jaTinham = 0;
$erros = 0;

foreach ($produtos as $produtoId) {
    $produtoId = (int)$produtoId;
    
    // Verifica se o produto já tem a categoria "Todos"
    $stmt = $conn->prepare("SELECT COUNT(*) FROM produto_categoria WHERE id_produto = ? AND id_categoria = ?");
    $stmt->execute([$produtoId, $todosId]);
    $jaTem = (int)$stmt->fetchColumn() > 0;
    
    if ($jaTem) {
        $jaTinham++;
        continue;
    }
    
    // Busca as categorias atuais do produto
    $categoriasAtuais = $productModel->getProductCategories($produtoId);
    $categoriaIds = array_map(function($cat) {
        return (int)$cat['id_categoria'];
    }, $categoriasAtuais);
    
    // Adiciona a categoria "Todos" à lista
    if (!in_array($todosId, $categoriaIds, true)) {
        $categoriaIds[] = $todosId;
    }
    
    // Atualiza as categorias do produto
    if ($productModel->setProductCategories($produtoId, $categoriaIds, null)) {
        $adicionados++;
        echo "✓ Produto ID $produtoId: categoria 'Todos' adicionada\n";
    } else {
        $erros++;
        echo "✗ ERRO ao adicionar categoria 'Todos' ao produto ID $produtoId\n";
    }
}

echo "\n";
echo "=== RESUMO ===\n";
echo "Produtos processados: " . count($produtos) . "\n";
echo "Categoria 'Todos' adicionada: $adicionados\n";
echo "Já tinham a categoria: $jaTinham\n";
echo "Erros: $erros\n";
echo "\nScript concluído!\n";

