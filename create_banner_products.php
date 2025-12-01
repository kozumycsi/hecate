<?php
/**
 * Script para criar categoria tipo banner e cadastrar os 5 produtos de banner
 * Execute este arquivo uma vez para criar os produtos decorativos
 */

require_once __DIR__ . '/model/CategoryModel.php';
require_once __DIR__ . '/model/ProductModel.php';

try {
    $catModel = new CategoryModel();
    $prodModel = new ProductModel();
    
    // Verifica se a categoria "Banners Decorativos" já existe
    $categorias = $catModel->getCategoriesByType('Categoria Tipo Banner');
    $categoriaBanner = null;
    
    foreach ($categorias as $cat) {
        if (mb_strtolower(trim($cat['nome'])) === 'banners decorativos') {
            $categoriaBanner = $cat;
            break;
        }
    }
    
    // Cria a categoria se não existir
    if (!$categoriaBanner) {
        $catModel->addCategory('Banners Decorativos', 'Categoria Tipo Banner', null, 1);
        $categorias = $catModel->getCategoriesByType('Categoria Tipo Banner');
        foreach ($categorias as $cat) {
            if (mb_strtolower(trim($cat['nome'])) === 'banners decorativos') {
                $categoriaBanner = $cat;
                break;
            }
        }
    }
    
    if (!$categoriaBanner) {
        die("Erro: Não foi possível criar ou encontrar a categoria de banner.\n");
    }
    
    $categoriaId = (int)$categoriaBanner['id_categoria'];
    
    // Define os 5 produtos de banner baseados nas imagens
    $banners = [
        [
            'nome' => 'Banner Corsets',
            'descricao' => 'Banner decorativo - Corsets',
            'imagem' => 'img/banner1.png',
            'preco' => 0.01, // Preço simbólico (não será exibido)
            'estoque' => 0 // Sem estoque para não aparecer como produto vendável
        ],
        [
            'nome' => 'Banner Sapatos',
            'descricao' => 'Banner decorativo - Sapatos',
            'imagem' => 'img/banner2.png',
            'preco' => 0.01,
            'estoque' => 0
        ],
        [
            'nome' => 'Banner Presente',
            'descricao' => 'Banner decorativo - Presente',
            'imagem' => 'img/banner3.png',
            'preco' => 0.01,
            'estoque' => 0
        ],
        [
            'nome' => 'Banner Vestidos',
            'descricao' => 'Banner decorativo - Vestidos',
            'imagem' => 'img/banner4.png',
            'preco' => 0.01,
            'estoque' => 0
        ],
        [
            'nome' => 'Banner Acessórios',
            'descricao' => 'Banner decorativo - Acessórios',
            'imagem' => 'img/banner5.png',
            'preco' => 0.01,
            'estoque' => 0
        ]
    ];
    
    // Verifica quais produtos já existem
    $produtosExistentes = $prodModel->getProductsByBannerCategory($categoriaId, 10);
    $nomesExistentes = [];
    foreach ($produtosExistentes as $p) {
        $nomesExistentes[] = mb_strtolower(trim($p['nome']));
    }
    
    // Cadastra os produtos que ainda não existem
    $produtosCriados = 0;
    foreach ($banners as $banner) {
        if (!in_array(mb_strtolower(trim($banner['nome'])), $nomesExistentes)) {
            // Primeiro, precisa criar o produto em uma categoria normal (não pode ser categoria tipo banner diretamente)
            // Vamos usar uma categoria temporária ou a primeira categoria principal disponível
            $categoriasPrincipais = $catModel->getCategoriesByType('Categoria Principal');
            $categoriaTemp = !empty($categoriasPrincipais) ? (int)$categoriasPrincipais[0]['id_categoria'] : null;
            
            if (!$categoriaTemp) {
                echo "Aviso: Não foi possível encontrar uma categoria principal para criar o produto {$banner['nome']}.\n";
                continue;
            }
            
            $produtoId = $prodModel->addProduct(
                $banner['nome'],
                $banner['descricao'],
                $banner['preco'],
                $categoriaTemp,
                $banner['estoque'],
                $banner['imagem'],
                null
            );
            
            if ($produtoId) {
                // Vincula o produto à categoria de banner
                $prodModel->setProductBannerCategories($produtoId, [$categoriaId]);
                $produtosCriados++;
                echo "Produto criado: {$banner['nome']} (ID: $produtoId)\n";
            } else {
                echo "Erro ao criar produto: {$banner['nome']}\n";
            }
        } else {
            echo "Produto já existe: {$banner['nome']}\n";
        }
    }
    
    echo "\nProcesso concluído! $produtosCriados produto(s) criado(s).\n";
    echo "Categoria de banner ID: $categoriaId\n";
    
} catch (Exception $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
?>

