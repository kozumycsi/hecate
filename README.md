# 🛍️ Hecate - E-commerce de Roupas Alternativas

Sistema completo de e-commerce desenvolvido em PHP para venda de roupas alternativas, com painel administrativo, carrinho de compras, sistema de pedidos, banners promocionais e muito mais.

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Funcionalidades](#funcionalidades)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Banco de Dados](#banco-de-dados)
- [Como Usar](#como-usar)
- [Deploy](#deploy)
- [Troubleshooting](#troubleshooting)
- [Estrutura de Arquivos Principais](#estrutura-de-arquivos-principais)

---

## 🎯 Sobre o Projeto

**Hecate** é uma plataforma de e-commerce completa desenvolvida para a venda de roupas alternativas. O sistema oferece uma experiência completa tanto para clientes quanto para administradores, incluindo:

- Catálogo de produtos com múltiplas categorias
- Sistema de carrinho de compras persistente
- Processo de checkout e finalização de pedidos
- Painel administrativo completo
- Sistema de banners promocionais
- Gestão de estoque
- Relatórios e estatísticas
- Sistema de favoritos
- Perfil de usuário com upload de foto

---

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP 7.4+** - Linguagem principal
- **MySQL/MariaDB** - Banco de dados
- **PDO** - Conexão com banco de dados (principal)
- **MySQLi** - Conexão alternativa para algumas funcionalidades

### Frontend
- **HTML5** - Estrutura
- **CSS3** - Estilização
- **JavaScript (Vanilla)** - Interatividade
- **Bootstrap 4.5.2** - Framework CSS
- **Font Awesome 5.15.4** - Ícones
- **Google Fonts (Arimo)** - Tipografia

### Arquitetura
- **MVC (Model-View-Controller)** - Padrão arquitetural
- **Sessões PHP** - Autenticação e estado do usuário
- **AJAX/Fetch API** - Requisições assíncronas

---

## ✨ Funcionalidades

### 👤 Área do Cliente

#### Navegação e Busca
- ✅ Página inicial com seções automáticas (Novidades, Voltaram, Mais Vendidos)
- ✅ Busca de produtos
- ✅ Navegação por categorias principais e subcategorias
- ✅ Visualização de produtos por categoria
- ✅ Página de detalhes do produto (`pgdec.php`)
- ✅ Produtos relacionados

#### Autenticação
- ✅ Cadastro de usuário
- ✅ Login/Logout
- ✅ Recuperação de senha por código
- ✅ Perfil de usuário com foto
- ✅ Edição de dados pessoais

#### Compras
- ✅ Carrinho de compras persistente (salvo no banco)
- ✅ Adicionar/remover produtos do carrinho
- ✅ Atualizar quantidades
- ✅ Seleção de tamanhos e cores
- ✅ Checkout completo
- ✅ Finalização de pedidos
- ✅ Histórico de pedidos (`meus-pedidos.php`)
- ✅ Rastreamento de pedidos

#### Outros
- ✅ Sistema de favoritos
- ✅ Contador de itens no carrinho
- ✅ Contador de favoritos

### 🔐 Área Administrativa

#### Dashboard (`paineladm.php`)
- ✅ Indicadores gerais (usuários, produtos, categorias, pedidos)
- ✅ Lista de produtos recentes
- ✅ Navegação rápida para todas as seções

#### Gestão de Produtos (`produtosadm.php`)
- ✅ Cadastro de produtos
- ✅ Edição de produtos
- ✅ Exclusão de produtos
- ✅ Upload de imagens
- ✅ Gestão de estoque
- ✅ Múltiplas categorias por produto
- ✅ Categoria principal
- ✅ Flag "Recém Adicionado"
- ✅ Tamanhos e cores
- ✅ Preços e descrições
- ✅ Lista de produtos sem estoque

#### Gestão de Categorias (`category.php`)
- ✅ Criação de categorias
- ✅ Edição de categorias
- ✅ Exclusão de categorias (com verificação de produtos vinculados)
- ✅ Tipos de categoria (Principal, Subcategoria, Banner)
- ✅ Ordenação de categorias
- ✅ Ativação/desativação

#### Gestão de Banners (`bannersadm.php`)
- ✅ Criação de banners
- ✅ Upload de imagens de banner
- ✅ Tipos de banner (Divulgação, Decoração)
- ✅ Vinculação de múltiplas categorias
- ✅ Vinculação de produtos específicos
- ✅ Ativação/desativação
- ✅ Páginas de exibição de banners (`banner-produtos.php`, `banner-landing.php`)

#### Gestão de Pedidos (`pedidosadm.php`)
- ✅ Lista de todos os pedidos
- ✅ Detalhes do pedido
- ✅ Status do pedido
- ✅ Informações do cliente

#### Relatórios (`relatorios.php`)
- ✅ Estatísticas de vendas
- ✅ Produtos mais vendidos
- ✅ Relatórios por período

#### Configurações (`configuracoes.php`)
- ✅ Configurações gerais do sistema

---

## 📁 Estrutura do Projeto

```
hecate/
├── components/              # Componentes reutilizáveis
│   ├── navbar.php          # Barra de navegação principal
│   └── user-profile.php    # Dropdown do perfil do usuário
│
├── controller/             # Controladores (lógica de negócio)
│   ├── BannerController.php
│   ├── CadastroController.php
│   ├── CartController.php
│   ├── CategoryController.php
│   ├── FavoriteController.php
│   ├── LoginController.php
│   ├── LogoutController.php
│   ├── NovaSenhaController.php
│   ├── PedidoController.php
│   ├── ProductController.php
│   ├── ProfileController.php
│   ├── RecuperarSenhaController.php
│   └── VerificarCodigoController.php
│
├── model/                  # Modelos (acesso ao banco de dados)
│   ├── BannerModel.php
│   ├── CadastroModel.php
│   ├── CartModel.php
│   ├── CategoryModel.php
│   ├── FavoriteModel.php
│   ├── LoginModel.php
│   ├── PedidoModel.php
│   ├── ProductModel.php
│   ├── ProfileModel.php
│   ├── RecuperarSenhaModel.php
│   └── RelatorioModel.php
│
├── service/                # Serviços e utilitários
│   ├── conexao.php         # Conexão PDO principal
│   ├── conexaodash.php     # Conexão MySQLi para dashboard
│   ├── conexaologin.php    # Conexão MySQLi para login
│   ├── funcoes.php         # Funções utilitárias
│   ├── funcoesdash.php     # Funções do dashboard
│   └── path_helper.php     # Helpers de URLs e caminhos
│
├── database/               # Migrations e scripts SQL
│   └── migrations/
│       ├── 20250115_create_banner_tables.sql
│       ├── 20250116_add_product_auto_sections.sql
│       ├── 20250116_sync_total_vendas.sql
│       ├── 20250117_create_carrinho_table.sql
│       ├── 20251106_add_produto_banner_categoria.sql
│       ├── 20251106_alter_categoria_add_ativo_sort.sql
│       ├── 20251129_add_produto_categoria.sql
│       └── README_MULTIPLAS_CATEGORIAS.md
│
├── css/                    # Arquivos CSS
│   └── user-profile.css
│
├── js/                     # Arquivos JavaScript
│   └── favorites.js
│
├── img/                    # Imagens estáticas
│   ├── logo.png
│   ├── avatarfixo.png
│   └── ...
│
├── uploads/                # Uploads de usuários
│   ├── banners/            # Imagens de banners
│   ├── products/           # Imagens de produtos
│   └── profile_pics/       # Fotos de perfil
│
├── scripts/                # Scripts utilitários
│   ├── adicionar_categoria_todos.php
│   └── remove_recem_adicionado_antigos.php
│
├── extras e uteis se necessario/  # Utilitários extras
│   ├── migrate_images.php
│   └── README_PROFILE_IMAGES.md
│
├── index.php               # Página inicial
├── login.php               # Página de login
├── cadastro.php            # Página de cadastro
├── produtos.php            # Lista de produtos
├── pgdec.php               # Detalhes do produto
├── carrinho.php            # Carrinho de compras
├── checkout.php            # Checkout
├── finalizar-compra.php    # Finalização de compra
├── meus-pedidos.php        # Pedidos do usuário
├── perfil.php              # Perfil do usuário
├── favoritos.php           # Favoritos do usuário
├── busca.php               # Busca de produtos
├── paineladm.php           # Dashboard administrativo
├── produtosadm.php         # Gestão de produtos
├── category.php            # Gestão de categorias
├── bannersadm.php          # Gestão de banners
├── pedidosadm.php          # Gestão de pedidos
├── relatorios.php          # Relatórios
├── configuracoes.php       # Configurações
├── produtos-sem-estoque.php # Produtos sem estoque
├── banner-produtos.php      # Página de produtos do banner
├── banner-landing.php      # Landing page do banner
├── style.css               # CSS principal
└── README.md               # Este arquivo
```

---

## 📋 Requisitos

### Servidor
- **PHP 7.4 ou superior**
- **MySQL 5.7+ ou MariaDB 10.2+**
- **Apache** (ou servidor web compatível)
- **Extensões PHP:**
  - `pdo_mysql`
  - `mysqli`
  - `gd` (para processamento de imagens)
  - `mbstring` (para manipulação de strings UTF-8)
  - `session`

### Navegador
- Chrome, Firefox, Edge, Safari (versões recentes)
- JavaScript habilitado

---

## 🚀 Instalação

### 1. Clonar/Baixar o Projeto

```bash
# Se usar Git
git clone [url-do-repositorio]
cd hecate

# Ou baixe e extraia o ZIP
```

### 2. Configurar Servidor Local (XAMPP/WAMP)

1. Copie a pasta do projeto para:
   - **XAMPP:** `C:\xampp\htdocs\hecate`
   - **WAMP:** `C:\wamp64\www\hecate`

2. Inicie o Apache e MySQL no painel de controle

### 3. Criar Banco de Dados

1. Acesse `http://localhost/phpmyadmin`
2. Crie um novo banco de dados chamado `login`
3. Importe a estrutura do banco (veja seção [Banco de Dados](#banco-de-dados))

### 4. Configurar Conexão

Edite os arquivos de conexão com suas credenciais:

**`service/conexao.php`** (PDO):
```php
private $servername = "localhost";
private $username = "root";
private $password = "";
private $dbname = "login";
```

**`service/conexaologin.php`** (MySQLi):
```php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "login";
```

**`service/conexaodash.php`** (MySQLi):
```php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "login";
```

### 5. Executar Migrations

Execute as migrations na ordem:

1. `database/migrations/20250115_create_banner_tables.sql`
2. `database/migrations/20250116_add_product_auto_sections.sql`
3. `database/migrations/20250117_create_carrinho_table.sql`
4. `database/migrations/20251106_add_produto_banner_categoria.sql`
5. `database/migrations/20251106_alter_categoria_add_ativo_sort.sql`
6. `database/migrations/20251129_add_produto_categoria.sql`
7. `database/migrations/20250116_sync_total_vendas.sql` (opcional, sincroniza dados)

### 6. Configurar Permissões

Certifique-se de que a pasta `uploads/` tenha permissão de escrita:

```bash
# Linux/Mac
chmod -R 755 uploads/

# Windows: Verifique as permissões da pasta
```

### 7. Acessar o Sistema

- **Frontend:** `http://localhost/hecate/index.php`
- **Admin:** `http://localhost/hecate/paineladm.php` (após criar usuário admin)

---

## ⚙️ Configuração

### Configuração de Charset

O sistema está configurado para usar **UTF-8 (utf8mb4)** em todas as conexões:

- **PDO:** `charset=utf8mb4` + `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci`
- **MySQLi:** `set_charset("utf8mb4")`

### Configuração de Queries Grandes

Para hospedagens como InfinityFree, o sistema já está configurado com:

```sql
SET SQL_BIG_SELECTS = 1
```

Isso permite queries com múltiplos JOINs sem erro de `MAX_JOIN_SIZE`.

### Configuração de Erros

**Desenvolvimento:**
Os arquivos principais têm exibição de erros ativada:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

**Produção:**
Remova ou comente essas linhas e use apenas logs:
```php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
```

### Helpers de URL

O sistema usa `path_helper.php` para gerenciar URLs:

- `asset_url($path)` - URLs para assets (CSS, JS, imagens)
- `url_to($path)` - URLs para páginas internas
- `resolve_asset_path($path)` - Resolve caminhos de imagens

---

## 🗄️ Banco de Dados

### Tabelas Principais

#### `usuario`
Armazena dados dos usuários (clientes e administradores).

**Campos principais:**
- `idusuario` (PK)
- `nome`
- `email`
- `senha` (hash)
- `is_admin` (TINYINT)
- `foto_perfil`
- `created_at`

#### `produto`
Armazena informações dos produtos.

**Campos principais:**
- `id_produto` (PK)
- `nome`
- `descricao`
- `preco`
- `estoque`
- `imagem`
- `tamanhos` (JSON)
- `recem_adicionado` (TINYINT)
- `estoque_atualizado_em` (DATETIME)
- `total_vendas` (INT)
- `categoria` (legado, mantido para compatibilidade)

#### `categoria`
Armazena categorias de produtos.

**Campos principais:**
- `id_categoria` (PK)
- `nome`
- `tipo` (ENUM: 'Categoria Principal', 'Subcategoria', 'Banner')
- `ativo` (TINYINT)
- `sort_order` (INT)

#### `produto_categoria`
Tabela de relacionamento N:N entre produtos e categorias.

**Campos:**
- `id_produto` (FK)
- `id_categoria` (FK)
- `principal` (TINYINT) - Indica categoria principal

#### `carrinho`
Carrinho de compras persistente por usuário.

**Campos principais:**
- `id` (PK)
- `id_usuario` (FK)
- `id_produto` (FK)
- `quantidade`
- `tamanho`
- `cor`
- `created_at`
- `updated_at`

#### `pedido`
Armazena pedidos realizados.

**Campos principais:**
- `id_pedido` (PK)
- `id_usuario` (FK)
- `data_pedido`
- `status`
- `total`
- `endereco_entrega`

#### `item_do_pedido`
Itens de cada pedido.

**Campos principais:**
- `id_item` (PK)
- `id_pedido` (FK)
- `id_produto` (FK)
- `quantidade`
- `preco_unitario`
- `tamanho`
- `cor`

#### `banner`
Banners promocionais.

**Campos principais:**
- `id_banner` (PK)
- `titulo`
- `imagem`
- `tipo_banner` (ENUM: 'divulgacao', 'decoracao')
- `ativo` (TINYINT)

#### `banner_categoria`
Relacionamento N:N entre banners e categorias.

#### `banner_produto`
Relacionamento N:N entre banners de divulgação e produtos.

#### `favorito`
Produtos favoritados pelos usuários.

**Campos:**
- `id` (PK)
- `id_usuario` (FK)
- `id_produto` (FK)
- `created_at`

#### `codigo`
Códigos de recuperação de senha.

**Campos:**
- `id` (PK)
- `email`
- `codigo`
- `expiracao`
- `usado` (TINYINT)

### Relacionamentos

```
usuario (1) ──< carrinho
usuario (1) ──< pedido
usuario (1) ──< favorito
produto (N) >──< categoria (N) [via produto_categoria]
produto (1) ──< carrinho
produto (1) ──< item_do_pedido
pedido (1) ──< item_do_pedido
banner (N) >──< categoria (N) [via banner_categoria]
banner (N) >──< produto (N) [via banner_produto]
```

---

## 📖 Como Usar

### Para Clientes

1. **Cadastro/Login:**
   - Acesse `cadastro.php` para criar conta
   - Ou `login.php` para fazer login

2. **Navegar Produtos:**
   - Use o menu de categorias na navbar
   - Ou busque produtos em `busca.php`

3. **Adicionar ao Carrinho:**
   - Na página do produto (`pgdec.php`), selecione tamanho/cor
   - Clique em "Adicionar ao Carrinho"
   - O carrinho é salvo automaticamente

4. **Finalizar Compra:**
   - Acesse `carrinho.php`
   - Revise os itens
   - Clique em "Finalizar Compra"
   - Preencha os dados de entrega em `checkout.php`
   - Confirme o pedido

5. **Acompanhar Pedidos:**
   - Acesse `meus-pedidos.php`
   - Veja o histórico e detalhes dos pedidos

### Para Administradores

1. **Acessar Painel:**
   - Faça login como administrador
   - Acesse `paineladm.php`

2. **Gerenciar Produtos:**
   - Vá em "Produtos" no menu lateral
   - Clique em "Adicionar" para novo produto
   - Preencha nome, descrição, preço, estoque
   - Selecione categorias (múltiplas)
   - Marque "Recém Adicionado" se desejar
   - Faça upload da imagem
   - Salve

3. **Gerenciar Categorias:**
   - Vá em "Categorias"
   - Crie categorias principais e subcategorias
   - Organize a hierarquia

4. **Gerenciar Banners:**
   - Vá em "Banners"
   - Crie banners de divulgação ou decoração
   - Vincule categorias ou produtos específicos
   - Ative/desative conforme necessário

5. **Visualizar Pedidos:**
   - Vá em "Pedidos"
   - Veja todos os pedidos
   - Acesse detalhes de cada pedido

6. **Relatórios:**
   - Vá em "Relatórios"
   - Visualize estatísticas de vendas
   - Analise produtos mais vendidos

---

## 🌐 Deploy

### InfinityFree (Recomendado)

O projeto está otimizado para InfinityFree:

1. **Estrutura de Pastas:**
   - Todos os arquivos devem estar na raiz pública (`htdocs/`)
   - Não use subpastas como `/view/`

2. **Configuração de Banco:**
   - Use as credenciais fornecidas pelo InfinityFree
   - Atualize `service/conexao.php`, `conexaologin.php`, `conexaodash.php`

3. **Upload de Arquivos:**
   - Faça upload via FTP ou File Manager
   - Certifique-se de que `uploads/` tenha permissão de escrita

4. **Configurações Especiais:**
   - O sistema já está configurado com `SET SQL_BIG_SELECTS = 1`
   - Charset UTF-8 configurado corretamente

5. **URLs:**
   - Use URLs relativas (já configuradas via `path_helper.php`)
   - Não use caminhos absolutos

### Outras Hospedagens

1. **Configurar Banco de Dados:**
   - Atualize credenciais nos arquivos de conexão

2. **Permissões:**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/banners/
   chmod 755 uploads/products/
   chmod 755 uploads/profile_pics/
   ```

3. **PHP.ini:**
   - Verifique `upload_max_filesize` e `post_max_size`
   - Recomendado: `upload_max_filesize = 10M`

4. **.htaccess (Opcional):**
   ```apache
   # Proteger arquivos sensíveis
   <FilesMatch "\.(sql|md|log)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

---

## 🔧 Troubleshooting

### Erro 500 (Página em Branco)

**Solução:**
1. Ative exibição de erros temporariamente:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Verifique logs do servidor
3. Verifique sintaxe PHP: `php -l arquivo.php`
4. Confirme que todas as classes estão sendo carregadas

### Erro de Conexão com Banco

**Solução:**
1. Verifique se MySQL está rodando
2. Confirme credenciais nos arquivos de conexão
3. Teste conexão manualmente:
   ```php
   $conn = new mysqli("localhost", "root", "", "login");
   if ($conn->connect_error) {
       die("Erro: " . $conn->connect_error);
   }
   ```

### Imagens Não Aparecem

**Solução:**
1. Verifique se `uploads/` tem permissão de escrita
2. Confirme que `resolve_asset_path()` está sendo usado
3. Verifique caminhos no banco de dados
4. Use caminhos relativos, não absolutos

### Erro SQL: MAX_JOIN_SIZE

**Solução:**
Já está configurado automaticamente com `SET SQL_BIG_SELECTS = 1` em todas as conexões.

### Caracteres Especiais com Pontos de Interrogação

**Solução:**
1. Confirme que o banco usa `utf8mb4`
2. Verifique se as conexões estão configuradas:
   - PDO: `charset=utf8mb4`
   - MySQLi: `set_charset("utf8mb4")`
3. Verifique meta tag HTML: `<meta charset="UTF-8">`

### Carrinho Não Salva

**Solução:**
1. Verifique se a tabela `carrinho` existe
2. Execute a migration: `database/migrations/20250117_create_carrinho_table.sql`
3. Confirme que o usuário está logado (sessão ativa)

### Dropdown do Perfil Não Abre

**Solução:**
1. Verifique se jQuery e Bootstrap JS estão carregados
2. Adicione no final do body:
   ```html
   <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   ```

### AJAX Retorna HTML em vez de JSON

**Solução:**
1. Verifique se o caminho do controller está correto
2. Confirme que não há erros PHP antes do JSON
3. Use `header('Content-Type: application/json')` no controller
4. Verifique se não há `echo` ou `print` antes do JSON

---

## 📄 Estrutura de Arquivos Principais

### Controllers

- **`ProductController.php`** - Gerencia produtos (CRUD, busca, filtros)
- **`CartController.php`** - Gerencia carrinho (adicionar, remover, atualizar)
- **`PedidoController.php`** - Gerencia pedidos (criar, listar, detalhes)
- **`CategoryController.php`** - Gerencia categorias
- **`BannerController.php`** - Gerencia banners
- **`LoginController.php`** - Autenticação
- **`ProfileController.php`** - Perfil do usuário

### Models

- **`ProductModel.php`** - Acesso aos dados de produtos
- **`CartModel.php`** - Acesso aos dados do carrinho
- **`PedidoModel.php`** - Acesso aos dados de pedidos
- **`CategoryModel.php`** - Acesso aos dados de categorias
- **`BannerModel.php`** - Acesso aos dados de banners
- **`LoginModel.php`** - Validação de login
- **`ProfileModel.php`** - Dados do perfil

### Services

- **`conexao.php`** - Classe `UsePDO` para conexão PDO principal
- **`conexaologin.php`** - Conexão MySQLi para login
- **`conexaodash.php`** - Conexão MySQLi para dashboard
- **`path_helper.php`** - Funções helper para URLs e caminhos
- **`funcoes.php`** - Funções utilitárias gerais
- **`funcoesdash.php`** - Funções específicas do dashboard

### Páginas Principais

- **`index.php`** - Homepage com seções automáticas
- **`produtos.php`** - Lista de produtos
- **`pgdec.php`** - Detalhes do produto
- **`carrinho.php`** - Carrinho de compras
- **`checkout.php`** - Checkout
- **`meus-pedidos.php`** - Pedidos do usuário
- **`perfil.php`** - Perfil do usuário
- **`favoritos.php`** - Favoritos
- **`busca.php`** - Busca de produtos

### Páginas Administrativas

- **`paineladm.php`** - Dashboard
- **`produtosadm.php`** - Gestão de produtos
- **`category.php`** - Gestão de categorias
- **`bannersadm.php`** - Gestão de banners
- **`pedidosadm.php`** - Gestão de pedidos
- **`relatorios.php`** - Relatórios
- **`configuracoes.php`** - Configurações

---

## 📝 Notas Importantes

### Seções Automáticas

O sistema possui seções automáticas na homepage:

- **Novidades:** Produtos com `recem_adicionado = 1`
- **Voltaram:** Produtos com estoque reposto nos últimos 7 dias
- **Mais Vendidos:** Produtos ordenados por `total_vendas`

### Múltiplas Categorias

Um produto pode ter múltiplas categorias através da tabela `produto_categoria`. Uma categoria pode ser marcada como "principal" para exibição.

### Carrinho Persistente

O carrinho é salvo no banco de dados, permitindo que o usuário acesse de qualquer dispositivo após fazer login.

### Sistema de Banners

Banners podem ser:
- **Divulgação:** Vinculados a categorias ou produtos específicos
- **Decoração:** Apenas visuais, sem vínculo

### Compatibilidade

O sistema mantém compatibilidade com:
- Sistema legado de categoria única (`produto.categoria`)
- Migração gradual para múltiplas categorias
- Fallback automático quando tabelas não existem

---

## 📞 Suporte

Para problemas ou dúvidas:

1. Verifique a seção [Troubleshooting](#troubleshooting)
2. Revise os logs de erro do PHP/Apache
3. Verifique a documentação das migrations em `database/migrations/`

---

## 📜 Licença

Este projeto é proprietário. Todos os direitos reservados.

---

## 🎉 Créditos

Desenvolvido para **Hecate - Roupas Alternativas**.

**Versão:** 1.0  
**Última Atualização:** Janeiro 2025

---

**Desenvolvido com ❤️ para uma experiência de compra excepcional!**

