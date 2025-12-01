<?php 
session_start();
require_once __DIR__ . '/service/path_helper.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hecate - Roupas Alternativas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="login.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-container">
            <!-- Login Form -->
            <h2>Entrar</h2>
            <?php
                if (isset($_SESSION['msg'])) {
                    echo "<div style='color: red; text-align: center; margin-bottom: 10px;'>" . $_SESSION['msg'] . "</div>";
                    unset($_SESSION['msg']);
                }
            ?>
            <form action="<?= url_to('controller/LoginController.php') ?>" method="post">
                <div style="margin-top: 10px;">
                    <label for="usuario">Nome ou Email:</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Digite seu nome ou email" style="width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 20px;" required>
                </div>
                <div style="margin-top: 10px;">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" style="width: 100%; padding: 8px; margin-bottom: 20px; border-radius: 20px;" required>
                </div>
                <button type="submit" style="background-color: darkred; color: white; padding: 10px 20px; width: 100%; border: none; border-radius: 20px; cursor: pointer;">
                    Entrar
                </button>
            </form>
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div style="display: flex; align-items: center;">
                    <input type="checkbox" id="lembre-me" style="margin-right: 5px;">
                    <label for="lembre-me">Lembre-me</label>
                </div>
                <a href="RecuperarSenha.php" style="text-decoration: none; color: darkred;">Esqueceu sua senha?</a>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <p>ou faça login com</p>
                <button style="background-color: #3b5998; color: white; padding: 10px 20px; width: 100%; margin-bottom: 10px; border: none; border-radius: 20px; cursor: pointer;">
                    Facebook
                </button>
                <button style="background-color: #db4a39; color: white; padding: 10px 20px; width: 100%; border: none; border-radius: 20px; cursor: pointer;">
                    Google
                </button>
            </div>
            <!-- Success Message -->
            <div id="loginSuccessMessage" style="display: none; text-align: center; margin-top: 20px; color: green; font-size: 18px;">
                Login feito com sucesso!
            </div>
         </div>

        <!-- Text Content -->
        <div class="text-content">
            <h4>Cadastre-se</h4>

            <p>
                O registro neste site permite que você acesse o status e o histórico do seu pedido. Basta preencher os campos abaixo e configuraremos uma nova conta para você rapidamente. Solicitaremos apenas as informações necessárias para tornar o processo de compra mais rápido e fácil
            </p>
            <a class="button" href="cadastro.php">
            Cadastrar
        </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-container">
        <div class="footer-container">
        <!-- Informações -->
        <div class="footer-column">
            <h4>Informações da loja</h4>
            <ul>
                <li><a href="#">Hecate no Remessa</a></li>
                <li><a href="#">Conforme</a></li>
                <li><a href="#">Sobre Hecate</a></li>
                <li><a href="#">Venda na Hecate</a></li>
                <li><a href="#">Blogueiros de moda</a></li>
                <li><a href="#">Carreiras</a></li>
                <li><a href="#">Sala de Imprensa</a></li>
            </ul>
        </div>

        <!-- Ajuda e Suporte -->
        <div class="footer-column">
            <h4>Ajuda e suporte</h4>
            <ul>
                <li><a href="#">Política de Frete</a></li>
                <li><a href="#">Devolução</a></li>
                <li><a href="#">Reembolso</a></li>
                <li><a href="#">Como Pedir</a></li>
                <li><a href="#">Como Rastrear</a></li>
                <li><a href="#">Guia de Tamanhos</a></li>
                <li><a href="#">Hecate VIP</a></li>
            </ul>
        </div>

        <!-- Atendimento ao Cliente -->
        <div class="footer-column">
            <h4>Atendimento ao cliente</h4>
            <ul>
                <li><a href="#">Contate-Nos</a></li>
                <li><a href="#">Método de Pagamento</a></li>
                <li><a href="#">Pontos Bônus</a></li>
            </ul>
        </div>

        <!-- Redes sociais e cadastro -->
        <div class="footer-column">
            <h4>Encontre-nos em</h4>
            <div class="social-icons">
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/facebook.png"/></a>
                <a href="https://www.instagram.com/hecatealternativa/profilecard/?igsh=MXB0dmV3bGN5NDlhag=="><img src="https://img.icons8.com/ios-filled/24/000000/instagram-new.png"/></a>
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/twitter.png"/></a>
                <a href="#"><img src="https://img.icons8.com/ios-filled/24/000000/youtube-play.png"/></a>
            </div>
            <div class="subscribe-section">
                <h4>Cadastre-se para receber noticias sobre Hecate</h4>
                <input type="email" placeholder="Endereço do Seu Email">
                <input type="text" placeholder="Conta WhatsApp">
                <button>Inscreva-se</button>
            </div>
            
            <!-- Pagamento -->
            <h4>Pagamento</h4>
            <div class="payment-icons">
                <img src="https://img.icons8.com/ios-filled/35/000000/visa.png"/>
                <img src="https://img.icons8.com/ios-filled/35/000000/mastercard.png"/>
                <img src="https://img.icons8.com/ios-filled/35/000000/paypal.png"/>
            </div>
        </div>
    </div>
</footer>
<div id="message-container" class="message-container">
    <p id="message-text"></p>
</div>
</body>
</html>