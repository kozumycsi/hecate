<?php
require_once __DIR__ . '/../service/conexao.php';
require_once __DIR__ . '/../service/funcoes.php';

class RecuperarSenhaModel
{
    private $conexao;

    function __construct()
    {
        $pdo = new UsePDO();
        $this->conexao = $pdo->getInstance();
    }

    function buscarIDusuarioPorEmail($email)
    {
        $stmt = $this->conexao->prepare("SELECT u.IDusuario FROM usuario u WHERE u.email = ?");
        $stmt->execute([$email]);
    
        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['IDusuario']; 
        }
    
        return null;
    }

    function salvarCodigo($IDusuario, $codigo)
    {
        $stmt = $this->conexao->prepare("DELETE FROM codigo WHERE usuario = ?");
        $stmt->execute([$IDusuario]);

        $stmt = $this->conexao->prepare("INSERT INTO codigo (usuario, codigo) VALUES (?, ?)");
        $stmt->execute([$IDusuario, $codigo]);
    }
}

function atualizarSenha($email, $novaSenha) {
    $conn = new UsePDO();
    $instance = $conn->getInstance();

    $novaSenhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuario SET senha = ? WHERE email = ?";
    $stmt = $instance->prepare($sql);
    return $stmt->execute([$novaSenhaCriptografada, $email]);
}
?>
