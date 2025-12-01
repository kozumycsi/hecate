<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Hecate</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="checkout.css">
    <link rel="stylesheet" href="user-profile.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <!-- Resumo do Pedido -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-shopping-cart"></i> Resumo do Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div id="resumo-produtos"></div>
                        
                        <!-- Cupom de Desconto -->
                        <div class="cupom-section">
                            <h5><i class="fas fa-tag"></i> Cupom de Desconto</h5>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cupom-codigo" placeholder="Digite seu cupom">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="aplicarCupom()">
                                        Aplicar
                                    </button>
                                </div>
                            </div>
                            <div id="cupom-mensagem"></div>
                        </div>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cep">CEP *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="cep" maxlength="9" placeholder="00000-000">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="buscarCep()" title="Buscar CEP">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="cep-status" class="cep-status"></div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="endereco">Endereço *</label>
                                    <input type="text" class="form-control" id="endereco" placeholder="Digite o endereço">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero">Número *</label>
                                    <input type="text" class="form-control" id="numero" placeholder="123">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="complemento">Complemento</label>
                                    <input type="text" class="form-control" id="complemento" placeholder="Apto, Casa, etc">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bairro">Bairro *</label>
                                    <input type="text" class="form-control" id="bairro" placeholder="Digite o bairro">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cidade">Cidade *</label>
                                    <input type="text" class="form-control" id="cidade" placeholder="Digite a cidade">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado *</label>
                                    <input type="text" class="form-control" id="estado" placeholder="Digite o estado">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="limparCamposEndereco()">
                                    <i class="fas fa-eraser"></i> Limpar Campos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Forma de Pagamento -->
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-credit-card"></i> Forma de Pagamento</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento" id="pix" value="pix" checked>
                                <label class="form-check-label" for="pix">
                                    <i class="fas fa-qrcode"></i> PIX
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento" id="debito" value="debito">
                                <label class="form-check-label" for="debito">
                                    <i class="fas fa-credit-card"></i> Cartão de Débito
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento" id="credito" value="credito">
                                <label class="form-check-label" for="credito">
                                    <i class="fas fa-credit-card"></i> Cartão de Crédito
                                </label>
                            </div>
                        </div>

                        <!-- Campos do Cartão (inicialmente ocultos) -->
                        <div id="campos-cartao" style="display: none;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="numero-cartao">Número do Cartão *</label>
                                        <input type="text" class="form-control" id="numero-cartao" maxlength="19" placeholder="0000 0000 0000 0000">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="validade">Validade *</label>
                                        <input type="text" class="form-control" id="validade" maxlength="5" placeholder="MM/AA">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cvv">CVV *</label>
                                        <input type="text" class="form-control" id="cvv" maxlength="4" placeholder="123">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nome-cartao">Nome no Cartão *</label>
                                        <input type="text" class="form-control" id="nome-cartao" placeholder="Nome como está no cartão">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-receipt"></i> Resumo</h4>
                    </div>
                    <div class="card-body">
                        <div class="resumo-item">
                            <span>Subtotal:</span>
                            <span id="subtotal">R$ 0,00</span>
                        </div>
                        <div class="resumo-item">
                            <span>Frete:</span>
                            <span id="frete">R$ 0,00</span>
                        </div>
                        <div class="resumo-item" id="desconto-item" style="display: none;">
                            <span>Desconto:</span>
                            <span id="desconto">-R$ 0,00</span>
                        </div>
                        <hr>
                        <div class="resumo-item total">
                            <span>Total:</span>
                            <span id="total">R$ 0,00</span>
                        </div>
                        
                        <button class="btn btn-primary btn-block mt-3" onclick="finalizarCompra()">
                            <i class="fas fa-lock"></i> Finalizar Compra
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script src="checkout.js"></script>
</body>
</html> 