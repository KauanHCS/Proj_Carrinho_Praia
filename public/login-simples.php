<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Simples - Debug</title>
    <style>
        body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
        input, button { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: #0066cc; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Login Simplificado (Debug)</h2>
    
    <form id="loginForm">
        <input type="email" id="email" placeholder="Email" required value="teste@teste.com">
        <input type="password" id="password" placeholder="Senha" required value="123456">
        <button type="submit">Entrar</button>
    </form>
    
    <div id="resultado"></div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const resultado = document.getElementById('resultado');
            resultado.innerHTML = '<p>Fazendo login...</p>';
            
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            
            fetch('../src/Controllers/actions.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        resultado.innerHTML = '<p class="success">✓ Login realizado!</p>';
                        
                        // Salvar dados
                        sessionStorage.setItem('user', JSON.stringify(data.data));
                        
                        // Redirecionar
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1000);
                    } else {
                        resultado.innerHTML = '<p class="error">❌ ' + data.message + '</p>';
                    }
                } catch (error) {
                    resultado.innerHTML = '<p class="error">❌ Erro ao processar resposta: ' + error + '</p><pre>' + text + '</pre>';
                }
            })
            .catch(error => {
                resultado.innerHTML = '<p class="error">❌ Erro de conexão: ' + error + '</p>';
                console.error('Erro:', error);
            });
        });
    </script>
    
    <hr>
    <p><small>Versão simplificada para debug. <a href="login.php">Usar login normal</a></small></p>
</body>
</html>
