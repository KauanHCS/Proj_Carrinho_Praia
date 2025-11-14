<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Security;
use CarrinhoDePreia\Logger;

/**
 * Classe User - Gerencia autenticação e usuários
 * Mantém compatibilidade total com as funções originais
 */
class User
{
    private $db;
    private $userData;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userData = null;
    }

    /**
     * Fazer login do usuário - mantém mesma lógica original
     * MELHORADO: Rate limiting e logging
     */
    public function login($email, $password)
    {
        try {
            if (empty($email) || empty($password)) {
                throw new \Exception('Email e senha são obrigatórios');
            }

            // ✨ NOVO: Rate limiting para prevenir brute force
            if (!Security::checkRateLimit($email)) {
                $waitTime = Security::getRateLimitWaitTime($email);
                Logger::warning('Login bloqueado por rate limit', [
                    'email' => $email,
                    'wait_time' => $waitTime
                ]);
                throw new \Exception("Muitas tentativas de login. Aguarde {$waitTime} segundos.");
            }

            // Verificar se o usuário existe
            $user = $this->db->selectOne(
                "SELECT * FROM usuarios WHERE email = ?",
                "s",
                [$email]
            );

            if (!$user) {
                Logger::warning('Tentativa de login com email inexistente', ['email' => $email]);
                throw new \Exception('Email ou senha incorretos');
            }

            // Verificar senha usando hash seguro
            if (!Security::verifyPassword($password, $user['senha'])) {
                Logger::warning('Tentativa de login com senha incorreta', ['email' => $email]);
                throw new \Exception('Email ou senha incorretos');
            }

            // ✨ NOVO: Reset rate limit após login bem-sucedido
            Security::resetRateLimit($email);

            // Iniciar sessão - mantém compatibilidade
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];

            $this->userData = $user;

            // ✨ NOVO: Log de login bem-sucedido
            Logger::info('Login bem-sucedido', [
                'user_id' => $user['id'],
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $user['id'],
                    'nome' => $user['nome']
                ],
                'message' => 'Login realizado com sucesso'
            ];

        } catch (\Exception $e) {
            Logger::error('Falha no login', [
                'email' => $email ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Registrar novo usuário - mantém mesma lógica original
     * MELHORADO: Validação de senha forte e logging
     */
    public function register($nome, $sobrenome, $email, $telefone, $password, $confirmPassword)
    {
        try {
            // Validações - mesmas da versão original
            if (empty($nome) || empty($sobrenome) || empty($email) || empty($telefone) || empty($password)) {
                throw new \Exception('Todos os campos são obrigatórios');
            }

            if ($password !== $confirmPassword) {
                throw new \Exception('As senhas não coincidem');
            }

            // ✨ NOVO: Validação de força de senha
            $passwordValidation = Security::validatePasswordStrength($password);
            if (!$passwordValidation['valid']) {
                throw new \Exception(implode('. ', $passwordValidation['errors']));
            }

            // ✨ NOVO: Validação de email
            if (!Security::validateEmail($email)) {
                throw new \Exception('Email inválido');
            }

            // Verificar se o email já existe
            $existingUser = $this->db->selectOne(
                "SELECT id FROM usuarios WHERE email = ?",
                "s",
                [$email]
            );

            if ($existingUser) {
                throw new \Exception('Email já cadastrado');
            }

            // Criar usuário com senha hasheada
            $nomeCompleto = $nome . ' ' . $sobrenome;
            $hashedPassword = Security::hashPassword($password);
            
            $userId = $this->db->insert(
                "INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)",
                "ssss",
                [$nomeCompleto, $email, $telefone, $hashedPassword]
            );

            // Iniciar sessão - mantém compatibilidade
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $userId;
            $_SESSION['usuario_nome'] = $nomeCompleto;
            $_SESSION['usuario_email'] = $email;

            // ✨ NOVO: Log de cadastro bem-sucedido
            Logger::info('Novo usuário cadastrado', [
                'user_id' => $userId,
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $userId,
                    'nome' => $nomeCompleto
                ],
                'message' => 'Cadastro realizado com sucesso'
            ];

        } catch (\Exception $e) {
            Logger::error('Falha no cadastro', [
                'email' => $email ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Login com Google - mantém mesma lógica original
     */
    public function loginGoogle($email)
    {
        try {
            if (empty($email)) {
                throw new \Exception('Email é obrigatório');
            }

            // Verificar se o usuário existe e tem login com Google
            $user = $this->db->selectOne(
                "SELECT id, nome, email, imagem_url FROM usuarios WHERE email = ? AND google_id IS NOT NULL",
                "s",
                [$email]
            );

            if (!$user) {
                throw new \Exception('Usuário não encontrado ou não tem login com Google');
            }

            // Iniciar sessão
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];

            $userData = [
                'id' => $user['id'],
                'name' => $user['nome'],
                'email' => $user['email'],
                'imageUrl' => $user['imagem_url'] ?: "https://ui-avatars.com/api/?name=" . urlencode($user['nome']) . "&background=0066cc&color=fff"
            ];

            return [
                'success' => true,
                'data' => ['user' => $userData],
                'message' => 'Login com Google realizado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Registrar usuário Google - mantém mesma lógica original
     */
    public function registerGoogle($name, $email, $imageUrl, $googleId)
    {
        try {
            if (empty($name) || empty($email) || empty($googleId)) {
                throw new \Exception('Dados do Google são obrigatórios');
            }

            // Verificar se o email já existe
            $existingUser = $this->db->selectOne(
                "SELECT id FROM usuarios WHERE email = ?",
                "s",
                [$email]
            );

            if ($existingUser) {
                // Atualizar usuário existente com Google ID
                $this->db->execute(
                    "UPDATE usuarios SET google_id = ?, imagem_url = ? WHERE email = ?",
                    "sss",
                    [$googleId, $imageUrl, $email]
                );

                // Obter informações do usuário
                $user = $this->db->selectOne(
                    "SELECT id, nome FROM usuarios WHERE email = ?",
                    "s",
                    [$email]
                );
                
            } else {
                // Criar novo usuário
                $userId = $this->db->insert(
                    "INSERT INTO usuarios (nome, email, imagem_url, google_id, data_cadastro) VALUES (?, ?, ?, ?, NOW())",
                    "ssss",
                    [$name, $email, $imageUrl, $googleId]
                );
                $user = ['id' => $userId, 'nome' => $name];
            }

            // Iniciar sessão
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $email;

            $userData = [
                'id' => $user['id'],
                'name' => $user['nome'],
                'email' => $email,
                'imageUrl' => $imageUrl
            ];

            return [
                'success' => true,
                'data' => ['user' => $userData],
                'message' => 'Usuário do Google registrado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar se usuário Google existe - mantém mesma lógica original
     */
    public function checkGoogleUser($email)
    {
        try {
            if (empty($email)) {
                throw new \Exception('Email é obrigatório');
            }

            // Verificar se o usuário já existe
            $user = $this->db->selectOne(
                "SELECT id, nome FROM usuarios WHERE email = ? AND google_id IS NOT NULL",
                "s",
                [$email]
            );

            if ($user) {
                return [
                    'success' => true,
                    'data' => [
                        'exists' => true,
                        'usuario_id' => $user['id'],
                        'nome' => $user['nome']
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => ['exists' => false]
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter usuário logado - compatibilidade com função original
     */
    public function getUsuarioLogado()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Verificar se usuário está logado - compatibilidade com função original
     */
    public function verificarLogin()
    {
        $usuarioId = $this->getUsuarioLogado();
        if (!$usuarioId) {
            throw new \Exception('Usuário não está logado');
        }
        return $usuarioId;
    }

    /**
     * Fazer logout
     */
    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpar dados da sessão
        unset($_SESSION['usuario_id']);
        unset($_SESSION['usuario_nome']);
        unset($_SESSION['usuario_email']);
        
        $this->userData = null;
        
        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ];
    }

    /**
     * Obter dados do usuário atual
     */
    public function getCurrentUser()
    {
        $userId = $this->getUsuarioLogado();
        if (!$userId) {
            return null;
        }

        if (!$this->userData) {
            $this->userData = $this->db->selectOne(
                "SELECT id, nome, email, telefone, imagem_url, data_cadastro, ultimo_login FROM usuarios WHERE id = ?",
                "i",
                [$userId]
            );
        }

        return $this->userData;
    }

    /**
     * Atualizar dados do usuário
     */
    public function updateUser($userId, $dados)
    {
        try {
            $allowedFields = ['nome', 'email', 'telefone', 'imagem_url'];
            $updateFields = [];
            $values = [];
            $types = "";

            foreach ($dados as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "$field = ?";
                    $values[] = $value;
                    $types .= "s";
                }
            }

            if (empty($updateFields)) {
                throw new \Exception('Nenhum campo válido para atualizar');
            }

            $values[] = $userId;
            $types .= "i";

            $sql = "UPDATE usuarios SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $affectedRows = $this->db->execute($sql, $types, $values);

            if ($affectedRows > 0) {
                // Limpar cache dos dados do usuário
                $this->userData = null;
                
                return [
                    'success' => true,
                    'message' => 'Dados atualizados com sucesso'
                ];
            } else {
                throw new \Exception('Nenhum dado foi alterado');
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Login específico para funcionários
     */
    public function loginFuncionario($nome, $telefone, $codigoAdmin)
    {
        try {
            if (empty($nome) || empty($telefone) || empty($codigoAdmin)) {
                throw new \Exception('Todos os campos são obrigatórios');
            }

            // Verificar se o código existe e está disponível
            $codigo = $this->db->selectOne(
                "SELECT cf.*, u.nome as admin_nome FROM codigos_funcionarios cf 
                 JOIN usuarios u ON cf.admin_id = u.id 
                 WHERE cf.codigo = ? AND cf.usado = 0 AND cf.ativo = 1",
                "s",
                [$codigoAdmin]
            );

            if (!$codigo) {
                throw new \Exception('Código inválido ou já utilizado');
            }

            // Cadastrar funcionário automaticamente
            $nomeCompleto = trim($nome);
            $emailFuncionario = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nome)) . '@funcionario.local';
            
            $funcionarioId = $this->db->insert(
                "INSERT INTO usuarios (nome, email, telefone, tipo_usuario, funcao_funcionario, codigo_admin, data_cadastro) 
                 VALUES (?, ?, ?, 'funcionario', ?, ?, NOW())",
                "ssssi",
                [$nomeCompleto, $emailFuncionario, $telefone, $codigo['funcao'], $codigo['admin_id']]
            );

            // Marcar código como usado
            $this->db->execute(
                "UPDATE codigos_funcionarios SET usado = 1, usado_por_usuario = ?, data_uso = NOW() WHERE codigo = ?",
                "is",
                [$funcionarioId, $codigoAdmin]
            );

            // Iniciar sessão
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $funcionarioId;
            $_SESSION['usuario_nome'] = $nomeCompleto;
            $_SESSION['usuario_email'] = $emailFuncionario;
            $_SESSION['usuario_tipo'] = 'funcionario';
            $_SESSION['usuario_funcao'] = $codigo['funcao'];
            $_SESSION['usuario_admin_id'] = $codigo['admin_id'];

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $funcionarioId,
                    'nome' => $nomeCompleto,
                    'funcao' => $codigo['funcao'],
                    'admin_id' => $codigo['admin_id'],
                    'admin_nome' => $codigo['admin_nome']
                ],
                'message' => 'Login de funcionário realizado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cadastro específico para funcionários
     */
    public function registerFuncionario($nome, $sobrenome, $email, $telefone, $password, $confirmPassword, $codigoAdmin)
    {
        try {
            if (empty($nome) || empty($sobrenome) || empty($email) || empty($telefone) || empty($password) || empty($codigoAdmin)) {
                throw new \Exception('Todos os campos são obrigatórios');
            }

            if ($password !== $confirmPassword) {
                throw new \Exception('As senhas não coincidem');
            }

            // Verificar se o email já existe
            $existingUser = $this->db->selectOne(
                "SELECT id FROM usuarios WHERE email = ?",
                "s",
                [$email]
            );

            if ($existingUser) {
                throw new \Exception('Email já cadastrado');
            }

            // Verificar se o código existe e está disponível
            $codigo = $this->db->selectOne(
                "SELECT cf.*, u.nome as admin_nome FROM codigos_funcionarios cf 
                 JOIN usuarios u ON cf.admin_id = u.id 
                 WHERE cf.codigo = ? AND cf.usado = 0 AND cf.ativo = 1",
                "s",
                [$codigoAdmin]
            );

            if (!$codigo) {
                throw new \Exception('Código inválido ou já utilizado');
            }

            // Criar funcionário com senha
            $nomeCompleto = $nome . ' ' . $sobrenome;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $funcionarioId = $this->db->insert(
                "INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, funcao_funcionario, codigo_admin, data_cadastro) 
                 VALUES (?, ?, ?, ?, 'funcionario', ?, ?, NOW())",
                "sssssi",
                [$nomeCompleto, $email, $telefone, $hashedPassword, $codigo['funcao'], $codigo['admin_id']]
            );

            // Marcar código como usado
            $this->db->execute(
                "UPDATE codigos_funcionarios SET usado = 1, usado_por_usuario = ?, data_uso = NOW() WHERE codigo = ?",
                "is",
                [$funcionarioId, $codigoAdmin]
            );

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $funcionarioId,
                    'nome' => $nomeCompleto,
                    'funcao' => $codigo['funcao'],
                    'admin_id' => $codigo['admin_id']
                ],
                'message' => 'Cadastro de funcionário realizado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Gerar código único para funcionário (apenas administradores)
     */
    public function gerarCodigoFuncionario($adminId, $funcao)
    {
        try {
            // Verificar se o usuário é administrador
            $admin = $this->db->selectOne(
                "SELECT tipo_usuario FROM usuarios WHERE id = ?",
                "i",
                [$adminId]
            );

            if (!$admin || $admin['tipo_usuario'] !== 'administrador') {
                throw new \Exception('Apenas administradores podem gerar códigos');
            }

            // Gerar código único
            $tentativas = 0;
            do {
                $codigo = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                
                $existe = $this->db->selectOne(
                    "SELECT id FROM codigos_funcionarios WHERE codigo = ?",
                    "s",
                    [$codigo]
                );
                
                $tentativas++;
            } while ($existe && $tentativas < 100);

            if ($existe) {
                throw new \Exception('Erro ao gerar código único');
            }

            // Inserir código na tabela
            $codigoId = $this->db->insert(
                "INSERT INTO codigos_funcionarios (codigo, admin_id, funcao, ativo, data_criacao) 
                 VALUES (?, ?, ?, 1, NOW())",
                "sis",
                [$codigo, $adminId, $funcao]
            );

            return [
                'success' => true,
                'data' => [
                    'codigo' => $codigo,
                    'funcao' => $funcao,
                    'data_criacao' => date('Y-m-d H:i:s')
                ],
                'message' => 'Código gerado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar códigos gerados pelo administrador
     */
    public function listarCodigosFuncionarios($adminId)
    {
        try {
            $codigos = $this->db->selectAll(
                "SELECT cf.*, u.nome as funcionario_nome 
                 FROM codigos_funcionarios cf 
                 LEFT JOIN usuarios u ON cf.usado_por_usuario = u.id 
                 WHERE cf.admin_id = ? AND cf.ativo = 1 
                 ORDER BY cf.data_criacao DESC",
                "i",
                [$adminId]
            );

            return [
                'success' => true,
                'data' => $codigos,
                'message' => 'Códigos listados com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Sanitizar entrada - mantém função original
     */
    public static function sanitizeInput($input)
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Validar email - mantém função original
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// Funções de compatibilidade para não quebrar código existente
if (!function_exists('getUsuarioLogado')) {
    function getUsuarioLogado() {
        $user = new \CarrinhoDePreia\User();
        return $user->getUsuarioLogado();
    }
}

if (!function_exists('verificarLogin')) {
    function verificarLogin() {
        $user = new \CarrinhoDePreia\User();
        return $user->verificarLogin();
    }
}