
const messageContainer = document.getElementById('message-container');
const messageText = document.getElementById('message-text');
const backToHomeLink = document.createElement('a'); 


backToHomeLink.href = "#"; 
backToHomeLink.textContent = "Voltar à página de início?";
backToHomeLink.style.marginLeft = "10px"; 
backToHomeLink.style.textDecoration = "underline"; 
backToHomeLink.style.color = "#007bff"; 


function showMessage(message) {
    messageContainer.style.display = 'block';
    messageText.textContent = message;

   
    if (!messageContainer.contains(backToHomeLink)) {
        messageContainer.appendChild(backToHomeLink);
    }

    
    setTimeout(() => {
        messageContainer.style.display = 'none';
    }, 5000);
}


function saveToLocalStorage(nome, email, senha) {
    const users = JSON.parse(localStorage.getItem('users')) || []; 

    
    const existingUser = users.find(user => user.email === email);
    if (existingUser) {
        return 'exists'; 
    }

  
    users.push({ nome, email, senha });
    localStorage.setItem('users', JSON.stringify(users));
    return 'saved'; 
}


document.querySelector('form').addEventListener('submit', (event) => {
    event.preventDefault(); 
    const nome = document.getElementById('nome').value.trim();
    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value.trim();
    const confirmSenha = document.getElementById('confirm-senha').value.trim();

   
    if (!nome || !email || !senha || !confirmSenha) {
        showMessage('Preencha todos os campos!');
        return;
    }

    if (senha !== confirmSenha) {
        showMessage('As senhas não conferem!');
        return;
    }

    
    const result = saveToLocalStorage(nome, email, senha);

    if (result === 'exists') {
        showMessage('Bem-vindo de novo!');
    } else if (result === 'saved') {
        showMessage('Cadastro realizado com sucesso!');
    }

  
    document.querySelector('form').reset();
});




document.querySelectorAll('.eye-icon').forEach(eyeIcon => {
    eyeIcon.addEventListener('click', function() {
       
        const passwordField = this.previousElementSibling;
        
       
        if (passwordField.type === "password") {
            passwordField.type = "text"; 
            this.innerHTML = '&#128065;'; 
        } else {
            passwordField.type = "password"; 
            this.innerHTML = '&#128064;'; 
        }
    });
});
function toggleSearchBar() {
    const searchInput = document.getElementById("searchInput");
  
    if (searchInput.style.display === "none") {
        searchInput.style.display = "block";
        searchInput.focus(); 
    } else {
        searchInput.style.display = "none";
    }
}