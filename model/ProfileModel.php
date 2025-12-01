<?php
require_once __DIR__ . '/../service/conexao.php';

function updateProfilePicture($idusuario, $image_data, $mime_type) {
    $conn = new UsePDO();
    $instance = $conn->getInstance();

    try {
        $instance->beginTransaction();

        // Primeiro, remove a imagem anterior se existir
        $sql_delete = "DELETE FROM profile_images WHERE user_id = ?";
        $stmt_delete = $instance->prepare($sql_delete);
        $stmt_delete->execute([$idusuario]);

        // Insere a nova imagem
        $sql_insert = "INSERT INTO profile_images (user_id, image_data, mime_type) VALUES (?, ?, ?)";
        $stmt_insert = $instance->prepare($sql_insert);
        $stmt_insert->execute([$idusuario, $image_data, $mime_type]);

        $instance->commit();
        return true;
    } catch (Exception $e) {
        $instance->rollBack();
        return false;
    }
}

function getProfilePicture($idusuario) {
    $conn = new UsePDO();
    $instance = $conn->getInstance();

    $sql = "SELECT image_data, mime_type FROM profile_images WHERE user_id = ?";
    $stmt = $instance->prepare($sql);
    $stmt->execute([$idusuario]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function updateProfile($idusuario, $nome, $email) {
    $conn = new UsePDO();
    $instance = $conn->getInstance();

    try {
        $instance->beginTransaction();

        // Update pessoa table
        $sql_pessoa = "UPDATE pessoa SET nome = ? WHERE idusuario = ?";
        $stmt_pessoa = $instance->prepare($sql_pessoa);
        $stmt_pessoa->execute([$nome, $idusuario]);

        // Update usuario table
        $sql_usuario = "UPDATE usuario SET email = ? WHERE idusuario = ?";
        $stmt_usuario = $instance->prepare($sql_usuario);
        $stmt_usuario->execute([$email, $idusuario]);

        $instance->commit();
        return true;
    } catch (Exception $e) {
        $instance->rollBack();
        return false;
    }
}
?> 