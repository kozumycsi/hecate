-- Migration: criar tabela de vínculo entre produtos e múltiplas categorias
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)

-- Tabela para relacionamento muitos-para-muitos entre produtos e categorias
CREATE TABLE IF NOT EXISTS produto_categoria (
  id_produto INT NOT NULL,
  id_categoria INT NOT NULL,
  principal TINYINT(1) DEFAULT 0 COMMENT 'Indica se é a categoria principal do produto',
  PRIMARY KEY (id_produto, id_categoria),
  KEY idx_pc_categoria (id_categoria),
  KEY idx_pc_principal (principal),
  CONSTRAINT fk_pc_produto FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE,
  CONSTRAINT fk_pc_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrar dados existentes da coluna produto.categoria para a nova tabela
-- Apenas se a coluna categoria ainda existir
INSERT INTO produto_categoria (id_produto, id_categoria, principal)
SELECT id_produto, categoria, 1
FROM produto
WHERE categoria IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM produto_categoria pc 
    WHERE pc.id_produto = produto.id_produto 
    AND pc.id_categoria = produto.categoria
  );

-- Observação: 
-- A coluna produto.categoria será mantida por enquanto para compatibilidade,
-- mas o sistema priorizará a tabela produto_categoria para produtos com múltiplas categorias.
-- Futuramente, a coluna produto.categoria pode ser removida se desejado.
