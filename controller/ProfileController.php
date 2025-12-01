<?php
session_start();
require_once __DIR__ . '/../model/ProfileModel.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

// Handle profile picture upload
if (isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.']);
        exit();
    }

    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'O arquivo é muito grande. Tamanho máximo: 5MB']);
        exit();
    }

    // Lê o arquivo e converte para base64
    $image_data = file_get_contents($file['tmp_name']);
    $mime_type = $file['type'];
    
    // Salva no banco como BLOB
    if (updateProfilePicture($_SESSION['idusuario'], $image_data, $mime_type)) {
        // Converte para base64 para exibição
        $base64_image = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
        
        echo json_encode(['success' => true, 'profile_pic' => $base64_image]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar a imagem no banco de dados']);
    }
    exit();
}

// Handle profile information update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome']) && isset($_POST['email'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $idusuario = $_SESSION['idusuario'];

    if (updateProfile($idusuario, $nome, $email)) {
        $_SESSION['usuario'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['msg'] = "Perfil atualizado com sucesso!";
    } else {
        $_SESSION['msg'] = "Erro ao atualizar perfil.";
    }
    
    header('Location: ../perfil.php');
    exit();
}
?> 