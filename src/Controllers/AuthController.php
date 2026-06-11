<?php

namespace CarrinhoDePreia\Controllers;

use CarrinhoDePreia\Security;

class AuthController extends BaseController
{
    public static function login(): void
    {
        $email    = (string) self::input($_POST, 'email', '');
        $password = (string) self::input($_POST, 'password', '');

        if ($email === '' || $password === '') {
            self::error('Email e senha são obrigatórios');
        }

        $pdo = self::getPdo();

        // Rate limiting persistente em DB
        if (!Security::checkRateLimitDb($pdo, $email)) {
            $wait = Security::getRateLimitWaitTimeDb($pdo, $email);
            self::error("Muitas tentativas de login. Aguarde {$wait} segundos.");
        }

        // Login demo apenas em DEV (APP_ENV=local + APP_DEBUG=true)
        if (
            function_exists('env')
            && strtolower((string) env('APP_ENV', 'production')) === 'local'
            && function_exists('is_debug') && is_debug()
            && $email === 'demo@carrinho.com' && $password === '123456'
        ) {
            Security::resetRateLimitDb($pdo, $email);
            // Usar o primeiro usuário real do banco para sessão (produtos e vendas são por usuario_id).
            $stmt = $pdo->query('SELECT id, nome FROM usuarios WHERE COALESCE(ativo, 1) = 1 ORDER BY id ASC LIMIT 1');
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                self::error('Cadastre um usuário no sistema antes de usar o login demo.');
            }
            $demoId = (int) $row['id'];
            $_SESSION['usuario_id']    = $demoId;
            $_SESSION['usuario_nome']  = 'Usuário Demo';
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_tipo']  = 'administrador';
            self::json(true, [
                'usuario_id' => $demoId,
                'nome' => 'Usuário Demo',
                'tipo_usuario' => 'administrador',
            ], 'Login demo (apenas em DEV)');
        }

        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['senha'])) {
            self::error('Email ou senha incorretos');
        }

        Security::resetRateLimitDb($pdo, $email);

        $_SESSION['usuario_id']    = $user['id'];
        $_SESSION['usuario_nome']  = $user['nome'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_tipo']  = $user['tipo_usuario'] ?? 'administrador';

        self::json(true, [
            'usuario_id'         => $user['id'],
            'nome'               => $user['nome'],
            'tipo_usuario'       => $user['tipo_usuario'] ?? 'administrador',
            'funcao_funcionario' => $user['funcao_funcionario'] ?? null,
            'codigo_unico'       => $user['codigo_unico'] ?? null,
            'admin_id'           => $user['codigo_admin'] ?? null,
        ], 'Login realizado com sucesso');
    }

    public static function register(): void
    {
        $tipoCadastro    = (string) self::input($_POST, 'tipo_cadastro', 'administrador');
        $nome            = trim((string) self::input($_POST, 'nome', ''));
        $sobrenome       = trim((string) self::input($_POST, 'sobrenome', ''));
        $email           = trim((string) self::input($_POST, 'email', ''));
        $telefone        = (string) self::input($_POST, 'telefone', '');
        $password        = (string) self::input($_POST, 'password', '');
        $confirmPassword = (string) self::input($_POST, 'confirm_password', '');

        if ($nome === '' || $sobrenome === '' || $email === '' || $password === '') {
            self::error('Todos os campos são obrigatórios');
        }
        if ($password !== $confirmPassword) {
            self::error('As senhas não coincidem');
        }

        $pdo = self::getPdo();

        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            self::error('Email já cadastrado');
        }

        $nomeCompleto   = $nome . ' ' . $sobrenome;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($tipoCadastro === 'funcionario') {
            $codigoAdmin = (string) self::input($_POST, 'codigo_admin', '');
            if ($codigoAdmin === '') {
                self::error('Código do administrador é obrigatório');
            }

            $stmt = $pdo->prepare('SELECT * FROM codigos_funcionarios WHERE codigo = ? AND ativo = 1');
            $stmt->execute([$codigoAdmin]);
            $codigo = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$codigo) {
                self::error('Código inválido ou inativo');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, funcao_funcionario, codigo_admin, codigo_unico, data_cadastro)
                 VALUES (?, ?, ?, ?, 'funcionario', NULL, ?, ?, NOW())"
            );
            $stmt->execute([$nomeCompleto, $email, $telefone, $hashedPassword, $codigo['admin_id'], $codigoAdmin]);
            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                'INSERT INTO usos_codigo (codigo_id, usuario_id, data_uso) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE data_uso = NOW()'
            );
            $stmt->execute([$codigo['id'], $userId]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, data_cadastro)
                 VALUES (?, ?, ?, ?, 'administrador', NOW())"
            );
            $stmt->execute([$nomeCompleto, $email, $telefone, $hashedPassword]);
            $userId = $pdo->lastInsertId();

            $codigoUnico = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare('UPDATE usuarios SET codigo_unico = ? WHERE id = ?');
            $stmt->execute([$codigoUnico, $userId]);
        }

        self::json(true, ['usuario_id' => $userId, 'nome' => $nomeCompleto], 'Cadastro realizado com sucesso');
    }

    public static function alterarSenha(): void
    {
        $userId    = self::requireAuth();
        $atual     = (string) self::input($_POST, 'senha_atual', '');
        $nova      = (string) self::input($_POST, 'nova_senha', '');

        if ($atual === '' || $nova === '') {
            self::error('Todos os campos são obrigatórios');
        }
        if (strlen($nova) < 6) {
            self::error('A nova senha deve ter pelo menos 6 caracteres');
        }

        $pdo = self::getPdo();
        $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            self::error('Usuário não encontrado');
        }
        if (!password_verify($atual, $user['senha'])) {
            self::error('Senha atual incorreta');
        }

        $stmt = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        $stmt->execute([password_hash($nova, PASSWORD_DEFAULT), $userId]);

        self::json(true, null, 'Senha alterada com sucesso');
    }

    public static function excluirConta(): void
    {
        $userId = self::requireAuth();
        $senha  = (string) self::input($_POST, 'senha', '');

        if ($senha === '') {
            self::error('Senha é obrigatória para confirmar exclusão');
        }

        $pdo = self::getPdo();
        $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($senha, $user['senha'])) {
            self::error('Senha incorreta');
        }

        $stmt = $pdo->prepare('UPDATE usuarios SET ativo = 0, data_exclusao = NOW() WHERE id = ?');
        $stmt->execute([$userId]);

        session_destroy();
        self::json(true, null, 'Conta excluída com sucesso');
    }
}
