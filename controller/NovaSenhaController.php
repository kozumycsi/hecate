<?php
session_start();
require __DIR__ . '/../model/RecuperarSenhaModel.php';

if (!isset($_SESSION['email_recuperacao'])) {
    $_SESSION['msg'] = "Erro interno. Email não encontrado.";
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email_recuperacao'];
$novaSenha = $_POST['novaSenha'];
$confirmarSenha = $_POST['confirmaSenha'];

if ($novaSenha !== $confirmarSenha) {
    $_SESSION['msg'] = "As senhas não coincidem.";
        header("Location: ../novasenha.php");
    exit();
}

if (atualizarSenha($email, $novaSenha)) {
    unset($_SESSION['email_recuperacao']); 
    $_SESSION['msg'] = "Senha redefinida com sucesso!";
    header("Location: ../login.php");
    exit();
} else {
    $_SESSION['msg'] = "Erro ao redefinir a senha.";
        header("Location: ../novasenha.php");
    exit();
}




