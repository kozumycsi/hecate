-- Script para remover as categorias antigas "Novidades", "Voltaram" e "Mais Vendidos"
-- Execute este script APENAS se você tiver essas categorias cadastradas e quiser removê-las
-- ATENÇÃO: Este script remove as categorias. Certifique-se de que não há produtos vinculados a elas
-- ou que você já moveu os produtos para outras categorias.

-- Descomente as linhas abaixo se quiser executar:

-- DELETE FROM categoria WHERE LOWER(TRIM(nome)) IN ('novidades', 'voltaram', 'mais vendidos') AND tipo = 'Subcategoria';

-- Nota: Se houver produtos vinculados a essas categorias, você precisará:
-- 1. Mover os produtos para outras categorias primeiro
-- 2. Ou usar o sistema de exclusão de categorias do dashboard que já trata dependências

