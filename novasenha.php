<?php
session_start();


if (empty($_SESSION['codigo_verificado']) || $_SESSION['codigo_verificado'] !== true) {
    $_SESSION['msg'] = "Acesso inválido. Código não verificado.";
    header("Location: verificarcodigo.php");
    exit();
}


if (empty($_SESSION['email_recuperacao'])) {
    $_SESSION['msg'] = "Erro interno. Por favor, inicie a recuperação novamente.";
    header("Location: recuperarSenha.php");
    exit();
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Nova Senha</title>
    <link rel="stylesheet" href="">
</head>
<body>
    <div class="container">
        <div class="caixadelogin">
            <h2>Nova Senha</h2>
            <?php
            if (isset($_SESSION['msg'])) {
                echo "<p style='color:yellow;'>" . $_SESSION['msg'] . "</p>";
                unset($_SESSION['msg']);
            }
            ?>
            <form action="../controller/NovaSenhaController.php" method="post">
                <input type="password" name="novaSenha" placeholder="Nova senha" required>
                <input type="password" name="confirmaSenha" placeholder="Confirme a nova senha" required>
                <button type="submit">Alterar senha</button>
            </form>
        </div>
    </div>
</body>
</html>
