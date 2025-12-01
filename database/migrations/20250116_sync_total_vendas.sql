-- Script para sincronizar total_vendas dos produtos baseado nas vendas reais
-- Execute este script após criar os campos da migration anterior

-- Atualiza total_vendas de todos os produtos baseado nas vendas reais
UPDATE produto p
SET p.total_vendas = (
    SELECT COALESCE(SUM(ip.quantidade), 0)
    FROM item_do_pedido ip
    INNER JOIN pedido ped ON ip.id_pedido = ped.id_pedido
    WHERE ip.id_produto = p.id_produto
      AND ped.status != 'Cancelado'
)
WHERE EXISTS (
    SELECT 1
    FROM item_do_pedido ip2
    INNER JOIN pedido ped2 ON ip2.id_pedido = ped2.id_pedido
    WHERE ip2.id_produto = p.id_produto
      AND ped2.status != 'Cancelado'
);

-- Define total_vendas como 0 para produtos que nunca foram vendidos
UPDATE produto
SET total_vendas = 0
WHERE total_vendas IS NULL OR total_vendas = 0;

