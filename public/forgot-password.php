<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha - Carrinho de Praia</title>
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
        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .forgot-header {
            background: linear-gradient(135deg, #0066cc, #0099ff);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .forgot-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .forgot-header i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .forgot-content {
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
        .btn-secondary {
            border-radius: 10px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .back-to-login a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 500;
        }
        .back-to-login a:hover {
            text-decoration: underline;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success-message {
            display: none;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container mx-auto">
            <div class="forgot-header">
                <i class="bi bi-key"></i>
                <h2>Esqueci a Senha</h2>
                <p>Recupere o acesso à sua conta</p>
            </div>
            
            <div class="forgot-content">
                <!-- Formulário de recuperação -->
                <form id="forgotPasswordForm">
                    <div class="info-box">
                        <i class="bi bi-info-circle text-primary"></i>
                        <strong>Como funciona:</strong><br>
                        <small>Digite seu email cadastrado e enviaremos instruções para redefinir sua senha.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email cadastrado</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" placeholder="Seu email cadastrado" required>
                        </div>
                        <div class="form-text">Certifique-se de usar o mesmo email do seu cadastro.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Enviar Instruções
                        </button>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar ao Login
                        </a>
                    </div>
                </form>
                
                <!-- Mensagem de sucesso -->
                <div class="success-message" id="successMessage">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <h5 class="mt-2">Email Enviado!</h5>
                    <p>Verifique sua caixa de entrada e siga as instruções para redefinir sua senha.</p>
                    <small class="text-muted">Não esquece de verificar a pasta de spam!</small>
                </div>
                
                <div class="back-to-login">
                    <p>Lembrou da senha? <a href="login.php">Fazer Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            
            // Simular envio (aqui você implementaria a lógica real)
            setTimeout(() => {
                // Ocultar formulário e mostrar mensagem de sucesso
                document.getElementById('forgotPasswordForm').style.display = 'none';
                document.getElementById('successMessage').style.display = 'block';
                
                // Implementar lógica real de recuperação de senha aqui
                console.log('Email para recuperação:', email);
                
                // Em uma implementação real, você faria algo como:
                /*
                fetch('../src/Controllers/actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=forgot_password&email=${encodeURIComponent(email)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('forgotPasswordForm').style.display = 'none';
                        document.getElementById('successMessage').style.display = 'block';
                    } else {
                        alert('Erro: ' + data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar solicitação. Tente novamente.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
                */
            }, 2000);
        });
    </script>
</body>
</html>