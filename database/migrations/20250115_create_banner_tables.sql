-- Migration: Criar tabelas para sistema de banners com múltiplas categorias e produtos
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)

-- Tabela principal de banners
CREATE TABLE IF NOT EXISTS banner (
  id_banner INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  imagem VARCHAR(500) NOT NULL,
  tipo_banner ENUM('divulgacao', 'decoracao') NOT NULL DEFAULT 'divulgacao',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_banner_tipo (tipo_banner),
  INDEX idx_banner_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de relacionamento N:N entre banners e categorias
CREATE TABLE IF NOT EXISTS banner_categoria (
  id_banner_categoria INT AUTO_INCREMENT PRIMARY KEY,
  id_banner INT NOT NULL,
  id_categoria INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_banner_categoria (id_banner, id_categoria),
  INDEX idx_bc_banner (id_banner),
  INDEX idx_bc_categoria (id_categoria),
  CONSTRAINT fk_bc_banner FOREIGN KEY (id_banner) REFERENCES banner(id_banner) ON DELETE CASCADE,
  CONSTRAINT fk_bc_categoria FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de relacionamento N:N entre banners de divulgação e produtos (com ordem)
CREATE TABLE IF NOT EXISTS banner_produto (
  id_banner_produto INT AUTO_INCREMENT PRIMARY KEY,
  id_banner INT NOT NULL,
  id_produto INT NOT NULL,
  ordem INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_banner_produto (id_banner, id_produto),
  INDEX idx_bp_banner (id_banner),
  INDEX idx_bp_produto (id_produto),
  INDEX idx_bp_ordem (ordem),
  CONSTRAINT fk_bp_banner FOREIGN KEY (id_banner) REFERENCES banner(id_banner) ON DELETE CASCADE,
  CONSTRAINT fk_bp_produto FOREIGN KEY (id_produto) REFERENCES produto(id_produto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

