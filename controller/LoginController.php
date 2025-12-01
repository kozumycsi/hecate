<?php
session_start();
require __DIR__ . '/../model/LoginModel.php';
require __DIR__ . '/../service/funcoes.php';
require_once __DIR__ . '/../service/path_helper.php';

if ($_POST) {
	$usuario = $_POST['usuario'];
	$senha = $_POST['senha'];

	$result = login($usuario, $senha);

	if ($result) {
		$_SESSION['msg'] = "Login realizado com sucesso!";
		$_SESSION['usuario'] = $result['nome'];
		$_SESSION['idusuario'] = $result['idusuario'];
		$_SESSION['email'] = $result['email'];
		$_SESSION['is_admin'] = isset($result['is_admin']) ? (int)$result['is_admin'] : 0;
		if (!empty($_SESSION['is_admin'])) {
			header('Location: ' . url_to('paineladm.php'));
		} else {
			header('Location: ' . url_to('index.php'));
		}
		exit();
	} else {
		$_SESSION['msg'] = "Usuário ou senha incorretos.";
		header('Location: ' . url_to('login.php')); 
		exit();
	}
}
?>
