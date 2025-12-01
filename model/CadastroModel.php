<?php
require __DIR__ . '/../service/conexao.php';
require __DIR__ . '/../service/funcoes.php';

function register($fullname, $email, $senha) {
    $conn = new UsePDO();
    $instance = $conn->getInstance();

    $sql = "SELECT IDusuario FROM usuario WHERE email = ?";
    $stmt = $instance->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        return false; 
    } // <-- Fechando o bloco do if corretamente

    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuario(email, senha) VALUES (?, ?)";
    $stmt = $instance->prepare($sql);
    $stmt->execute([$email, $hashed_password]);

    $idusuario = $instance->lastInsertId();

    $sql = "INSERT INTO pessoa(nome, IDusuario) VALUES (?, ?)";
    $stmt = $instance->prepare($sql);
    $stmt->execute([$fullname, $idusuario]);

    return $idusuario;
}
?>
