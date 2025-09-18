<?php

namespace CarrinhoDePreia;

use CarrinhoDePreia\Database;

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
     */
    public function login($email, $password)
    {
        try {
            if (empty($email) || empty($password)) {
                throw new \Exception('Email e senha são obrigatórios');
            }

            // Verificar se o usuário existe
            $user = $this->db->selectOne(
                "SELECT * FROM usuarios WHERE email = ?",
                "s",
                [$email]
            );

            if (!$user) {
                throw new \Exception('Email ou senha incorretos');
            }

            // Verificar senha usando hash seguro
            if (!password_verify($password, $user['senha'])) {
                throw new \Exception('Email ou senha incorretos');
            }

            // Iniciar sessão - mantém compatibilidade
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];

            $this->userData = $user;

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $user['id'],
                    'nome' => $user['nome']
                ],
                'message' => 'Login realizado com sucesso'
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
     * Registrar novo usuário - mantém mesma lógica original
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
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
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

            return [
                'success' => true,
                'data' => [
                    'usuario_id' => $userId,
                    'nome' => $nomeCompleto
                ],
                'message' => 'Cadastro realizado com sucesso'
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