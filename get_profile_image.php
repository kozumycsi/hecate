<?php
session_start();
require_once __DIR__ . '/model/ProfileModel.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['idusuario'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Obtém a imagem do banco de dados
$image_data = getProfilePicture($_SESSION['idusuario']);

if ($image_data) {
    // Define os headers para exibir a imagem
    header('Content-Type: ' . $image_data['mime_type']);
    header('Cache-Control: public, max-age=31536000'); // Cache por 1 ano
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
    
    // Exibe a imagem
    echo $image_data['image_data'];
} else {
    // Se não há imagem, retorna a imagem padrão
    $default_image_path = 'img/avatarfixo.png';
    if (file_exists($default_image_path)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        echo file_get_contents($default_image_path);
    } else {
        http_response_code(404);
        exit('Image not found');
    }
}
?> 