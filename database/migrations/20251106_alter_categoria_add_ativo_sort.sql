-- Migration: adicionar campos de status e ordenação à tabela categoria
-- Execute este arquivo manualmente no seu banco (MySQL/MariaDB)

ALTER TABLE categoria
  ADD COLUMN IF NOT EXISTS ativo TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS sort_order INT NULL DEFAULT NULL;

-- Opcional: inicializar sort_order sequencialmente por id (ajuste conforme necessidade)
-- UPDATE categoria SET sort_order = id_categoria WHERE sort_order IS NULL;
