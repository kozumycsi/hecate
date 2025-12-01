-- Migration: Adicionar campos para seções automáticas (Novidades, Voltaram, Mais Vendidos)
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)
-- Compatível com MySQL 5.7+ e MariaDB 10.2+

-- Adiciona campo para marcar produtos como "Recém Adicionado" (Novidades)
-- Verifica se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "produto";
SET @columnname = "recem_adicionado";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag para produtos que aparecem na seção Novidades'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adiciona campo para rastrear quando o estoque foi atualizado (Voltaram)
SET @columnname = "estoque_atualizado_em";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " DATETIME NULL COMMENT 'Timestamp de quando o estoque foi reposto (para seção Voltaram)'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adiciona campo para armazenar total de vendas (Mais Vendidos)
SET @columnname = "total_vendas";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT NOT NULL DEFAULT 0 COMMENT 'Contador de total de unidades vendidas (para seção Mais Vendidos)'")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Índices para melhorar performance das consultas (verifica se já existem)
SET @indexname = "idx_produto_recem_adicionado";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, "(recem_adicionado)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

SET @indexname = "idx_produto_estoque_atualizado";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, "(estoque_atualizado_em)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

SET @indexname = "idx_produto_total_vendas";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, "(total_vendas)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Nota: O trigger para atualizar estoque_atualizado_em é implementado via código PHP
-- no método updateProduct() do ProductModel para maior compatibilidade e controle.

