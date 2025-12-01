<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Configurações</title>
    <link rel="stylesheet" href="paineladm.css" />
    <style>
        .header-bar {
            padding: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .blue-bar { background-color: #0056b3; }
        .config-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .config-section h3 {
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        form input[type="text"], form input[type="email"], form input[type="tel"], form textarea, form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .btn-salvar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-salvar:hover {
            background-color: #218838;
        }
        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 3px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #0056b3;
            padding: 15px;
            margin-bottom: 20px;
        }
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .permission-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .permission-item input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="paineladm.php" data-section="indicadores">Indicadores</a>
        <a href="category.php" data-section="categorias">Categorias</a>
        <a href="produtosadm.php" data-section="produtos">Produtos</a>
        <a href="produtos-sem-estoque.php" data-section="produtos-sem-estoque">Produtos sem Estoque</a>
        <a href="bannersadm.php" data-section="banners">Banners</a>
        <a href="pedidosadm.php" data-section="pedidos">Pedidos</a>
        <a href="relatorios.php" data-section="relatorios">Relatórios</a>
        <a href="configuracoes.php" data-section="configuracoes" class="active">Configurações</a>
    </div>

    <div class="main" style="margin-left: 200px; padding: 20px;">
        <div class="header-bar blue-bar">Configurações do Sistema</div>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Informações da Loja -->
        <div class="config-section">
            <h3>Informações da Loja</h3>
            <form method="post" action="../controller/ConfigController.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_store_info" />
                
                <label for="store_name">Nome da Loja</label>
                <input type="text" id="store_name" name="store_name" value="Hecate - Roupas Alternativas" required />

                <label for="store_email">E-mail de Contato</label>
                <input type="email" id="store_email" name="store_email" value="contato@hecate.com.br" required />

                <label for="store_phone">Telefone</label>
                <input type="tel" id="store_phone" name="store_phone" value="(11) 98765-4321" required />

                <label for="store_address">Endereço</label>
                <textarea id="store_address" name="store_address" rows="3">Rua Exemplo, 123 - São Paulo, SP - CEP: 01234-567</textarea>

                <label for="store_description">Descrição da Loja</label>
                <textarea id="store_description" name="store_description" rows="4">Loja especializada em moda alternativa, gótica e dark.</textarea>

                <button type="submit" class="btn-salvar">Salvar Informações</button>
            </form>
        </div>

        <!-- Métodos de Pagamento -->
        <div class="config-section">
            <h3>Métodos de Pagamento</h3>
            <div class="info-box">
                Configure os métodos de pagamento aceitos pela sua loja.
            </div>
            <form method="post" action="../controller/ConfigController.php">
                <input type="hidden" name="action" value="update_payment_methods" />
                
                <div class="permission-grid">
                    <div class="permission-item">
                        <input type="checkbox" id="pix" name="payment_methods[]" value="PIX" checked />
                        <label for="pix" style="margin: 0;">PIX</label>
                    </div>
                    <div class="permission-item">
                        <input type="checkbox" id="credito" name="payment_methods[]" value="Cartão de Crédito" checked />
                        <label for="credito" style="margin: 0;">Cartão de Crédito</label>
                    </div>
                    <div class="permission-item">
                        <input type="checkbox" id="debito" name="payment_methods[]" value="Cartão de Débito" checked />
                        <label for="debito" style="margin: 0;">Cartão de Débito</label>
                    </div>
                    <div class="permission-item">
                        <input type="checkbox" id="boleto" name="payment_methods[]" value="Boleto Bancário" checked />
                        <label for="boleto" style="margin: 0;">Boleto Bancário</label>
                    </div>
                </div>

                <label for="pix_key" style="margin-top: 20px;">Chave PIX</label>
                <input type="text" id="pix_key" name="pix_key" value="contato@hecate.com.br" />

                <button type="submit" class="btn-salvar">Salvar Métodos de Pagamento</button>
            </form>
        </div>

        <!-- Opções de Envio -->
        <div class="config-section">
            <h3>Opções de Envio</h3>
            <form method="post" action="../controller/ConfigController.php">
                <input type="hidden" name="action" value="update_shipping" />
                
                <div class="permission-grid">
                    <div class="permission-item">
                        <input type="checkbox" id="correios" name="shipping_methods[]" value="Correios" checked />
                        <label for="correios" style="margin: 0;">Correios (PAC/SEDEX)</label>
                    </div>
                    <div class="permission-item">
                        <input type="checkbox" id="transportadora" name="shipping_methods[]" value="Transportadora" checked />
                        <label for="transportadora" style="margin: 0;">Transportadora</label>
                    </div>
                    <div class="permission-item">
                        <input type="checkbox" id="retirada" name="shipping_methods[]" value="Retirada em Loja" checked />
                        <label for="retirada" style="margin: 0;">Retirada em Loja</label>
                    </div>
                </div>

                <label for="shipping_time" style="margin-top: 20px;">Prazo de Envio (dias úteis)</label>
                <input type="text" id="shipping_time" name="shipping_time" value="5-10 dias" />

                <label for="free_shipping_min">Frete Grátis a partir de (R$)</label>
                <input type="text" id="free_shipping_min" name="free_shipping_min" value="200.00" />

                <button type="submit" class="btn-salvar">Salvar Opções de Envio</button>
            </form>
        </div>

        <!-- Permissões e Usuários -->
        <div class="config-section">
            <h3>Gerenciamento de Permissões</h3>
            <div class="info-box">
                Gerencie as permissões de acesso ao painel administrativo. Por padrão, apenas usuários marcados como administradores têm acesso total ao sistema.
            </div>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f9f9f9;">
                        <th style="border: 1px solid #ccc; padding: 10px; text-align: left;">Tipo de Usuário</th>
                        <th style="border: 1px solid #ccc; padding: 10px; text-align: left;">Permissões</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 10px;"><strong>Administrador</strong></td>
                        <td style="border: 1px solid #ccc; padding: 10px;">Acesso total ao sistema</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 10px;"><strong>Gerente</strong></td>
                        <td style="border: 1px solid #ccc; padding: 10px;">Acesso a produtos, pedidos e relatórios (a implementar)</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 10px;"><strong>Cliente</strong></td>
                        <td style="border: 1px solid #ccc; padding: 10px;">Acesso apenas à loja e perfil</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Manutenção -->
        <div class="config-section">
            <h3>Manutenção do Sistema</h3>
            <div class="info-box">
                <strong>Atenção:</strong> As ações abaixo são irreversíveis. Use com cautela.
            </div>
            
            <form method="post" action="../controller/ConfigController.php" style="margin-top: 20px;" onsubmit="return confirm('Tem certeza que deseja limpar o cache do sistema?');">
                <input type="hidden" name="action" value="clear_cache" />
                <button type="submit" class="btn-salvar" style="background-color: #ffc107; color: black;">Limpar Cache</button>
            </form>
        </div>
    </div>
</body>
</html>
