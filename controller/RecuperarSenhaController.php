<?php
session_start();
require_once __DIR__ . '/../model/RecuperarSenhaModel.php';
require_once __DIR__ . '/../service/funcoes.php';

function gerarCodigo() {
    return rand(1000, 9999);
}

$model = new RecuperarSenhaModel();

if (!empty($_POST['email'])) {
    $email = trim($_POST['email']);
    $IDusuario = $model->buscarIDusuarioPorEmail($email);

    if ($IDusuario) {
        $codigo = gerarCodigo();
        $model->salvarCodigo($IDusuario, $codigo);

        $_SESSION['msg'] = "Código enviado para seu email!";
        $_SESSION['email_recuperacao'] = $email;
        $_SESSION['codigo_recuperacao'] = $codigo;
        $_SESSION['id_usuario_recuperacao'] = $IDusuario;

        header('Location: ../verificarcodigo.php');
        exit();
    } else {
        $_SESSION['msg'] = "Email não encontrado.";
        header('Location: ../RecuperarSenha.php');
        exit();
    }
} else {
    $_SESSION['msg'] = "Por favor, preencha o campo de email.";
    header('Location: ../RecuperarSenha.php');
    exit();
}
