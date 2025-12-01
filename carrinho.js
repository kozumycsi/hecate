function carregarCarrinho() {
  // O carrinho agora é carregado pelo PHP, então apenas atualizamos o total
  atualizarTotal();
}

function atualizarTotal() {
  if (typeof cartItems === 'undefined') {
    return;
  }
  
  let total = 0;
  let quantidadeItens = 0;

  const checkboxes = document.querySelectorAll('.carrinho-item input[type="checkbox"]');

  checkboxes.forEach((checkbox, index) => {
    if (checkbox.checked && cartItems[index]) {
      const produto = cartItems[index];
      total += parseFloat(produto.preco) * parseInt(produto.quantidade);
      quantidadeItens += parseInt(produto.quantidade);
    }
  });

  document.getElementById('totalBarra').textContent = total.toFixed(2).replace('.', ',');
  document.getElementById('quantidadeItens').textContent = quantidadeItens;
}

function alterarQuantidade(itemId, change, estoque) {
  // Encontra o item no array
  const item = cartItems.find(i => parseInt(i.id) === itemId);
  if (!item) return;

  const quantidadeAtual = parseInt(item.quantidade);
  const novaQuantidade = quantidadeAtual + change;
  
  // Impede quantidade menor que 1
  if (novaQuantidade < 1) {
    return;
  } 
  // Verifica se não excede o estoque disponível
  if (estoque !== undefined && estoque !== null && novaQuantidade > estoque) {
    alert('Quantidade máxima disponível no estoque: ' + estoque);
    return;
  }

  // Atualiza via AJAX
  const formData = new FormData();
  formData.append('item_id', itemId);
  formData.append('quantity', novaQuantidade);

  fetch('controller/CartController.php?action=update_quantity', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Atualiza o item no array local
      item.quantidade = novaQuantidade;
      // Recarrega a página para atualizar o carrinho
      location.reload();
    } else {
      alert(data.message || 'Erro ao atualizar quantidade.');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    alert('Erro ao atualizar quantidade.');
  });
}

function removerDoCarrinho(itemId) {
  if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
    return;
  }

  const formData = new FormData();
  formData.append('item_id', itemId);

  fetch('controller/CartController.php?action=remove_item', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message || 'Erro ao remover item.');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    alert('Erro ao remover item.');
  });
}

function selecionarTodosItens() {
  const checkbox = document.getElementById('selecionarTudo');
  const checkboxes = document.querySelectorAll('.carrinho-item input[type="checkbox"]');

  checkboxes.forEach(cb => {
    cb.checked = checkbox.checked;
  });

  atualizarTotal(); // Atualiza o total após a seleção/deseleção
}

function excluirSelecionados() {
  const checkboxes = document.querySelectorAll('.carrinho-item input[type="checkbox"]');
  const selecionados = Array.from(checkboxes).filter(cb => cb.checked);

  if (selecionados.length === 0) {
    alert('Selecione ao menos um produto para excluir.');
    return;
  }

  const mensagem = selecionados.length === checkboxes.length
    ? 'Tem certeza que deseja remover todos os produtos do carrinho?'
    : `Tem certeza que deseja remover ${selecionados.length} item(ns) selecionado(s)?`;

  if (!confirm(mensagem)) {
    return;
  }

  // Coleta os IDs dos itens selecionados
  const itemIds = [];
  selecionados.forEach(checkbox => {
    const itemDiv = checkbox.closest('.carrinho-item');
    if (itemDiv) {
      const itemId = itemDiv.dataset.itemId;
      if (itemId) {
        itemIds.push(parseInt(itemId));
      }
    }
  });

  if (itemIds.length === 0) {
    return;
  }

  const formData = new FormData();
  itemIds.forEach(id => {
    formData.append('item_ids[]', id);
  });

  fetch('controller/CartController.php?action=remove_items', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message || 'Erro ao remover itens.');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    alert('Erro ao remover itens.');
  });
}

function finalizarCompra() {
  if (typeof cartItems === 'undefined' || cartItems.length === 0) {
    alert('O carrinho está vazio.');
    return;
  }

  // Redirecionar para a página de finalização da compra
  window.location.href = 'finalizar-compra.php';
}

// Carrega o total ao abrir a página
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', carregarCarrinho);
} else {
  carregarCarrinho();
}
