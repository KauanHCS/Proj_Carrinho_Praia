<?php
// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// CSP para página de login com CDNs
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://accounts.google.com https://www.gstatic.com; ";
$csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; ";
$csp .= "img-src 'self' data: https: blob:; ";
$csp .= "font-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://fonts.gstatic.com; ";
$csp .= "connect-src 'self' https:; ";
$csp .= "frame-src 'self' https://accounts.google.com; ";
$csp .= "worker-src 'self' blob:; ";
$csp .= "object-src 'none';";
header("Content-Security-Policy: $csp");
?>
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
        .demo-info {
            margin-top: 20px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
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
                            <a href="forgot-password.php">Esqueceu sua senha?</a>
                        </div>
                        
                        <!-- Informações de demonstração -->
                        <div class="demo-info">
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
                        <!-- Seleção de Tipo de Cadastro -->
                        <div class="mb-3">
                            <label class="form-label">Tipo de Cadastro</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="radio" class="form-check-input" id="cadastroAdmin" name="tipoCadastro" value="administrador" checked style="display:none;">
                                    <label class="form-check-label" for="cadastroAdmin">
                                        <div class="card text-center h-100" style="cursor: pointer; border: 2px solid #0066cc;">
                                            <div class="card-body p-3">
                                                <i class="bi bi-person-gear" style="font-size: 2rem; color: #0066cc;"></i>
                                                <h6 class="mt-2 mb-0">Administrador</h6>
                                                <small class="text-muted">Conta principal</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="form-check-input" id="cadastroFunc" name="tipoCadastro" value="funcionario" style="display:none;">
                                    <label class="form-check-label" for="cadastroFunc">
                                        <div class="card text-center h-100" style="cursor: pointer; border: 2px solid #e9ecef;">
                                            <div class="card-body p-3">
                                                <i class="bi bi-person-badge" style="font-size: 2rem; color: #6c757d;"></i>
                                                <h6 class="mt-2 mb-0">Funcionário</h6>
                                                <small class="text-muted">Precisa de código</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campos Comuns -->
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
                        <!-- Campos apenas para Administrador -->
                        <div id="camposAdminCadastro">
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
                        </div>
                        
                        <!-- Campos para Funcionário -->
                        <div id="camposFuncionarioCadastro" style="display: none;">
                            <div class="mb-3">
                                <label for="funcionarioPassword" class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="funcionarioPassword" placeholder="Sua senha">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleFuncionarioPassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="funcionarioConfirmPassword" class="form-label">Confirmar Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="funcionarioConfirmPassword" placeholder="Confirme sua senha">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleFuncionarioConfirmPassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="codigoAdminCadastro" class="form-label">Código do Administrador</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="text" class="form-control" id="codigoAdminCadastro" placeholder="Digite o código fornecido" maxlength="6">
                                </div>
                                <div class="form-text">Solicite este código ao administrador que irá gerenciá-lo</div>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Atenção:</strong> Após o cadastro, o administrador definirá suas permissões no sistema. Você fará login normalmente com email e senha.
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Concordo com os <a href="#" style="color: #0066cc;" onclick="alert('Funcionalidade ainda não implementada')">termos de uso</a> e <a href="#" style="color: #0066cc;" onclick="alert('Funcionalidade ainda não implementada')">política de privacidade</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Cadastrar
                        </button>
                        
                        <!-- Informações sobre cadastro -->
                        <div class="demo-info">
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
    
    
    <script>
        // Funções auxiliares para alternância de abas
        function switchToRegister() {
            const registerTab = document.getElementById('register-tab');
            const loginTab = document.getElementById('login-tab');
            
            loginTab.classList.remove('active');
            document.getElementById('login').classList.remove('show', 'active');
            
            registerTab.classList.add('active');
            document.getElementById('register').classList.add('show', 'active');
        }
        
        function switchToLogin() {
            const registerTab = document.getElementById('register-tab');
            const loginTab = document.getElementById('login-tab');
            
            registerTab.classList.remove('active');
            document.getElementById('register').classList.remove('show', 'active');
            
            loginTab.classList.add('active');
            document.getElementById('login').classList.add('show', 'active');
        }
        
        
        // Função para alternar campos de cadastro baseado no tipo
        function alternarCamposCadastro() {
            const cadastroAdmin = document.getElementById('cadastroAdmin');
            const cadastroFunc = document.getElementById('cadastroFunc');
            const camposAdminCadastro = document.getElementById('camposAdminCadastro');
            const camposFuncionarioCadastro = document.getElementById('camposFuncionarioCadastro');
            
            if (cadastroFunc.checked) {
                camposAdminCadastro.style.display = 'none';
                camposFuncionarioCadastro.style.display = 'block';
                
                // Remover required dos campos admin
                document.getElementById('registerPassword').required = false;
                document.getElementById('confirmPassword').required = false;
                
                // Adicionar required aos campos funcionário
                document.getElementById('funcionarioPassword').required = true;
                document.getElementById('funcionarioConfirmPassword').required = true;
                document.getElementById('codigoAdminCadastro').required = true;
            } else {
                camposAdminCadastro.style.display = 'block';
                camposFuncionarioCadastro.style.display = 'none';
                
                // Adicionar required aos campos admin
                document.getElementById('registerPassword').required = true;
                document.getElementById('confirmPassword').required = true;
                
                // Remover required dos campos funcionário
                document.getElementById('funcionarioPassword').required = false;
                document.getElementById('funcionarioConfirmPassword').required = false;
                document.getElementById('codigoAdminCadastro').required = false;
            }
        }
        
        // Função para atualizar estilo dos cards
        function atualizarEstilosCards() {
            // Cards de cadastro
            const cardsCadastro = document.querySelectorAll('[for="cadastroAdmin"] .card, [for="cadastroFunc"] .card');
            cardsCadastro.forEach(card => {
                const input = card.parentElement.previousElementSibling || card.parentElement.parentElement.querySelector('input');
                if (input && input.checked) {
                    card.style.border = '2px solid #0066cc';
                    card.querySelector('i').style.color = '#0066cc';
                } else {
                    card.style.border = '2px solid #e9ecef';
                    card.querySelector('i').style.color = '#6c757d';
                }
            });
        }
        
        // Inicialização
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
            
            // Alternar visibilidade da senha do funcionário
            document.getElementById('toggleFuncionarioPassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('funcionarioPassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
            
            // Alternar visibilidade da confirmação de senha do funcionário
            document.getElementById('toggleFuncionarioConfirmPassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('funcionarioConfirmPassword');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
            
            // Event listeners para alternância de tipo de cadastro
            document.getElementsByName('tipoCadastro').forEach(radio => {
                radio.addEventListener('change', function() {
                    alternarCamposCadastro();
                    atualizarEstilosCards();
                });
            });
            
            // Event listeners para cliques nos cards
            document.querySelectorAll('[for="cadastroAdmin"], [for="cadastroFunc"]').forEach(label => {
                label.addEventListener('click', function(e) {
                    e.preventDefault();
                    const input = this.previousElementSibling || this.parentElement.querySelector('input');
                    if (input) {
                        input.checked = true;
                        alternarCamposCadastro();
                        atualizarEstilosCards();
                    }
                });
            });
            
            // Máscara de telefone (cadastro)
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
            
            // Handler do formulário de login
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('loginEmail').value;
                const password = document.getElementById('loginPassword').value;
                
                if (!email || !password) {
                    alert('Por favor, preencha todos os campos.');
                    return;
                }
                
                // Verificar login demo
                if (email === 'demo@carrinho.com' && password === '123456') {
                    const user = {
                        name: 'Usuário Demo',
                        email: email,
                        imageUrl: "https://ui-avatars.com/api/?name=Usuario+Demo&background=0066cc&color=fff"
                    };
                    
                    sessionStorage.setItem('user', JSON.stringify(user));
                    sessionStorage.setItem('user_type', 'demo');
                    alert('Login demo realizado com sucesso!');
                    window.location.href = 'index.php';
                    return;
                }
                
                // Enviar dados para o servidor
                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('email', email);
                formData.append('password', password);
                
                fetch('../src/Controllers/actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = {
                            name: data.data.nome,
                            email: email,
                            tipo: data.data.tipo_usuario || 'administrador',
                            funcao: data.data.funcao_funcionario,
                            codigo_unico: data.data.codigo_unico,
                            imageUrl: "https://ui-avatars.com/api/?name=" + data.data.nome + "&background=0066cc&color=fff"
                        };
                        
                        sessionStorage.setItem('user', JSON.stringify(user));
                        sessionStorage.setItem('user_type', 'local');
                        alert('Login realizado com sucesso!');
                        window.location.href = 'index.php';
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro de conexão: ' + error);
                });
            });
            
            // Handler do formulário de cadastro
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const tipoCadastro = document.querySelector('input[name="tipoCadastro"]:checked').value;
                const firstName = document.getElementById('firstName').value;
                const lastName = document.getElementById('lastName').value;
                const email = document.getElementById('registerEmail').value;
                const phone = document.getElementById('registerPhone').value;
                const terms = document.getElementById('terms').checked;
                
                // Campos comuns
                if (!firstName || !lastName || !email || !phone) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }
                
                if (!terms) {
                    alert('Você precisa aceitar os termos de uso e política de privacidade.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'register');
                formData.append('tipo_cadastro', tipoCadastro);
                formData.append('nome', firstName);
                formData.append('sobrenome', lastName);
                formData.append('email', email);
                formData.append('telefone', phone);
                
                if (tipoCadastro === 'funcionario') {
                    // Cadastro de funcionário
                    const funcionarioPassword = document.getElementById('funcionarioPassword').value;
                    const funcionarioConfirmPassword = document.getElementById('funcionarioConfirmPassword').value;
                    const codigoAdmin = document.getElementById('codigoAdminCadastro').value;
                    
                    if (!funcionarioPassword || !funcionarioConfirmPassword || !codigoAdmin) {
                        alert('Por favor, preencha todos os campos de funcionário.');
                        return;
                    }
                    
                    if (funcionarioPassword !== funcionarioConfirmPassword) {
                        alert('As senhas não coincidem.');
                        return;
                    }
                    
                    formData.append('password', funcionarioPassword);
                    formData.append('confirm_password', funcionarioConfirmPassword);
                    formData.append('codigo_admin', codigoAdmin);
                } else {
                    // Cadastro de administrador
                    const password = document.getElementById('registerPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    
                    if (!password || !confirmPassword) {
                        alert('Por favor, preencha a senha.');
                        return;
                    }
                    
                    if (password !== confirmPassword) {
                        alert('As senhas não coincidem.');
                        return;
                    }
                    
                    formData.append('password', password);
                    formData.append('confirm_password', confirmPassword);
                }
                
                fetch('../src/Controllers/actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cadastro realizado com sucesso! Agora você pode fazer login.');
                        switchToLogin();
                        // Limpar formulário
                        document.getElementById('registerForm').reset();
                        // Resetar para administrador
                        document.getElementById('cadastroAdmin').checked = true;
                        alternarCamposCadastro();
                        atualizarEstilosCards();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro de conexão: ' + error);
                });
            });
            
            // Inicializar estado dos campos e estilos
            alternarCamposCadastro();
            atualizarEstilosCards();
        });
    </script>
</body>
</html>