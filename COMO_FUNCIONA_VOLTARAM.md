# Como Funciona a Seção "Voltaram"

## 📋 Visão Geral

A seção "Voltaram" exibe automaticamente produtos que estavam **sem estoque (estoque = 0)** e foram **repostos recentemente** (estoque > 0).

## 🔄 Como Funciona

### 1. **Campo no Banco de Dados**

O sistema usa o campo `estoque_atualizado_em` (DATETIME) na tabela `produto`:
- Este campo armazena a data/hora em que o estoque foi reposto
- É atualizado **automaticamente** pelo sistema

### 2. **Atualização Automática**

O campo `estoque_atualizado_em` é atualizado automaticamente quando:

```
Estoque anterior = 0  →  Estoque novo > 0
```

**Onde isso acontece:**
- No método `updateProduct()` do `ProductModel.php`
- Sempre que você edita um produto no dashboard e o estoque muda de 0 para um valor maior

**Código responsável:**
```php
// Se estoque mudou de 0 para > 0, atualiza estoque_atualizado_em
if ($hasEstoqueAtualizado && $estoqueAnterior === 0 && $estoqueNovo > 0) {
    $updates[] = 'estoque_atualizado_em = NOW()';
}
```

### 3. **Exibição na Home**

A seção "Voltaram" busca produtos que:
- ✅ Têm `estoque > 0` (produto disponível)
- ✅ Têm `estoque_atualizado_em` preenchido (foi reposto)
- ✅ Foram repostos nos **últimos 7 dias** (configurável)
- ✅ Não estão vinculados a categorias tipo banner

**Query SQL usada:**
```sql
SELECT p.*, c.nome AS categoria_nome
FROM produto p
LEFT JOIN categoria c ON p.categoria = c.id_categoria
WHERE p.estoque > 0
  AND p.estoque_atualizado_em IS NOT NULL
  AND p.estoque_atualizado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY p.estoque_atualizado_em DESC
LIMIT 18
```

## 📝 Exemplo Prático

### Cenário 1: Produto sem estoque
1. Produto "Vestido Gótico" tem estoque = 0
2. Campo `estoque_atualizado_em` = NULL
3. **Não aparece** na seção "Voltaram"

### Cenário 2: Reposição de estoque
1. Você edita o produto "Vestido Gótico" no dashboard
2. Altera o estoque de **0 para 10**
3. Sistema **automaticamente** atualiza `estoque_atualizado_em = NOW()`
4. Produto **aparece** na seção "Voltaram" por 7 dias

### Cenário 3: Após 7 dias
1. Passam 7 dias desde a reposição
2. Produto **sai automaticamente** da seção "Voltaram"
3. Mas continua disponível para venda normalmente

## ⚙️ Configurações

### Alterar período de exibição

No arquivo `index.php`, linha ~225, você pode alterar o número de dias:

```php
$voltaram = $prodModel->getVoltaram(7, 18); // Últimos 7 dias, máximo 18 produtos
```

**Parâmetros:**
- Primeiro número (7): Quantos dias atrás considerar
- Segundo número (18): Quantos produtos mostrar no máximo

**Exemplos:**
- `getVoltaram(3, 18)` → Produtos repostos nos últimos 3 dias
- `getVoltaram(14, 24)` → Produtos repostos nos últimos 14 dias, mostrar até 24

## 🎯 Casos de Uso

### ✅ Aparece na seção "Voltaram":
- Produto tinha estoque = 0, você repõe para 5 unidades
- Produto tinha estoque = 0, você repõe para 100 unidades
- Produto foi reposto há 2 dias (dentro do período de 7 dias)

### ❌ NÃO aparece na seção "Voltaram":
- Produto sempre teve estoque > 0 (nunca ficou sem)
- Produto foi reposto há mais de 7 dias
- Produto ainda está com estoque = 0
- Produto está vinculado a categoria tipo banner

## 🔍 Como Verificar

### No Dashboard:
1. Edite um produto
2. Veja o campo "Estoque"
3. Se o produto tinha estoque = 0 e você coloca > 0, o sistema atualiza automaticamente

### Na Página de Teste:
Acesse: `http://localhost/hecate/view/test_recem_adicionado.php`

Ou crie uma página de debug similar para "Voltaram".

## 📊 Resumo

| Ação | Resultado |
|------|-----------|
| Produto com estoque = 0 | `estoque_atualizado_em` = NULL |
| Editar produto: estoque 0 → 5 | `estoque_atualizado_em` = data/hora atual |
| Produto reposto há 2 dias | Aparece na seção "Voltaram" |
| Produto reposto há 10 dias | NÃO aparece (fora do período) |
| Produto sempre teve estoque | NÃO aparece (nunca foi reposto) |

## 💡 Dica

Se você quiser que um produto apareça na seção "Voltaram" manualmente:
1. Coloque o estoque como 0
2. Salve o produto
3. Depois, coloque o estoque > 0 novamente
4. Salve - o sistema atualizará automaticamente o timestamp

