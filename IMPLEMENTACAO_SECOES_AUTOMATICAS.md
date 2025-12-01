# Implementação de Seções Automáticas (Novidades, Voltaram, Mais Vendidos)

## Resumo das Mudanças

As categorias "Novidades", "Voltaram" e "Mais Vendidos" foram transformadas em regras automáticas baseadas em atributos dos produtos, removendo a necessidade de categorias reais no banco de dados.

## Arquivos Modificados

### 1. Migrations
- `database/migrations/20250116_add_product_auto_sections.sql` - Adiciona os novos campos na tabela produto
- `database/migrations/20250116_sync_total_vendas.sql` - Sincroniza total_vendas dos produtos existentes
- `database/migrations/20250116_remove_old_category_sections.sql` - Script opcional para remover categorias antigas

### 2. Models
- `model/ProductModel.php` - Adicionados métodos:
  - `getNovidades()` - Busca produtos com flag recem_adicionado
  - `getVoltaram()` - Busca produtos com estoque reposto recentemente
  - `getMaisVendidos()` - Busca produtos ordenados por vendas
  - `updateTotalVendas()` - Atualiza contador de vendas
  - `removeRecemAdicionadoAntigos()` - Remove flag após X dias

- `model/PedidoModel.php` - Adicionada lógica para atualizar total_vendas quando pedido é confirmado

### 3. Controllers
- `controller/ProductController.php` - Processa o campo recem_adicionado no formulário

### 4. Views
- `view/produtosadm.php` - Adicionado checkbox "Recém Adicionado" e exibição de estatísticas
- `index.php` - Atualizado para usar os novos métodos de busca
- `view/index.php` - Atualizado para usar os novos métodos de busca

### 5. Scripts
- `scripts/remove_recem_adicionado_antigos.php` - Script para remover flag de produtos antigos (executar via cron)

## Novos Campos na Tabela `produto`

1. **recem_adicionado** (TINYINT(1))
   - Flag para marcar produtos que aparecem na seção "Novidades"
   - Padrão: 0
   - Pode ser marcado manualmente no dashboard

2. **estoque_atualizado_em** (DATETIME)
   - Timestamp de quando o estoque foi reposto (de 0 para > 0)
   - Atualizado automaticamente pelo sistema
   - Usado para a seção "Voltaram"

3. **total_vendas** (INT)
   - Contador de total de unidades vendidas
   - Atualizado automaticamente quando pedidos são confirmados
   - Usado para a seção "Mais Vendidos"

## Como Funciona

### Novidades
- Produtos com `recem_adicionado = 1` aparecem automaticamente na seção "Novidades"
- Pode ser marcado no formulário de criação/edição de produto
- Recomendado: executar `scripts/remove_recem_adicionado_antigos.php` periodicamente (ex: 30 dias)

### Voltaram
- Produtos com `estoque > 0` e `estoque_atualizado_em` nos últimos 7 dias aparecem na seção "Voltaram"
- O campo `estoque_atualizado_em` é atualizado automaticamente quando o estoque muda de 0 para um valor maior
- Período configurável no método `getVoltaram()`

### Mais Vendidos
- Produtos ordenados por `total_vendas` (maior para menor)
- O campo `total_vendas` é atualizado automaticamente quando pedidos são confirmados
- Se o campo não existir, calcula dinamicamente da tabela `item_do_pedido`

## Instalação

1. Execute a migration principal:
   ```sql
   source database/migrations/20250116_add_product_auto_sections.sql
   ```

2. Sincronize os dados existentes:
   ```sql
   source database/migrations/20250116_sync_total_vendas.sql
   ```

3. (Opcional) Remova as categorias antigas:
   - Use o dashboard para mover produtos para outras categorias
   - Depois execute: `database/migrations/20250116_remove_old_category_sections.sql`

4. Configure cron job (opcional) para limpar flags antigos:
   ```bash
   # Executar diariamente às 2h da manhã
   0 2 * * * php /caminho/para/scripts/remove_recem_adicionado_antigos.php 30
   ```

## Notas Importantes

- As categorias antigas "Novidades", "Voltaram" e "Mais Vendidos" não são mais necessárias
- O sistema ignora essas categorias automaticamente na home
- Produtos vinculados a essas categorias antigas continuarão funcionando, mas é recomendado migrá-los
- O campo `total_vendas` é atualizado automaticamente, mas pode ser recalculado executando o script de sincronização

## Compatibilidade

- MySQL 5.7+
- MariaDB 10.2+
- PHP 7.4+

