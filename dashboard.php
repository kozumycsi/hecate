<?php
require_once __DIR__ . '/service/conexaodash.php';
require_once __DIR__ . '/service/funcoesdash.php';

$emailSelecionado = null;
$emails = buscarEmails($conexao);

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($action == 'delete' && $id > 0) {
        excluirEmail($conexao, $id);
        header("Location: dashboard.php");
        exit;
    }

    if ($action == 'unread' && $id > 0) {
        marcarComoNaoLido($conexao, $id);
        header("Location: dashboard.php?id=" . $id);
        exit;
    }
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $emailSelecionado = buscarEmailPorId($conexao, $id);

    if ($emailSelecionado) {
        marcarComoLido($conexao, $id);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Interface de Email</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
<style>
html, body { height: 100%; overflow-x: hidden; }
.email-container { height: calc(100vh - 56px); }
.email-list { height: 100%; overflow-y: auto; background-color: #f8f9fa; border-right: 1px solid #dee2e6; }
.email-detail { height: 100%; overflow-y: auto; padding: 20px; }
.email-item { cursor: pointer; padding: 15px; border-bottom: 1px solid #dee2e6; }
.email-item:hover { background-color: #f1f3f5; }
.email-item.active { background-color: #0d6efd; color: white; }
.email-item.active .text-muted { color: rgba(255, 255, 255, 0.75) !important; }
.email-item.unread { font-weight: bold; }
.unread-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: #0d6efd; margin-right: 8px; }
.empty-state { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: #6c757d; }
@media (max-width: 767.98px) {
    .email-container { height: auto; }
    .email-list, .email-detail { height: auto; max-height: 100vh; }
}
</style>
</head>
<body>
<header class="bg-primary text-white p-3">
<h1 class="h4 m-0">Email Dashboard</h1>
</header>

<div class="container-fluid p-0">
<div class="row g-0 email-container">
<div class="col-md-4 col-lg-3 email-list">
<?php if (empty($emails)): ?>
<div class="text-center p-4 text-muted">
<p>Nenhum email encontrado</p>
</div>
<?php else: ?>
<?php foreach ($emails as $email): ?>
<a href="?id=<?php echo $email['ID']; ?>" class="text-decoration-none">
<div class="email-item <?php echo ($emailSelecionado && $emailSelecionado['ID'] == $email['ID']) ? 'active' : ''; ?> <?php echo $email['lido'] ? '' : 'unread'; ?>">
<div class="d-flex justify-content-between align-items-start">
<div class="ms-2 me-auto">
<div class="d-flex align-items-center mb-1">
<?php if (!$email['lido']): ?>
<span class="unread-indicator"></span>
<?php endif; ?>
<span><?php echo htmlspecialchars($email['email_usuario']); ?></span>
</div>
<div class="<?php echo ($emailSelecionado && $emailSelecionado['ID'] == $email['ID']) ? '' : 'text-muted'; ?> small">
Recuperação de senha!
</div>
</div>
<small class="<?php echo ($emailSelecionado && $emailSelecionado['ID'] == $email['ID']) ? '' : 'text-muted'; ?>">
<?php echo formatarData($email['data'] ?? ''); ?>
</small>
</div>
</div>
</a>
<?php endforeach; ?>
<?php endif; ?>
</div>

<div class="col-md-8 col-lg-9 email-detail">
<?php if ($emailSelecionado): ?>
<div>
<div class="d-flex justify-content-between align-items-center mb-4">
<h2 class="h4">Atualização de Acesso à Conta.</h2>
<div>
    <a href="?action=delete&id=<?php echo $emailSelecionado['ID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir este email?');">
        <i class="bi bi-trash"></i> Excluir
    </a>
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-reply"></i> Responder</button>
    <a href="?action=unread&id=<?php echo $emailSelecionado['ID']; ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-envelope-open"></i> Marcar como não lido
    </a>
</div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
<span class="text-muted"><?php echo formatarData($emailSelecionado['data'] ?? ''); ?></span>
</div>

<div class="card mb-4">
<div class="card-header bg-light d-flex justify-content-between align-items-center">
<div><strong>De:</strong> <?php echo htmlspecialchars($emailSelecionado['email_usuario']); ?></div>
<div><span class="badge bg-secondary">Código: <?php echo htmlspecialchars($emailSelecionado['codigo']); ?></span></div>
</div>
<div class="card-body">
<p class="card-text">
<?php
echo nl2br(htmlspecialchars(
    "Informamos que o acesso à sua conta foi atualizado com sucesso.\n\n".
    "Seu novo código de acesso é:\n\n".
    "🔐 Código de Acesso: " . htmlspecialchars($emailSelecionado['codigo']) . "\n\n".
    "Por motivos de segurança, recomendamos que você mantenha este código em local seguro.\n".
    "Em caso de dúvidas ou dificuldades, nossa equipe está pronta para ajudar.\n\n".
    "Com cordialidade,\nEquipe de Atendimento"
));
?>
</p>
</div>
</div>

<div class="card">
<div class="card-header bg-light">
<h3 class="h5 mb-0">Detalhes do Usuário</h3>
</div>
<div class="card-body">
<div class="row">
<div class="col-md-6"><p><strong>Email do Usuário:</strong> <?php echo htmlspecialchars($emailSelecionado['email_usuario']); ?></p></div>
<div class="col-md-6"><p><strong>Código:</strong> <?php echo htmlspecialchars($emailSelecionado['codigo']); ?></p></div>
</div>
</div>
</div>
</div>
<?php else: ?>
<div class="empty-state">
<i class="bi bi-envelope fs-1"></i>
<p>Selecione um email para ver seu conteúdo</p>
</div>
<?php endif; ?>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
