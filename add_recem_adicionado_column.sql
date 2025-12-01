-- Script simples para adicionar a coluna recem_adicionado
-- Execute este script no phpMyAdmin ou no MySQL

-- Adiciona coluna recem_adicionado
ALTER TABLE produto 
ADD COLUMN recem_adicionado TINYINT(1) NOT NULL DEFAULT 0 
COMMENT 'Flag para produtos que aparecem na seção Novidades';

-- Adiciona coluna estoque_atualizado_em
ALTER TABLE produto 
ADD COLUMN estoque_atualizado_em DATETIME NULL 
COMMENT 'Timestamp de quando o estoque foi reposto (para seção Voltaram)';

-- Adiciona coluna total_vendas
ALTER TABLE produto 
ADD COLUMN total_vendas INT NOT NULL DEFAULT 0 
COMMENT 'Contador de total de unidades vendidas (para seção Mais Vendidos)';

-- Cria índices para melhorar performance
CREATE INDEX idx_produto_recem_adicionado ON produto(recem_adicionado);
CREATE INDEX idx_produto_estoque_atualizado ON produto(estoque_atualizado_em);
CREATE INDEX idx_produto_total_vendas ON produto(total_vendas);

-- Mensagem de sucesso
SELECT 'Colunas adicionadas com sucesso!' AS resultado;

