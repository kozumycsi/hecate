<?php session_start(); ?>
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
    <link rel="stylesheet" type="text/css" href="cadastro.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="form-container">
        <h1>CADASTRAR</h1>
        <p>HOME / MINHA CONTA</p>

        <?php
        if (isset($_SESSION['msg'])) {
            echo "<div style='color: red; text-align: center; margin-bottom: 10px;'>" . $_SESSION['msg'] . "</div>";
            unset($_SESSION['msg']);
        }
        ?>

        <!-- CORRIGIDO AQUI O ACTION -->
        <form action="../controller/CadastroController.php" method="post">
            <input type="text" class="input" placeholder="Nome" name="fullName" required>
            <input type="email" class="input" placeholder="Email" name="email" required>
            <input type="password" class="input" placeholder="Senha" name="password" required>
            <input type="password" class="input" placeholder="Confirme sua Senha" name="confirmPassword" required>
            <button type="submit" class="botao">Cadastrar</button>
        </form>
    </div>

    <footer>
        <div class="footer-container">
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

            <div class="footer-column">
                <h4>Atendimento ao cliente</h4>
                <ul>
                    <li><a href="#">Contate-Nos</a></li>
                    <li><a href="#">Método de Pagamento</a></li>
                    <li><a href="#">Pontos Bônus</a></li>
                </ul>
            </div>

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
