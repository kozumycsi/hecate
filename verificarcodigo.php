<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Verificar Código</title>
  <link rel="stylesheet" href="styl.css">
</head>
<body>
  <div class="container">
    <div class="caixadelogin">
      <h2>Verificar Código</h2>

      <?php
        session_start();
        if (isset($_SESSION['msg'])) {
          echo '<div class="mensagem" style="color: yellow;">'.$_SESSION['msg'].'</div>';
          unset($_SESSION['msg']); 
        }
      ?>

      <form action="../controller/VerificarCodigoController.php" method="post">
        <input type="text" name="codigo" placeholder="Digite o código" required>
        <button type="submit">Verificar Código</button>
      </form>

      <p><a href="recuperarsenha.php">Voltar</a></p>
    </div>
  </div>
</body>
</html>
