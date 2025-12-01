-- Atualização do banco de dados para suportar hierarquia de categorias
USE login;

-- Adicionar coluna parent_id se não existir
ALTER TABLE categoria ADD COLUMN IF NOT EXISTS parent_id INT(11) NULL DEFAULT NULL;

-- Adicionar chave estrangeira para parent_id
ALTER TABLE categoria ADD CONSTRAINT fk_categoria_parent 
    FOREIGN KEY (parent_id) REFERENCES categoria(id_categoria) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Modificar a coluna tipo para aceitar valores mais longos
ALTER TABLE categoria MODIFY COLUMN tipo VARCHAR(50) NOT NULL;
