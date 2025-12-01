// Carregar pedidos quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    carregarPedidos();
});

// Carregar pedidos do banco de dados (via PHP)
function carregarPedidos() {
    const listaPedidos = document.getElementById('lista-pedidos');
    
    // Usa dados do PHP se disponíveis
    let pedidos = [];
    if (typeof pedidosData !== 'undefined' && Array.isArray(pedidosData)) {
        pedidos = pedidosData.map(pedido => ({
            id_pedido: pedido.id_pedido,
            numero: pedido.id_pedido,
            data: pedido.data_pedido,
            status: pedido.status || 'Pendente',
            total: parseFloat(pedido.total),
            subtotal: parseFloat(pedido.total), // Aproximação - pode ser ajustado se houver campos separados
            frete: 0, // Aproximação - pode ser ajustado se houver campo de frete
            desconto: 0, // Aproximação
            cupom: null,
            produtos: (pedido.itens || []).map(item => {
                // Calcula preço unitário a partir do subtotal e quantidade
                const quantidade = parseInt(item.quantidade) || 1;
                const subtotal = parseFloat(item.subtotal || 0);
                const precoUnitario = quantidade > 0 ? subtotal / quantidade : 0;
                
                return {
                    id_produto: item.id_produto,
                    nome: item.produto_nome || 'Produto',
                    quantidade: quantidade,
                    preco: precoUnitario,
                    tamanho: null, // Não armazenado na tabela item_do_pedido
                    cor: null, // Não armazenado na tabela item_do_pedido
                    imagem: item.imagem || 'img/logo.png'
                };
            }),
            endereco: {
                logradouro: pedido.logradouro || '',
                numero: '',
                complemento: '',
                bairro: '',
                cidade: pedido.cidade || '',
                estado: pedido.estado || '',
                cep: pedido.cep || ''
            },
            pagamento: pedido.metodo_pagamento || 'PIX'
        }));
    }
    
    if (pedidos.length === 0) {
        listaPedidos.innerHTML = `
            <div class="pedido-vazio">
                <i class="fas fa-shopping-bag"></i>
                <h5>Nenhum pedido encontrado</h5>
                <p>Você ainda não realizou nenhum pedido.</p>
                <a href="index.php" class="btn btn-primary">Fazer compras</a>
            </div>
        `;
        return;
    }
    
    listaPedidos.innerHTML = '';
    
    pedidos.forEach((pedido) => {
        const pedidoDiv = document.createElement('div');
        pedidoDiv.className = 'pedido-item';
        
        pedidoDiv.innerHTML = `
            <div class="pedido-header">
                <div class="pedido-info">
                    <div class="pedido-numero">Pedido #${String(pedido.numero).padStart(4, '0')}</div>
                    <div class="pedido-data">${formatarData(pedido.data)}</div>
                </div>
                <div class="pedido-status status-${pedido.status.toLowerCase().replace(' ', '-')}">${pedido.status}</div>
            </div>
            <div class="pedido-body">
                ${gerarProdutosHTML(pedido.produtos)}
                ${gerarResumoHTML(pedido)}
                ${gerarEnderecoHTML(pedido.endereco)}
                ${gerarPagamentoHTML(pedido.pagamento)}
            </div>
        `;
        
        listaPedidos.appendChild(pedidoDiv);
    });
}

// Gerar HTML dos produtos
function gerarProdutosHTML(produtos) {
    if (!produtos || produtos.length === 0) {
        return '<h6><i class="fas fa-box"></i> Produtos</h6><p>Nenhum produto encontrado.</p>';
    }
    
    let html = '<h6><i class="fas fa-box"></i> Produtos</h6>';
    
    produtos.forEach(produto => {
        const variacoes = [];
        if (produto.cor) variacoes.push(`Cor: ${produto.cor}`);
        if (produto.tamanho) variacoes.push(`Tamanho: ${produto.tamanho}`);
        const variacoesTexto = variacoes.length > 0 ? variacoes.join(' | ') : '';
        
        html += `
            <div class="produto-pedido">
                <img src="${produto.imagem}" alt="${produto.nome}">
                <div class="produto-info">
                    <div class="produto-nome">${produto.nome}</div>
                    <div class="produto-detalhes">Quantidade: ${produto.quantidade}</div>
                    ${variacoesTexto ? `<div class="produto-detalhes">${variacoesTexto}</div>` : ''}
                </div>
                <div class="produto-preco">R$ ${(produto.preco * produto.quantidade).toFixed(2).replace('.', ',')}</div>
            </div>
        `;
    });
    
    return html;
}

// Gerar HTML do resumo
function gerarResumoHTML(pedido) {
    return `
        <div class="pedido-resumo">
            <h6><i class="fas fa-receipt"></i> Resumo</h6>
            ${pedido.subtotal !== pedido.total ? `
                <div class="resumo-item">
                    <span>Subtotal:</span>
                    <span>R$ ${pedido.subtotal.toFixed(2).replace('.', ',')}</span>
                </div>
            ` : ''}
            ${pedido.frete > 0 ? `
                <div class="resumo-item">
                    <span>Frete:</span>
                    <span>R$ ${pedido.frete.toFixed(2).replace('.', ',')}</span>
                </div>
            ` : pedido.frete === 0 && pedido.subtotal !== pedido.total ? `
                <div class="resumo-item">
                    <span>Frete:</span>
                    <span>Grátis</span>
                </div>
            ` : ''}
            ${pedido.desconto > 0 ? `
                <div class="resumo-item">
                    <span>Desconto:</span>
                    <span>-R$ ${pedido.desconto.toFixed(2).replace('.', ',')}</span>
                </div>
            ` : ''}
            <div class="resumo-item total">
                <span>Total:</span>
                <span>R$ ${pedido.total.toFixed(2).replace('.', ',')}</span>
            </div>
            ${pedido.cupom ? `
                <div class="resumo-item">
                    <span>Cupom aplicado:</span>
                    <span>${pedido.cupom}</span>
                </div>
            ` : ''}
        </div>
    `;
}

// Gerar HTML do endereço
function gerarEnderecoHTML(endereco) {
    if (!endereco || !endereco.logradouro) {
        return '';
    }
    
    return `
        <div class="endereco-info">
            <h6><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h6>
            <div class="endereco-texto">
                ${endereco.logradouro}${endereco.numero ? ', ' + endereco.numero : ''}
                ${endereco.complemento ? `<br>${endereco.complemento}` : ''}
                ${endereco.bairro ? `<br>${endereco.bairro}` : ''}
                ${endereco.cidade ? `<br>${endereco.cidade}` : ''}${endereco.estado ? ' - ' + endereco.estado : ''}
                ${endereco.cep ? `<br>CEP: ${endereco.cep}` : ''}
            </div>
        </div>
    `;
}

// Gerar HTML do pagamento
function gerarPagamentoHTML(pagamento) {
    if (!pagamento) {
        return '';
    }
    
    const pagamentoTextos = {
        'pix': 'PIX',
        'Pix': 'PIX',
        'debito': 'Cartão de Débito',
        'Débito': 'Cartão de Débito',
        'credito': 'Cartão de Crédito',
        'Crédito': 'Cartão de Crédito'
    };
    
    const textoPagamento = pagamentoTextos[pagamento] || pagamento;
    
    return `
        <div class="pagamento-info">
            <h6><i class="fas fa-credit-card"></i> Forma de Pagamento</h6>
            <div class="pagamento-tipo">${textoPagamento}</div>
        </div>
    `;
}

// Formatar data
function formatarData(dataString) {
    if (!dataString) return 'Data não disponível';
    
    const data = new Date(dataString);
    if (isNaN(data.getTime())) {
        return dataString; // Retorna a string original se não for uma data válida
    }
    
    return data.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
