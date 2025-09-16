<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Carrinho de Praia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .login-header i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .nav-tabs {
            border: none;
        }
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            margin: 0;
            color: #6c757d;
            font-weight: 500;
            padding: 15px 76px;
            text-align: center;
        }
        .nav-tabs .nav-link.active {
            color: #0066cc;
            border-bottom: 3px solid #0066cc;
            background: transparent;
        }
        .tab-content {
            padding: 30px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.25rem rgba(0, 102, 204, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .form-check-input:checked {
            background-color: #0066cc;
            border-color: #0066cc;
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #0066cc;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .social-login {
            margin-top: 20px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .social-login p {
            color: #6c757d;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn-social {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 1.2rem;
        }
        .btn-google {
            background-color: #ea4335;
            color: white;
        }
        .btn-facebook {
            background-color: #3b5998;
            color: white;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
        }
        .register-link a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        /* Estilo para o botão do Google */
        .google-signin {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        .btn-google-custom {
            background: white;
            color: #ea4335;
            border: 2px solid #ea4335;
            border-radius: 10px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            cursor: pointer;
        }
        .btn-google-custom:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-google-custom i {
            font-size: 1.5rem;
        }
        /* Container para o botão do Google */
        #google-login-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        #google-login-container > div {
            width: 100% !important;
        }
        /* Estilo para garantir que o botão sempre apareça */
        .g_id_signin {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }
        .g_id_signin > div {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container mx-auto">
            <div class="login-header">
                <i class="bi bi-cart"></i>
                <h2>Carrinho de Praia</h2>
                <p>Sistema de Gestão de Vendas</p>
            </div>
            <div class="nav nav-tabs" role="tablist">
                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">
                    <i class="bi bi-person-plus"></i> Cadastro
                </button>
            </div>
            <div class="tab-content">
                <!-- Login Tab -->
                <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="loginEmail" placeholder="Seu email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="loginPassword" placeholder="Sua senha" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Lembrar-me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </button>
                        <div class="forgot-password">
                            <a href="#">Esqueceu sua senha?</a>
                        </div>
                        
                        <!-- Login demo -->
                        <div class="social-login">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Login de Demonstração:</strong><br>
                                <small>Email: <code>demo@carrinho.com</code><br>
                                Senha: <code>123456</code></small>
                            </div>
                        </div>
                        
                        <div class="register-link">
                            <p>Não tem uma conta? <a href="#" onclick="switchToRegister()">Cadastre-se</a></p>
                        </div>
                    </form>
                </div>
                <!-- Register Tab -->
                <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                    <form id="registerForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">Nome</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="firstName" placeholder="Seu nome" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Sobrenome</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="lastName" placeholder="Seu sobrenome" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="registerEmail" placeholder="Seu email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPhone" class="form-label">Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control" id="registerPhone" placeholder="(XX) XXXXX-XXXX" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Senha</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="registerPassword" placeholder="Sua senha" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Senha</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirme sua senha" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Concordo com os <a href="#" style="color: #0066cc;">termos de uso</a> e <a href="#" style="color: #0066cc;">política de privacidade</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Cadastrar
                        </button>
                        <!-- Informações sobre cadastro -->
                        <div class="social-login">
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i>
                                <strong>Cadastro gratuito!</strong><br>
                                <small>Crie sua conta para gerenciar suas vendas</small>
                            </div>
                        </div>
                        <div class="register-link">
                            <p>Já tem uma conta? <a href="#" onclick="switchToLogin()">Faça login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/validation.js"></script>
    
    <script>
        // Funções auxiliares para alternância de abas
        function switchToRegister() {
            const registerTab = document.getElementById('register-tab');
            const loginTab = document.getElementById('login-tab');
            
            // Remover active do login
            loginTab.classList.remove('active');
            document.getElementById('login').classList.remove('show', 'active');
            
            // Adicionar active ao register
            registerTab.classList.add('active');
            document.getElementById('register').classList.add('show', 'active');
        }
        
        function switchToLogin() {
            const registerTab = document.getElementById('register-tab');
            const loginTab = document.getElementById('login-tab');
            
            // Remover active do register
            registerTab.classList.remove('active');
            document.getElementById('register').classList.remove('show', 'active');
            
            // Adicionar active ao login
            loginTab.classList.add('active');
            document.getElementById('login').classList.add('show', 'active');
        }
        
        // Funções para alternar visibilidade da senha
        document.addEventListener('DOMContentLoaded', function() {
            // Alternar visibilidade da senha no login
            document.getElementById('toggleLoginPassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('loginPassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
            
            // Alternar visibilidade da senha no cadastro
            document.getElementById('toggleRegisterPassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('registerPassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
            
            // Alternar visibilidade da confirmação de senha
            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('confirmPassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
            
            // Máscara de telefone
            document.getElementById('registerPhone').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                
                if (value.length > 6) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{0,2})/, '($1');
                }
                
                e.target.value = value;
            });
        });
        
        // Alternar visibilidade da senha no login
        document.getElementById('toggleLoginPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('loginPassword');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
        
        // Alternar visibilidade da senha no cadastro
        document.getElementById('toggleRegisterPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('registerPassword');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
        
        // Alternar visibilidade da confirmação de senha
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
        
        // Validação do formulário de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!email || !password) {
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            // Enviar dados para o servidor
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            
            fetch('actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Login bem-sucedido
                    const user = {
                        name: data.data.nome,
                        email: email,
                        imageUrl: "https://ui-avatars.com/api/?name=" + data.data.nome + "&background=0066cc&color=fff"
                    };
                    
                    // Salvar informações do usuário
                    sessionStorage.setItem('user', JSON.stringify(user));
                    sessionStorage.setItem('user_type', 'local');
                    
                    alert('Login realizado com sucesso!');
                    // Redirecionar para a página principal
                    window.location.href = 'index.php';
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro de conexão: ' + error);
            });
        });
        
        // Validação do formulário de cadastro
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('registerEmail').value;
            const phone = document.getElementById('registerPhone').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const terms = document.getElementById('terms').checked;
            
            // Validações
            if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
                alert('Por favor, preencha todos os campos.');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('As senhas não coincidem.');
                return;
            }
            
            if (!terms) {
                alert('Você precisa aceitar os termos de uso e política de privacidade.');
                return;
            }
            
            // Enviar dados para o servidor
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('nome', firstName);
            formData.append('sobrenome', lastName);
            formData.append('email', email);
            formData.append('telefone', phone);
            formData.append('password', password);
            formData.append('confirm_password', confirmPassword);
            
            fetch('actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cadastro bem-sucedido
                    alert('Cadastro realizado com sucesso!');
                    // Redirecionar para a aba de login
                    switchToLogin();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro de conexão: ' + error);
            });
        });
        
        // Adicionar máscara de telefone
        document.getElementById('registerPhone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            } else {
                value = value.replace(/(\d{0,2})/, '($1');
            }
            
            e.target.value = value;
        });
        
        // Inicializar quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, iniciando Google Sign-In...');
            
            // Mostrar botões personalizados inicialmente
            showFallbackButtons();
            
            // Inicializar o Google Sign-In após um pequeno delay
            setTimeout(function() {
                initializeGoogleSignIn();
            }, 1000);
        });
    </script>
</body>
</html>