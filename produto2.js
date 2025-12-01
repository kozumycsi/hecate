function alterarImagem(elemento) {
    const imagemPrincipal = document.getElementById('imagem-atual');
    imagemPrincipal.style.opacity = 0;
   
    setTimeout(() => {
        imagemPrincipal.src = elemento.src;
        imagemPrincipal.style.opacity = 1;
    }, 300);
}

function adicionarComentario() {
    // Obtém o texto do comentário do campo de entrada
    const comentarioInput = document.getElementById("comentario-input");
    const comentarioTexto = comentarioInput.value.trim();

    // Verifica se o campo de comentário não está vazio
    if (comentarioTexto) {
        // Cria um novo elemento de comentário
        const comentarioDiv = document.createElement("div");
        comentarioDiv.className = "comentario";

        // Cria um parágrafo com o texto do comentário
        const comentarioParagrafo = document.createElement("p");
        comentarioParagrafo.textContent = comentarioTexto;

        // Adiciona o parágrafo ao div do comentário
        comentarioDiv.appendChild(comentarioParagrafo);

        // Adiciona o novo comentário ao container de comentários
        document.getElementById("comentarios-lista").appendChild(comentarioDiv);

        // Limpa o campo de entrada
        comentarioInput.value = "";
    } else {
        alert("Por favor, escreva um comentário antes de enviar.");
    }
}function alterarImagem(elemento) {
    const imagemPrincipal = document.getElementById('imagem-atual');
    imagemPrincipal.style.opacity = 0;
   
    setTimeout(() => {
        imagemPrincipal.src = elemento.src;
        imagemPrincipal.style.opacity = 1;
    }, 300);
}

function adicionarComentario() {
    // Obtém o texto do comentário do campo de entrada
    const comentarioInput = document.getElementById("comentario-input");
    const comentarioTexto = comentarioInput.value.trim();

    // Verifica se o campo de comentário não está vazio
    if (comentarioTexto) {
        // Cria um novo elemento de comentário
        const comentarioDiv = document.createElement("div");
        comentarioDiv.className = "comentario";

        // Cria um parágrafo com o texto do comentário
        const comentarioParagrafo = document.createElement("p");
        comentarioParagrafo.textContent = comentarioTexto;

        // Adiciona o parágrafo ao div do comentário
        comentarioDiv.appendChild(comentarioParagrafo);

        // Adiciona o novo comentário ao container de comentários
        document.getElementById("comentarios-lista").appendChild(comentarioDiv);

        // Limpa o campo de entrada
        comentarioInput.value = "";
    } else {
        alert("Por favor, escreva um comentário antes de enviar.");
    }
}