<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Busca a imagem de perfil do banco
require_once __DIR__ . '/model/ProfileModel.php';
$profile_image = getProfilePicture($_SESSION['idusuario']);
$profile_pic_src = $profile_image ? 'data:' . $profile_image['mime_type'] . ';base64,' . base64_encode($profile_image['image_data']) : 'img/avatarfixo.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="user-profile.css">
    <style>
        body {
            font-family: 'Arimo', sans-serif;
        }
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.5);
            padding: 8px;
            text-align: center;
            border-bottom-left-radius: 75px;
            border-bottom-right-radius: 75px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .profile-picture-container:hover .upload-overlay {
            opacity: 1;
        }
        .upload-overlay i {
            color: white;
            font-size: 20px;
        }
        .profile-info {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group label {
            font-weight: bold;
            color: #363636;
        }
        .btn-primary {
            background-color: #69110c;
            border-color: #69110c;
        }
        .btn-primary:hover {
            background-color: #8b1510;
            border-color: #8b1510;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h2>Meu Perfil</h2>
        </div>

        <div class="profile-picture-container">
            <img src="<?php echo htmlspecialchars($profile_pic_src); ?>" alt="Foto de Perfil" class="profile-picture">
            <label for="profile-upload" class="upload-overlay">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="profile-upload" accept="image/*" style="display: none;">
        </div>

        <div class="profile-info">
            <form id="profile-form" action="../controller/ProfileController.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#profile-upload').change(function() {
                if (this.files && this.files[0]) {
                    var formData = new FormData();
                    formData.append('profile_pic', this.files[0]);

                    $.ajax({
                        url: '../controller/ProfileController.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                $('.profile-picture').attr('src', result.profile_pic);
                                $('.user-avatar').attr('src', result.profile_pic);
                            } else {
                                alert('Erro ao fazer upload da imagem: ' + result.message);
                            }
                        },
                        error: function() {
                            alert('Erro ao fazer upload da imagem');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 