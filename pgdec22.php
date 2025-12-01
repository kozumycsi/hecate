<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hecate - Roupas Alternativas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="pgdec.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Top Fixed Bar -->
    <div class="fixed-top-bar">
        <div class="left-side">
            <span>FRETE GRÁTIS APARTIR DE R$150</span>
        </div>
        <div class="right-side">
            <span>ONDE ENCONTRAR</span>
            <span>RASTREAR PEDIDO</span>
            <span>CONTATO</span>
            <div class="social-icons">
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/hecatealternativa/profilecard/?igsh=MXB0dmV3bGN5NDlhag=="><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-white bg-white">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="../index.php">
            <img src="../img/logo.png" width="80" alt="Logo Hecate">
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-search"></i> pesquisar</a>
                </li>
                 <li class="nav-item">
         <a class="nav-link" href="#"><i class="fas fa-heart"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="carrinho.php"><i class="fas fa-shopping-cart"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php"><i class="fas fa-user"></i> Entrar/Cadastre-se</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Secondary Navbar -->
    <nav class="navbar secondary-nav navbar-expand-lg navbar-light">
        <div class="collapse navbar-collapse justify-content-center" id="secondaryNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Todos</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Femininos</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Masculino</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Plus Size</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Calçados</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Acessórios</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Ofertas</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Reposições</a></li>
            </ul>
        </div>
    </nav>

    <!-- Produto Container -->
    <div class="produto-container">
        <div class="imagens-produto">
            <div class="imagem-principal">
                <img id="imagem-atual" src="../img/p22.webp" alt="Imagem Principal" />
            </div>
            <div class="imagem-miniaturas">
                <img src="../img/p22.webp" class="imagem-miniatura" alt="Miniatura 1" onclick="alterarImagem(this)" />
                <img src="../img/p22.webp" class="imagem-miniatura" alt="Miniatura 2" onclick="alterarImagem(this)" />
                <img src="../img/p22.webp" class="imagem-miniatura" alt="Miniatura 3" onclick="alterarImagem(this)" />
            </div>
        </div>

        <div class="detalhes-produto">
            <h1>Vestido gótico elegante</h1>
            <p>ID: <span>2424242422</span></p>
            <p class="avaliacao">Avaliação: 4,3</p>
            <p class="preco">R$ 169,90</p>

            <div class="escolha-cor">
                <p>Cores disponíveis</p>
                <div class="cores">
                    <div class="cor1" onclick="selecionarCor('Preto', this)" data-cor="Preto"></div>
                    <div class="cor2" onclick="selecionarCor('Vermelho', this)" data-cor="Vermelho"></div>
                    <div class="cor3" onclick="selecionarCor('Azul', this)" data-cor="Azul"></div>
                </div>
                <p id="cor-selecionada">Cor selecionada: <span>Nenhuma</span></p>
            </div>

            <div class="escolha-tamanho">
                <p>Tamanho:</p>
                <div class="tamanhos">
                    <button onclick="selecionarTamanho('PP (XS)', this)">PP (XS)</button>
                    <button onclick="selecionarTamanho('P (S)', this)">P (S)</button>
                    <button onclick="selecionarTamanho('M (M)', this)">M (M)</button>
                    <button onclick="selecionarTamanho('G (L)', this)">G (L)</button>
                    <button onclick="selecionarTamanho('GG (XL)', this)">GG (XL)</button>
                </div>
                <p id="tamanho-selecionado">Tamanho selecionado: <span>Nenhum</span></p>
            </div>

            <div class="acoes-produto">
                <button id="btn-adicionar" disabled>
                    Selecione cor e tamanho
                </button>     
                <button class="favoritos">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Seção de Comentários -->
    <div class="comentarios-container">
        <h2>Comentário do cliente</h2>
        <div class="avaliacao-comentarios">
            <p class="nota">4.6</p>
            <p class="total-avaliacao">avaliação</p>
        </div>

        <div class="filtros-comentarios">
            <button class="filtro">Todos os comentários</button>
            <button class="filtro">Imagem</button>
        </div>

        <div class="classificacao">
            <p>Classificação: </p>
            <button class="filtro">Tudo</button>
            <button class="filtro">Filtrar</button>
        </div>

        <div class="comentarios">
            <div class="comentario"></div>
            <div class="comentario"></div>
            <div class="comentario"></div>
        </div>

        <div class="paginacao">
            <button>&lt;</button>
            <button class="ativo">1</button>
            <button>2</button>
            <button>3</button>
            <button>4</button>
            <button>...</button>
            <button>10</button>
            <button>&gt;</button>
        </div>
    </div>

    <!-- Seção de Produtos Visitados -->
    <div class="produtos-visitados">
        <h2>Clientes também visitaram</h2>
    </div>
    <section class="produtos-visitados">
        <div class="grid-produtos">
            <!-- Caixa de produto 1 -->
            <div class="produto">
                <img src="../img/pgdec1.jpg" alt="Produto 1">
                <p>Nome do produto</p>
                <p>R$ 169,90</p>
            </div>
            <!-- Caixa de produto 2 -->
            <div class="produto">
                <img src="../img/pgdec2.webp" alt="Produto 2">
                <p>Nome do produto</p>
                <p>R$ 169,90</p>
            </div>
            <!-- Caixa de produto 3 -->
            <div class="produto">
                <img src="../img/pgdec3.webp" alt="Produto 3">
                <p>Nome do produto</p>
                <p>R$ 169,90</p>
            </div>
            <!-- Caixa de produto 4 -->
            <div class="produto">
                <img src="../img/pgdec4.webp" alt="Produto 4">
                <p>Nome do produto</p>
                <p>R$ 169,90</p>
            </div>
            <!-- Caixa de produto 5 -->
            <div class="produto">
                <img src="../img/pgdec5.webp" alt="Produto 5">
                <p>Nome do produto</p>
                <p>R$ 169,90</p>
            </div>
        </div>
    </section>

    <script>
        let corSelecionada = null;
        let tamanhoSelecionado = null;

        function alterarImagem(elemento) {
            const imagemPrincipal = document.getElementById("imagem-atual");
            imagemPrincipal.src = elemento.src;
        }

        function selecionarCor(cor, elemento) {
            // Remove seleção anterior
            document.querySelectorAll(".cores div").forEach(c => c.classList.remove("selecionada"));
            // Adiciona seleção atual
            elemento.classList.add("selecionada");
            corSelecionada = cor;
            document.getElementById("cor-selecionada").innerHTML = `Cor selecionada: <span>${cor}</span>`;
            verificarSelecao();
        }

        function selecionarTamanho(tamanho, elemento) {
            // Remove seleção anterior
            document.querySelectorAll(".tamanhos button").forEach(b => b.classList.remove("selecionado"));
            // Adiciona seleção atual
            elemento.classList.add("selecionado");
            tamanhoSelecionado = tamanho;
            document.getElementById("tamanho-selecionado").innerHTML = `Tamanho selecionado: <span>${tamanho}</span>`;
            verificarSelecao();
        }

        function verificarSelecao() {
            const btnAdicionar = document.getElementById("btn-adicionar");
            if (corSelecionada && tamanhoSelecionado) {
                btnAdicionar.disabled = false;
                btnAdicionar.textContent = "Adicionar ao Carrinho";
                btnAdicionar.onclick = function() {
                    adicionarAoCarrinho(22, 'Vestido gótico elegante', 169.9, '../img/p22.webp', corSelecionada, tamanhoSelecionado);
                };
            } else {
                btnAdicionar.disabled = true;
                btnAdicionar.textContent = "Selecione cor e tamanho";
            }
        }

        function adicionarAoCarrinho(id, nome, preco, imagem, cor, tamanho) {
            if (!cor || !tamanho) {
                alert("Por favor, selecione uma cor e um tamanho antes de adicionar ao carrinho.");
                return;
            }

            // Verificar se o carrinho existe no localStorage
            let carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
            
            // Adicionar produto ao carrinho
            carrinho.push({
                id: id,
                nome: nome,
                preco: preco,
                imagem: imagem,
                cor: cor,
                tamanho: tamanho,
                quantidade: 1
            });
            
            // Salvar no localStorage
            localStorage.setItem("carrinho", JSON.stringify(carrinho));
            
            // Mostrar mensagem de sucesso
            alert(`Produto adicionado ao carrinho!\nCor: ${cor}\nTamanho: ${tamanho}`);
            
            // Redirecionar para o carrinho
            window.location.href = "carrinho.php";
        }
    </script>
</body>
</html>