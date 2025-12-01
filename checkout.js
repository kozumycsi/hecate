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
    carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
    
    if (carrinho.length === 0) {
        window.location.href = 'carrinho.php';
        return;
    }
    
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
                R$ ${(produto.preco * produto.quantidade).toFixed(2)}
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
    
    document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
    document.getElementById('frete').textContent = frete === 0 ? 'Grátis' : `R$ ${frete.toFixed(2)}`;
    
    if (desconto > 0) {
        document.getElementById('desconto-item').style.display = 'flex';
        document.getElementById('desconto').textContent = `-R$ ${desconto.toFixed(2)}`;
    } else {
        document.getElementById('desconto-item').style.display = 'none';
    }
    
    document.getElementById('total').textContent = `R$ ${total.toFixed(2)}`;
}

// Buscar CEP
async function buscarCep() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    const cepInput = document.getElementById('cep');
    const cepStatus = document.getElementById('cep-status');
    
    if (cep.length !== 8) {
        mostrarStatusCep('Digite um CEP válido (8 dígitos)', 'error');
        return;
    }
    
    // Mostrar loading
    cepInput.classList.add('loading');
    mostrarStatusCep('Buscando CEP...', 'loading');
    
    try {
        // Configurar timeout para evitar demora excessiva
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 segundos
        
        // Tentar primeira API (ViaCEP)
        let response = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        let data = await response.json();
        
        // Se ViaCEP falhar, tentar API alternativa
        if (data.erro || !data.logradouro) {
            console.log('ViaCEP falhou, tentando API alternativa...');
            try {
                const controller2 = new AbortController();
                const timeoutId2 = setTimeout(() => controller2.abort(), 8000); // 8 segundos
                
                response = await fetch(`https://cep.awesomeapi.com.br/json/${cep}`, {
                    signal: controller2.signal
                });
                clearTimeout(timeoutId2);
                data = await response.json();
                
                // Adaptar formato da API alternativa
                if (data.status === 200) {
                    data = {
                        logradouro: data.address,
                        bairro: data.district,
                        localidade: data.city,
                        uf: data.state
                    };
                }
            } catch (error2) {
                console.log('Segunda API falhou, tentando terceira...');
                // Terceira API como fallback
                try {
                    const controller3 = new AbortController();
                    const timeoutId3 = setTimeout(() => controller3.abort(), 8000); // 8 segundos
                    
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
                    console.error('Todas as APIs falharam:', error3);
                    throw new Error('Não foi possível conectar aos serviços de CEP');
                }
            }
        }
        
        if (data.logradouro && data.localidade) {
            document.getElementById('endereco').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('estado').value = data.uf || '';
            
            // Feedback de sucesso
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
        console.error('Erro ao buscar CEP:', error);
        
        // Feedback de erro
        cepInput.classList.remove('loading');
        cepInput.classList.add('error');
        
        // Mensagem específica para timeout
        if (error.name === 'AbortError') {
            mostrarStatusCep('Tempo limite excedido. Verifique sua conexão e tente novamente.', 'error');
        } else {
            mostrarStatusCep('CEP não encontrado. Preencha os campos manualmente.', 'error');
        }
        
        // Limpar campos
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

// Função para mostrar status do CEP
function mostrarStatusCep(mensagem, tipo) {
    const cepStatus = document.getElementById('cep-status');
    cepStatus.textContent = mensagem;
    cepStatus.className = `cep-status ${tipo}`;
    cepStatus.style.display = 'block';
}

// Função para limpar campos de endereço
function limparCamposEndereco() {
    document.getElementById('cep').value = '';
    document.getElementById('endereco').value = '';
    document.getElementById('numero').value = '';
    document.getElementById('complemento').value = '';
    document.getElementById('bairro').value = '';
    document.getElementById('cidade').value = '';
    document.getElementById('estado').value = '';
    
    // Limpar status do CEP
    const cepStatus = document.getElementById('cep-status');
    cepStatus.style.display = 'none';
    
    // Limpar classes de estilo
    const cepInput = document.getElementById('cep');
    cepInput.classList.remove('loading', 'success', 'error');
    
    // Focar no campo CEP
    document.getElementById('cep').focus();
}

// Formatar CEP
function formatarCep(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
}

// Toggle campos do cartão
function toggleCamposCartao() {
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    const camposCartao = document.getElementById('campos-cartao');
    
    if (formaPagamento === 'pix') {
        camposCartao.style.display = 'none';
    } else {
        camposCartao.style.display = 'block';
    }
}

// Formatar número do cartão
function formatarNumeroCartao(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value;
}

// Formatar validade
function formatarValidade(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/, '$1/$2');
    e.target.value = value;
}

// Formatar CVV
function formatarCvv(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
}

// Validar formulário
function validarFormulario() {
    const cep = document.getElementById('cep').value;
    const endereco = document.getElementById('endereco').value;
    const numero = document.getElementById('numero').value;
    const bairro = document.getElementById('bairro').value;
    const cidade = document.getElementById('cidade').value;
    const estado = document.getElementById('estado').value;
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    
    // Validar endereço
    if (!cep || !endereco || !numero || !bairro || !cidade || !estado) {
        alert('Por favor, preencha todos os campos do endereço');
        return false;
    }
    
    // Validar cartão se necessário
    if (formaPagamento !== 'pix') {
        const numeroCartao = document.getElementById('numero-cartao').value;
        const validade = document.getElementById('validade').value;
        const cvv = document.getElementById('cvv').value;
        const nomeCartao = document.getElementById('nome-cartao').value;
        
        if (!numeroCartao || !validade || !cvv || !nomeCartao) {
            alert('Por favor, preencha todos os campos do cartão');
            return false;
        }
        
        // Validar número do cartão (simples)
        if (numeroCartao.replace(/\s/g, '').length < 13) {
            alert('Número do cartão inválido');
            return false;
        }
        
        // Validar CVV
        if (cvv.length < 3) {
            alert('CVV inválido');
            return false;
        }
    }
    
    return true;
}

// Finalizar compra
function finalizarCompra() {
    if (!validarFormulario()) {
        return;
    }
    
    const formaPagamento = document.querySelector('input[name="pagamento"]:checked').value;
    const total = parseFloat(document.getElementById('total').textContent.replace('R$ ', '').replace(',', '.'));
    
    // Simular processamento
    const btnFinalizar = document.querySelector('button[onclick="finalizarCompra()"]');
    btnFinalizar.disabled = true;
    btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    setTimeout(() => {
        // Criar resumo da compra
        const resumoCompra = {
            produtos: carrinho,
            endereco: {
                cep: document.getElementById('cep').value,
                endereco: document.getElementById('endereco').value,
                numero: document.getElementById('numero').value,
                complemento: document.getElementById('complemento').value,
                bairro: document.getElementById('bairro').value,
                cidade: document.getElementById('cidade').value,
                estado: document.getElementById('estado').value
            },
            pagamento: formaPagamento,
            subtotal: parseFloat(document.getElementById('subtotal').textContent.replace('R$ ', '').replace(',', '.')),
            frete: document.getElementById('frete').textContent === 'Grátis' ? 0 : parseFloat(document.getElementById('frete').textContent.replace('R$ ', '').replace(',', '.')),
            desconto: descontoAplicado,
            total: total,
            cupom: cupomAtivo ? Object.keys(cuponsValidos).find(key => cuponsValidos[key] === cupomAtivo) : null,
            data: new Date().toISOString()
        };
        
        // Salvar no localStorage (simulação de banco de dados)
        const pedidos = JSON.parse(localStorage.getItem('pedidos')) || [];
        pedidos.push(resumoCompra);
        localStorage.setItem('pedidos', JSON.stringify(pedidos));
        
        // Limpar carrinho
        localStorage.removeItem('carrinho');
        
        // Mostrar sucesso
        alert(`Compra finalizada com sucesso!\n\nForma de pagamento: ${formaPagamento.toUpperCase()}\nTotal: R$ ${total.toFixed(2)}\n\nVocê receberá um email com os detalhes da compra.`);
        
        // Redirecionar para página inicial
        window.location.href = 'index.php';
    }, 2000);
} 