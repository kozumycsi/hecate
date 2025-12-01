-- Migration: criar tabela de vínculo entre produtos e categorias de banner
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)

CREATE TABLE IF NOT EXISTS produto_banner_categoria (
  id_produto INT NOT NULL,
  id_categoria INT NOT NULL,
  PRIMARY KEY (id_produto, id_categoria),
  KEY idx_pbc_categoria (id_categoria),
  CONSTRAINT fk_pbc_produto FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE,
  CONSTRAINT fk_pbc_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Observação:
-- Utilize apenas categorias cujo campo categoria.tipo = 'Categoria Tipo Banner' para este vínculo.
-- O sistema já tratará isso na interface.
