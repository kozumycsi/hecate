-- Migration: criar tabela de carrinho de compras por usuário
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)

CREATE TABLE IF NOT EXISTS carrinho (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_produto INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  tamanho VARCHAR(50) NULL,
  cor VARCHAR(50) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_product_size_color (id_usuario, id_produto, tamanho, cor),
  KEY idx_carrinho_usuario (id_usuario),
  KEY idx_carrinho_produto (id_produto),
  CONSTRAINT fk_carrinho_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(idusuario) ON DELETE CASCADE,
  CONSTRAINT fk_carrinho_produto FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

