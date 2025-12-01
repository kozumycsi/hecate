<?php
/**
 * Script para migrar imagens de perfil do sistema antigo (arquivos) para o novo sistema (BLOB)
 * Execute este script apenas uma vez após implementar o novo sistema
 */

require_once 'service/conexao.php';
require_once 'model/ProfileModel.php';

echo "Iniciando migração de imagens de perfil...\n";

$conn = new UsePDO();
$instance = $conn->getInstance();

// Busca usuários que têm profile_pic na tabela usuario (sistema antigo)
$sql = "SELECT idusuario, profile_pic FROM usuario WHERE profile_pic IS NOT NULL AND profile_pic != ''";
$stmt = $instance->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$migrated = 0;
$errors = 0;

foreach ($users as $user) {
    $file_path = 'uploads/profile_pics/' . basename($user['profile_pic']);
    
    if (file_exists($file_path)) {
        try {
            // Lê o arquivo
            $image_data = file_get_contents($file_path);
            $mime_type = mime_content_type($file_path);
            
            // Salva no novo sistema
            if (updateProfilePicture($user['idusuario'], $image_data, $mime_type)) {
                echo "Migrado: Usuário ID {$user['idusuario']} - {$file_path}\n";
                $migrated++;
            } else {
                echo "Erro ao migrar: Usuário ID {$user['idusuario']} - {$file_path}\n";
                $errors++;
            }
        } catch (Exception $e) {
            echo "Erro ao processar arquivo {$file_path}: " . $e->getMessage() . "\n";
            $errors++;
        }
    } else {
        echo "Arquivo não encontrado: {$file_path}\n";
        $errors++;
    }
}

echo "\nMigração concluída!\n";
echo "Migrados com sucesso: {$migrated}\n";
echo "Erros: {$errors}\n";

if ($migrated > 0) {
    echo "\nAgora você pode remover o campo 'profile_pic' da tabela 'usuario' se desejar.\n";
    echo "Execute: ALTER TABLE usuario DROP COLUMN profile_pic;\n";
}
?> 