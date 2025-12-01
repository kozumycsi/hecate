<?php
require_once __DIR__ . '/../service/path_helper.php';

if (isset($_SESSION['usuario'])) {
    
    require_once __DIR__ . '/../model/ProfileModel.php';
    $profile_image = getProfilePicture($_SESSION['idusuario']);
    $defaultAvatar = asset_url('img/avatarfixo.png');
    $profile_pic_src = $profile_image ? 'data:' . $profile_image['mime_type'] . ';base64,' . base64_encode($profile_image['image_data']) : $defaultAvatar;
    
  
    ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($profile_pic_src); ?>" alt="Profile" class="user-avatar" onerror="this.src='<?php echo $defaultAvatar; ?>'">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="<?= url_to('perfil.php') ?>">Meu Perfil</a>
            <a class="dropdown-item" href="<?= url_to('meus-pedidos.php') ?>">Meus Pedidos</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?= url_to('controller/LogoutController.php') ?>">Sair</a>
        </div>
    </li>
    <?php
} else {
    ?>
    <li class="nav-item">
        <a class="nav-link" href="<?= url_to('login.php') ?>"><i class="fas fa-user"></i> Entrar/Cadastrar-se</a>
    </li>
    <?php
}
?> 