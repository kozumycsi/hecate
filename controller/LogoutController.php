<?php
session_start();
require_once __DIR__ . '/../service/path_helper.php';

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

// Redirecionar para a home usando a função url_to
header('Location: ' . url_to('index.php'));
exit();
?> 