// Variáveis globais
let carrinho = [];
let descontoAplicado = 0;
let cupomAtivo = null;

// Cupons válidos (simulação)
const cuponsValidos = {
    'HECATE10': { desconto: 10, tipo: 'percentual' },
    'FREEGRATIS': { desconto: 15, tipo: 'percentual' },
    'PIX5': { desconto: 5, tipo: 'percentual' },
    'DESCONTO20': { desconto: 20, tipo: 'percentual' }
};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Usa cartItems do PHP se disponível, senão tenta localStorage
    if (typeof cartItems !== 'undefined' && Array.isArray(cartItems)) {
        carrinho = cartItems.map(item => ({
            id: item.id || item.id_produto,
            id_produto: item.id_produto || item.id,
            nome: item.nome,
            preco: parseFloat(item.preco),
            quantidade: parseInt(item.quantidade),
            tamanho: item.tamanho || null,
            cor: item.cor || null,
            imagem: item.imagem || '../img/logo.png'
        }));
    } else {
        carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    }
    
    if (carrinho.length === 0) {
        window.location.href = 'carrinho.php';
        return;
    }
    
    carregarCarrinho();
    configurarEventos();
    calcularResumo();
});

// Configurar eventos
function configurarEventos() {
    // CEP
    document.getElementById('cep').addEventListener('blur', buscarCep);
    document.getElementById('cep').addEventListener('input', formatarCep);
    
    // Forma de pagamento
    document.querySelectorAll('input[name="pagamento"]').forEach(radio => {
        radio.addEventListener('change', toggleCamposCartao);
    });
    
    // Formatação de campos
    document.getElementById('numero-cartao').addEventListener('input', formatarNumeroCartao);
    document.getElementById('validade').addEventListener('input', formatarValidade);
    document.getElementById('cvv').addEventListener('input', formatarCvv);
}

// Carregar carrinho
function carregarCarrinho() {
    const resumoProdutos = document.getElementById('resumo-produtos');
    resumoProdutos.innerHTML = '';
    
    carrinho.forEach(produto => {
        const produtoDiv = document.createElement('div');
        produtoDiv.className = 'produto-resumo';
        
        const variacoes = [];
        if (produto.cor) variacoes.push(`Cor: ${produto.cor}`);
        if (produto.tamanho) variacoes.push(`Tamanho: ${produto.tamanho}`);
        const variacoesTexto = variacoes.length > 0 ? variacoes.join(' | ') : '';
        
        produtoDiv.innerHTML = `
            <img src="${produto.imagem}" alt="${produto.nome}">
            <div class="produto-info">
                <h6>${produto.nome}</h6>
                <p>Quantidade: ${produto.quantidade}</p>
                ${variacoesTexto ? `<p>${variacoesTexto}</p>` : ''}
            </div>
            <div class="produto-preco">
                R$ ${(produto.preco * produto.quantidade).toFixed(2).replace('.', ',')}
            </div>
        `;
        
        resumoProdutos.appendChild(produtoDiv);
    });
}

// Aplicar cupom
function aplicarCupom() {
    const codigo = document.getElementById('cupom-codigo').value.trim().toUpperCase();
    const mensagem = document.getElementById('cupom-mensagem');
    
    if (!codigo) {
        mostrarMensagemCupom('Digite um código de cupom', 'erro');
        return;
    }
    
    if (cupomAtivo) {
        mostrarMensagemCupom('Você já tem um cupom aplicado', 'erro');
        return;
    }
    
    if (cuponsValidos[codigo]) {
        cupomAtivo = cuponsValidos[codigo];
        descontoAplicado = calcularDesconto();
        mostrarMensagemCupom(`Cupom ${codigo} aplicado com sucesso!`, 'sucesso');
        document.getElementById('cupom-codigo').disabled = true;
        calcularResumo();
    } else {
        mostrarMensagemCupom('Cupom inválido', 'erro');
    }
}

// Mostrar mensagem do cupom
function mostrarMensagemCupom(mensagem, tipo) {
    const elemento = document.getElementById('cupom-mensagem');
    elemento.textContent = mensagem;
    elemento.className = `cupom-${tipo}`;
    
    setTimeout(() => {
        elemento.textContent = '';
        elemento.className = '';
    }, 3000);
}

// Calcular desconto
function calcularDesconto() {
    if (!cupomAtivo) return 0;
    
    const subtotal = carrinho.reduce((total, produto) => total + (produto.preco * produto.quantidade), 0);
    
    if (cupomAtivo.tipo === 'percentual') {
        return subtotal * (cupomAtivo.desconto / 100);
    }
    
    return cupomAtivo.desconto;
}

// Calcular frete (simulação)
function calcularFrete() {
    const subtotal = carrinho.reduce((total, produto) => total + (produto.preco * produto.quantidade), 0);
    
    if (subtotal >= 150) {
        return 0; // Frete grátis
    }
    
    return 15.90; // Frete padrão
}

// Calcular resumo
function calcularResumo() {
    const subtotal = carrinho.reduce((total, produto) => total + (produto.preco * produto.quantidade), 0);
    const frete = calcularFrete();
    const desconto = calcularDesconto();
    const total = subtotal + frete - desconto;
    
    document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('frete').textContent = frete === 0 ? 'Grátis' : `R$ ${frete.toFixed(2).replace('.', ',')}`;
    
    if (desconto > 0) {
        document.getElementById('desconto-item').style.display = 'flex';
        document.getElementById('desconto').textContent = `-R$ ${desconto.toFixed(2).replace('.', ',')}`;
    } else {
        document.getElementById('desconto-item').style.display = 'none';
    }
    
    document.getElementById('total').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Buscar CEP (mesma função do checkout.js)
async function buscarCep() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    const cepInput = document.getElementById('cep');
    const cepStatus = document.getElementById('cep-status');
    
    if (cep.length !== 8) {
        mostrarStatusCep('Digite um CEP válido (8 dígitos)', 'error');
        return;
    }
    
    cepInput.classList.add('loading');
    mostrarStatusCep('Buscando CEP...', 'loading');
    
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        
        let response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        let data = await response.json();
        
        if (data.erro || !data.logradouro) {
            try {
                const controller2 = new AbortController();
                const timeoutId2 = setTimeout(() => controller2.abort(), 8000);
                
                response = await fetch(`https://cep.awesomeapi.com.br/json/${cep}`, {
                    signal: controller2.signal
                });
                clearTimeout(timeoutId2);
                data = await response.json();
                
                if (data.status === 200) {
                    data = {
                        logradouro: data.address,
                        bairro: data.district,
                        localidade: data.city,
                        uf: data.state
                    };
                }
            } catch (error2) {
                try {
                    const controller3 = new AbortController();
                    const timeoutId3 = setTimeout(() => controller3.abort(), 8000);
                    
                    response = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`, {
                        signal: controller3.signal
                    });
                    clearTimeout(timeoutId3);
                    data = await response.json();
                    
                    if (data.street) {
                        data = {
                            logradouro: data.street,
                            bairro: data.neighborhood,
                            localidade: data.city,
                            uf: data.state
                        };
                    }
                } catch (error3) {
                    throw new Error('Não foi possível conectar aos serviços de CEP');
                }
            }
        }
        
        if (data.logradouro && data.localidade) {
            document.getElementById('endereco').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('estado').value = data.uf || '';
            
            cepInput.classList.remove('loading');
            cepInput.classList.add('success');
            mostrarStatusCep('CEP encontrado com sucesso!', 'success');
            
            setTimeout(() => {
                cepInput.classList.remove('success');
                cepStatus.style.display = 'none';
            }, 3000);
        } else {
            throw new Error('CEP não encontrado');
        }
    } catch (error) {
        cepInput.classList.remove('loading');
        cepInput.classList.add('error');
        
        if (error.name === 'AbortError') {
            mostrarStatusCep('Tempo limite excedido. Verifique sua conexão e tente novamente.', 'error');
        } else {
            mostrarStatusCep('CEP não encontrado. Preencha os campos manualmente.', 'error');
        }
        
        document.getElementById('endereco').value = '';
        document.getElementById('bairro').value = '';
        document.getElementById('cidade').value = '';
        document.getElementById('estado').value = '';
        
        setTimeout(() => {
            cepInput.classList.remove('error');
            cepStatus.style.display = 'none';
        }, 5000);
    }
}

function mostrarStatusCep(mensagem, tipo) {
    const cepStatus = document.getElementById('cep-status');
    cepStatus.textContent = mensagem;
    cepStatus.className = `cep-status ${tipo}`;
    cepStatus.style.display = 'block';
}

function limparCamposEndereco() {
    document.getElementById('cep').value = '';
    document.getElementById('endereco').value = '';
    document.getElementById('numero').value = '';
    document.getElementById('complemento').value = '';
    document.getElementById('bairro').value = '';
    document.getElementById('cidade').value = '';
    document.getElementById('estado').value = '';
    
    const cepStatus = document.getElementById('cep-status');
    cepStatus.style.display = 'none';
    
    const cepInput = document.getElementById('cep');
    cepInput.classList.remove('loading', 'success', 'error');
    
    document.getElementById('cep').focus();
}

function formatarCep(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
}

function toggleCamposCartao() {
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    const camposCartao = document.getElementById('campos-cartao');
    
    if (formaPagamento === 'pix') {
        camposCartao.style.display = 'none';
    } else {
        camposCartao.style.display = 'block';
    }
}

function formatarNumeroCartao(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value;
}

function formatarValidade(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/, '$1/$2');
    e.target.value = value;
}

function formatarCvv(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
}

function validarFormulario() {
    const cep = document.getElementById('cep').value;
    const endereco = document.getElementById('endereco').value;
    const numero = document.getElementById('numero').value;
    const bairro = document.getElementById('bairro').value;
    const cidade = document.getElementById('cidade').value;
    const estado = document.getElementById('estado').value;
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    
    if (!cep || !endereco || !numero || !bairro || !cidade || !estado) {
        alert('Por favor, preencha todos os campos do endereço');
        return false;
    }
    
    if (formaPagamento !== 'pix') {
        const numeroCartao = document.getElementById('numero-cartao').value;
        const validade = document.getElementById('validade').value;
        const cvv = document.getElementById('cvv').value;
        const nomeCartao = document.getElementById('nome-cartao').value;
        
        if (!numeroCartao || !validade || !cvv || !nomeCartao) {
            alert('Por favor, preencha todos os campos do cartão');
            return false;
        }
        
        if (numeroCartao.replace(/\s/g, '').length < 13) {
            alert('Número do cartão inválido');
            return false;
        }
        
        if (cvv.length < 3) {
            alert('CVV inválido');
            return false;
        }
    }
    
    return true;
}

// Finalizar compra - ENVIA PARA O BANCO DE DADOS
async function finalizarCompra() {
    if (!validarFormulario()) {
        return;
    }
    
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    const total = parseFloat(document.getElementById('total').textContent.replace('R$ ', '').replace(',', '.'));
    const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('R$ ', '').replace(',', '.'));
    const frete = document.getElementById('frete').textContent === 'Grátis' ? 0 : parseFloat(document.getElementById('frete').textContent.replace('R$ ', '').replace(',', '.'));
    
    const btnFinalizar = document.querySelector('button[onclick="finalizarCompra()"]');
    btnFinalizar.disabled = true;
    btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    // Timeout de 60 segundos para a requisição
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 60000);
    
    try {
        const dadosPedido = {
            action: 'create_order',
            produtos: carrinho.map(item => ({
                id_produto: item.id_produto || item.id,
                quantidade: item.quantidade,
                tamanho: item.tamanho || null,
                cor: item.cor || null,
                preco: item.preco
            })),
            endereco: {
                cep: document.getElementById('cep').value,
                logradouro: document.getElementById('endereco').value,
                numero: document.getElementById('numero').value,
                complemento: document.getElementById('complemento').value,
                bairro: document.getElementById('bairro').value,
                cidade: document.getElementById('cidade').value,
                estado: document.getElementById('estado').value
            },
            pagamento: {
                metodo: formaPagamento,
                numero_cartao: formaPagamento !== 'pix' ? document.getElementById('numero-cartao').value.replace(/\s/g, '') : null,
                validade: formaPagamento !== 'pix' ? document.getElementById('validade').value : null,
                cvv: formaPagamento !== 'pix' ? document.getElementById('cvv').value : null,
                nome_cartao: formaPagamento !== 'pix' ? document.getElementById('nome-cartao').value : null
            },
            valores: {
                subtotal: subtotal,
                frete: frete,
                desconto: descontoAplicado,
                total: total
            },
            cupom: cupomAtivo ? Object.keys(cuponsValidos).find(key => cuponsValidos[key] === cupomAtivo) : null
        };
        
        const response = await fetch('controller/PedidoController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(dadosPedido),
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Limpar carrinho do banco de dados
            await fetch('controller/CartController.php?action=clear_cart', {
                method: 'POST'
            });
            
            alert(`Compra finalizada com sucesso!\n\nPedido #${result.pedido_id}\nForma de pagamento: ${formaPagamento.toUpperCase()}\nTotal: R$ ${total.toFixed(2).replace('.', ',')}\n\nVocê receberá um email com os detalhes da compra.`);
            
            // Redirecionar para página de pedidos
            window.location.href = 'meus-pedidos.php';
        } else {
            alert('Erro ao finalizar compra: ' + (result.message || 'Erro desconhecido'));
            btnFinalizar.disabled = false;
            btnFinalizar.innerHTML = '<i class="fas fa-lock"></i> Finalizar Compra';
        }
    } catch (error) {
        clearTimeout(timeoutId);
        console.error('Erro:', error);
        let errorMessage = 'Erro ao finalizar compra. Tente novamente.';
        
        if (error.name === 'AbortError') {
            errorMessage = 'A requisição demorou muito. O servidor pode estar sobrecarregado. Por favor, tente novamente em alguns instantes.';
        } else if (error.message.includes('Lock wait timeout') || error.message.includes('1205')) {
            errorMessage = 'O sistema está processando muitas solicitações. Por favor, aguarde alguns segundos e tente novamente.';
        } else if (error.message.includes('HTTP error')) {
            errorMessage = 'Erro de conexão com o servidor. Verifique sua conexão e tente novamente.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Não foi possível conectar ao servidor. Verifique sua conexão.';
        }
        
        alert(errorMessage);
        btnFinalizar.disabled = false;
        btnFinalizar.innerHTML = '<i class="fas fa-lock"></i> Finalizar Compra';
    }
}

