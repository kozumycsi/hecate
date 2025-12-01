<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="user-profile.css">
    <style>
        body {
            font-family: 'Arimo', sans-serif;
        }
        .orders-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .order-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .order-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .order-status {
            font-weight: bold;
            color: #69110c;
        }
        .order-details {
            margin-top: 15px;
        }
        .product-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 5px;
        }
        .product-info {
            flex-grow: 1;
        }
        .order-total {
            text-align: right;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="orders-container">
        <h2 class="text-center mb-4">Meus Pedidos</h2>
        
        <?php
        // Aqui você deve buscar os pedidos do usuário do banco de dados
        // Por enquanto, vamos mostrar um exemplo
        ?>
        
        <div class="order-card">
            <div class="order-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Pedido #12345</h5>
                        <p class="mb-0">Data: 01/01/2024</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <span class="order-status">Em Processamento</span>
                    </div>
                </div>
            </div>
            
            <div class="order-details">
                <div class="product-item">
                    <img src="../img/hecate1.png" alt="Produto" class="product-image">
                    <div class="product-info">
                        <h6>Pijama</h6>
                        <p class="mb-0">Quantidade: 1</p>
                        <p class="mb-0">Tamanho: M</p>
                    </div>
                    <div class="product-price">
                        R$ 63,90
                    </div>
                </div>
            </div>
            
            <div class="order-total">
                Total: R$ 63,90
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 