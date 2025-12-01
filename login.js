window.onload = function() {
    const entrarBtn = document.getElementById('entrarBtn');
   
    // Cria a mensagem de sucesso
    const loginSuccessMessage = document.createElement('div');
    loginSuccessMessage.style.position = 'fixed';
    loginSuccessMessage.style.top = '150px'; // Posição da mensagem
    loginSuccessMessage.style.left = '50%';
    loginSuccessMessage.style.transform = 'translateX(-50%)';
    loginSuccessMessage.style.zIndex = '1000';
    loginSuccessMessage.style.display = 'none'; // Oculta inicialmente
   
    // Cria o link "Voltar para a página de início"
    const returnLink = document.createElement('a');
    returnLink.href = 'https://igorgabs05.github.io/'; // Link para a página de início
    returnLink.innerText = 'Voltar para a página de início?';
    returnLink.style.marginLeft = '10px'; // Espaço entre a mensagem e o link
    returnLink.style.textDecoration = "underline"; // Destaque o link
    returnLink.style.color = "#007bff"; // Cor azul para o link
   
    // Adiciona o link ao lado da mensagem de sucesso
    loginSuccessMessage.appendChild(returnLink);
   
    // Adiciona a mensagem ao corpo da página
    document.body.appendChild(loginSuccessMessage);

    // Função para salvar as credenciais no localStorage
    function salvarCredenciais(email, senha) {
        const users = JSON.parse(localStorage.getItem('users')) || []; // Recupera usuários existentes ou inicia vazio
        users.push({ email, senha });
        localStorage.setItem('users', JSON.stringify(users)); // Salva no localStorage
    }

    // Função para verificar as credenciais
    function verificarCredenciais(email, senha) {
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const user = users.find(user => user.email === email);
        return user && user.senha === senha;
    }

    // Adicionar o evento de clique no botão "Entrar"
    entrarBtn.addEventListener('click', function() {
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value.trim();

        // Validação básica
        if (!email || !senha) {
            loginSuccessMessage.textContent = 'Preencha todos os campos!';
            loginSuccessMessage.style.color = 'red';
            loginSuccessMessage.style.display = 'block';
            return;
        }

        // Verifica se já existe um usuário salvo
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const existingUser = users.find(user => user.email === email);

        if (existingUser) {
            // Verifica as credenciais e exibe a mensagem correspondente
            if (verificarCredenciais(email, senha)) {
                window.location.href = 'index.html'; // Redireciona para a página inicial
            } else {
                loginSuccessMessage.innerHTML = 'Usuário ou senha incorretos! <a href="#" style="color: #007bff; text-decoration: underline;">vc esqueceu sua senha?</a>';
                loginSuccessMessage.style.color = 'red';
                loginSuccessMessage.style.display = 'block';
            }
        } else {
            // Caso seja o primeiro login, salva as credenciais e redireciona
            salvarCredenciais(email, senha);
            window.location.href = 'index.html'; // Redireciona para a página inicial
        }

        // Não é mais necessário exibir a mensagem de sucesso aqui, pois o redirecionamento cuidará disso
        // Adiciona o link "Voltar para a página de início" ao lado da mensagem
        // loginSuccessMessage.appendChild(returnLink);
        // loginSuccessMessage.style.display = 'block';

        // Limpar os campos de input
        document.getElementById('email').value = '';
        document.getElementById('senha').value = '';

        // Ocultar a mensagem após 5 segundos
        setTimeout(function() {
            loginSuccessMessage.style.display = 'none';
        }, 5000);
    });
};

function toggleSearchBar() {
    const searchInput = document.getElementById("searchInput");
    // Alterna a exibição do campo de pesquisa
    if (searchInput.style.display === "none") {
        searchInput.style.display = "block";
        searchInput.focus(); // Coloca o foco no campo de pesquisa automaticamente
    } else {
        searchInput.style.display = "none";
    }
}

// Alternar visibilidade da senha (ícone olho)
document.querySelectorAll('.eye-icon').forEach(eyeIcon => {
    eyeIcon.addEventListener('click', function() {
        const passwordField = this.previousElementSibling;
        
        if (passwordField.type === "password") {
            passwordField.type = "text"; // Torna a senha visível
            this.innerHTML = '&#128065;'; // Muda para "olho aberto"
        } else {
            passwordField.type = "password"; // Torna a senha invisível
            this.innerHTML = '&#128064;'; // Muda para "olho fechado"
        }
    });
});
