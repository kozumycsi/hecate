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
    <link rel="stylesheet" type="text/css" href="ashash.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <!-- Seção de campanha: imagem de fundo à esquerda (definida em ashah.css/.custom-bg)
         e informações de footer à direita, formando um grande rodapé visual. -->
    <section id="shop" class="py-5 custom-bg">
        <div class="container-fluid">
            <div class="row no-gutters">
                <!-- Coluna esquerda: apenas a arte de fundo -->
                <div class="col-md-7"></div>

                <!-- Coluna direita: painel com informações do footer -->
                <div class="col-md-5">
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
                                <h4>Cadastre-se para receber notícias sobre Hecate</h4>
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
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

