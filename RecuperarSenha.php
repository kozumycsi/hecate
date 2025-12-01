
<?php
session_start();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="recupersenha.css">
</head>
<body>
    <div class="container">
        <div class="caixadelogin">
            <h2>Recuperar Senha</h2>
            <?php
            if (isset($_SESSION['msg'])) {
                echo '<div class="mensagem">'.$_SESSION['msg'].'</div>';
                unset($_SESSION['msg']);
            }
            ?>
            <p>Por favor, insira seu email abaixo para receber o código de verificação.</p>
            <form action="../controller/RecuperarSenhaController.php" method="post">
                <input type="email" class="form-control" placeholder="Email" name="email" required />
                <button class="botao" type="submit">Enviar código</button>
            </form>
            <p class="textodesla"><a href="index.php">Voltar ao Login</a></p>
        </div>
    </div>
</body>
</html>