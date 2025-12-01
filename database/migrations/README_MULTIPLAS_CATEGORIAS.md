# Implementação de Múltiplas Categorias por Produto

## O que foi implementado

Este sistema permite que um produto seja associado a **múltiplas categorias** simultaneamente, oferecendo maior flexibilidade na organização do catálogo.

### Principais funcionalidades:
- ✅ Seleção de múltiplas categorias por produto (checkboxes)
- ✅ Definição de uma categoria principal para exibição
- ✅ Compatibilidade com o sistema legado (coluna `produto.categoria` mantida)
- ✅ Interface intuitiva com sincronização automática
- ✅ Exibição de todas as categorias na listagem (categoria principal em negrito)

## Como ativar

### 1. Executar a Migration SQL

Acesse seu banco de dados MySQL/MariaDB e execute o arquivo:
```
database/migrations/20251129_add_produto_categoria.sql
```

**Opções para executar:**

#### Via phpMyAdmin:
1. Abra o phpMyAdmin
2. Selecione seu banco de dados
3. Vá em "SQL"
4. Cole o conteúdo do arquivo e execute

#### Via linha de comando:
```bash
mysql -u seu_usuario -p seu_banco < database/migrations/20251129_add_produto_categoria.sql
```

#### Via XAMPP:
1. Abra `http://localhost/phpmyadmin`
2. Selecione o banco de dados do sistema
3. Clique em "SQL"
4. Cole o conteúdo do arquivo
5. Clique em "Executar"

### 2. Verificar a migração

Após executar a migration, verifique se a tabela foi criada:
```sql
SHOW TABLES LIKE 'produto_categoria';
```

Você deve ver a tabela `produto_categoria` listada.

### 3. Verificar migração de dados

A migration automaticamente migra os dados existentes da coluna `produto.categoria` para a nova tabela `produto_categoria`, marcando-as como categoria principal.

Verifique com:
```sql
SELECT * FROM produto_categoria LIMIT 10;
```

## Como usar

### Cadastrar novo produto:
1. Acesse o painel administrativo → Produtos
2. No formulário de cadastro, você verá:
   - **Checkboxes de categorias**: Marque todas as categorias às quais o produto pertence
   - **Select de categoria principal**: Escolha qual será a categoria principal (apenas categorias marcadas aparecem aqui)
3. Salve o produto

### Editar produto existente:
1. Clique em "Editar" no produto desejado
2. As categorias atuais estarão marcadas
3. Adicione ou remova categorias conforme necessário
4. Altere a categoria principal se desejar
5. Salve as alterações

### Visualização:
- Na listagem de produtos, todas as categorias são exibidas
- A categoria principal aparece em **negrito**
- Produtos aparecem em todas as páginas de categorias selecionadas

## Estrutura do Banco

### Tabela `produto_categoria`
```sql
CREATE TABLE produto_categoria (
  id_produto INT NOT NULL,
  id_categoria INT NOT NULL,
  principal TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id_produto, id_categoria)
);
```

**Campos:**
- `id_produto`: ID do produto
- `id_categoria`: ID da categoria
- `principal`: 1 = categoria principal, 0 = categoria secundária

## Compatibilidade

O sistema mantém total compatibilidade com a versão anterior:
- A coluna `produto.categoria` continua existindo
- Novos produtos atualizam ambas (coluna legada + tabela nova)
- Se a tabela `produto_categoria` não existir, o sistema usa o comportamento antigo
- Migração gradual sem quebrar funcionalidades existentes

## Benefícios

1. **Maior flexibilidade**: Produtos podem aparecer em múltiplas categorias
2. **Melhor organização**: Organize seu catálogo de forma mais lógica
3. **SEO melhorado**: Produtos acessíveis por mais caminhos de navegação
4. **Experiência do usuário**: Clientes encontram produtos mais facilmente
5. **Sem perda de dados**: Migração automática dos dados existentes

## Rollback (se necessário)

Se precisar reverter a mudança:

```sql
-- Remove a tabela de múltiplas categorias
DROP TABLE IF EXISTS produto_categoria;

-- O sistema voltará a usar apenas produto.categoria
```

**Nota**: Após o rollback, produtos continuarão funcionando com a categoria legada.

## Suporte

Em caso de dúvidas ou problemas:
1. Verifique se a migration foi executada corretamente
2. Confira os logs do PHP para erros
3. Teste primeiro em ambiente de desenvolvimento
4. Faça backup do banco antes de executar em produção
