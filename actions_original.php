<?php
// actions.php
require_once 'config/database.php';

// Input sanitization functions
function sanitizeInput($input) {
    if (is_string($input)) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

function sanitizeArray($array) {
    $sanitized = [];
    foreach ($array as $key => $value) {
        $sanitized[sanitizeInput($key)] = sanitizeInput($value);
    }
    return $sanitized;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePrice($price) {
    return is_numeric($price) && $price > 0;
}

function validateQuantity($quantity) {
    return is_numeric($quantity) && intval($quantity) == $quantity && $quantity > 0;
}

// Função para retornar resposta JSON
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

// Funções de sessão
function getUsuarioLogado() {
    session_start();
    return $_SESSION['usuario_id'] ?? null;
}

function verificarLogin() {
    $usuarioId = getUsuarioLogado();
    if (!$usuarioId) {
        jsonResponse(false, null, 'Usuário não está logado');
    }
    return $usuarioId;
}

// Verificar ação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            login();
            break;
        case 'register':
            register();
            break;
        case 'check_google_user':
            checkGoogleUser();
            break;
        case 'register_google':
            registerGoogleUser();
            break;
        case 'login_google':
            loginGoogleUser();
            break;
        case 'finalizar_venda':
            finalizarVenda();
            break;
        case 'reabastecer':
            reabastecerEstoque();
            break;
        case 'salvar_produto':
            salvarProduto();
            break;
        case 'atualizar_produto':
            atualizarProduto();
            break;
        case 'excluir_produto':
            excluirProduto();
            break;
        case 'criar_notificacao':
            criarNotificacao();
            break;
        default:
            jsonResponse(false, null, 'Ação inválida');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'verificar_estoque_baixo':
            verificarEstoqueBaixo();
            break;
        case 'get_produto':
            getProduto();
            break;
        case 'get_produtos_mais_vendidos':
            getProdutosMaisVendidos();
            break;
        default:
            jsonResponse(false, null, 'Ação inválida');
    }
}

// Funções de autenticação com Google

function checkGoogleUser() {
    $conn = getConnection();
    
    try {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            jsonResponse(false, null, 'Email é obrigatório');
        }
        
        // Verificar se o usuário já existe
        $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND google_id IS NOT NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            jsonResponse(true, [
                'exists' => true,
                'usuario_id' => $usuario['id'],
                'nome' => $usuario['nome']
            ]);
        } else {
            jsonResponse(true, ['exists' => false]);
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function registerGoogleUser() {
    $conn = getConnection();
    
    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $imageUrl = $_POST['imageUrl'] ?? '';
        $googleId = $_POST['googleId'] ?? '';
        
        if (empty($name) || empty($email) || empty($googleId)) {
            jsonResponse(false, null, 'Dados do Google são obrigatórios');
        }
        
        // Verificar se o email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Atualizar usuário existente com Google ID
            $stmt = $conn->prepare("UPDATE usuarios SET google_id = ?, imagem_url = ? WHERE email = ?");
            $stmt->bind_param("sss", $googleId, $imageUrl, $email);
            $stmt->execute();
            
            // Obter informações do usuário
            $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            
        } else {
            // Criar novo usuário
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, imagem_url, google_id, data_cadastro) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $imageUrl, $googleId);
            $stmt->execute();
            $usuarioId = $conn->insert_id;
            $usuario = ['id' => $usuarioId, 'nome' => $name];
        }
        
        // Iniciar sessão
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $email;
        
        // Criar usuário para retorno
        $user = [
            'id' => $usuario['id'],
            'name' => $usuario['nome'],
            'email' => $email,
            'imageUrl' => $imageUrl
        ];
        
        jsonResponse(true, ['user' => $user], 'Usuário do Google registrado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function loginGoogleUser() {
    $conn = getConnection();
    
    try {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            jsonResponse(false, null, 'Email é obrigatório');
        }
        
        // Verificar se o usuário existe e tem login com Google
        $stmt = $conn->prepare("SELECT id, nome, email, imagem_url FROM usuarios WHERE email = ? AND google_id IS NOT NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Usuário não encontrado ou não tem login com Google');
        }
        
        $usuario = $result->fetch_assoc();
        
        // Iniciar sessão
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        
        // Criar usuário para retorno
        $user = [
            'id' => $usuario['id'],
            'name' => $usuario['nome'],
            'email' => $usuario['email'],
            'imageUrl' => $usuario['imagem_url'] ?: "https://ui-avatars.com/api/?name=" . urlencode($usuario['nome']) . "&background=0066cc&color=fff"
        ];
        
        jsonResponse(true, ['user' => $user], 'Login com Google realizado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

// Funções de autenticação existentes
function login() {
    $conn = getConnection();
    
    try {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            jsonResponse(false, null, 'Email e senha são obrigatórios');
        }
        
        // Verificar se o usuário existe
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Email ou senha incorretos');
        }
        
        $usuario = $result->fetch_assoc();
        
        // Verificar senha usando hash seguro
        if (!password_verify($password, $usuario['senha'])) {
            jsonResponse(false, null, 'Email ou senha incorretos');
        }
        
        // Iniciar sessão
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        
        jsonResponse(true, [
            'usuario_id' => $usuario['id'],
            'nome' => $usuario['nome']
        ], 'Login realizado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function register() {
    $conn = getConnection();
    
    try {
        $nome = $_POST['nome'] ?? '';
        $sobrenome = $_POST['sobrenome'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validações
        if (empty($nome) || empty($sobrenome) || empty($email) || empty($telefone) || empty($password)) {
            jsonResponse(false, null, 'Todos os campos são obrigatórios');
        }
        
        if ($password !== $confirmPassword) {
            jsonResponse(false, null, 'As senhas não coincidem');
        }
        
        // Verificar se o email já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            jsonResponse(false, null, 'Email já cadastrado');
        }
        
        // Criar usuário com senha hasheada
        $nomeCompleto = $nome . ' ' . $sobrenome;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nomeCompleto, $email, $telefone, $hashedPassword);
        $stmt->execute();
        $usuarioId = $conn->insert_id;
        
        // Iniciar sessão
        session_start();
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_nome'] = $nomeCompleto;
        $_SESSION['usuario_email'] = $email;
        
        jsonResponse(true, [
            'usuario_id' => $usuarioId,
            'nome' => $nomeCompleto
        ], 'Cadastro realizado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

// Funções de ação existentes (mantidas)
function finalizarVenda() {
    $conn = getConnection();
    
    try {
        $carrinho = json_decode($_POST['carrinho'], true);
        $formaPagamento = $_POST['forma_pagamento'];
        $valorPago = $_POST['valor_pago'] ?? 0;
        
        // Iniciar transação
        $conn->begin_transaction();
        
        // Calcular total
        $total = 0;
        foreach ($carrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
        
        // Inserir venda
        $stmt = $conn->prepare("INSERT INTO vendas (data, forma_pagamento, total) VALUES (NOW(), ?, ?)");
        $stmt->bind_param("sd", $formaPagamento, $total);
        $stmt->execute();
        $vendaId = $conn->insert_id;
        
        // Inserir itens da venda e atualizar estoque
        foreach ($carrinho as $item) {
            // Verificar estoque atual
            $stmt = $conn->prepare("SELECT quantidade, nome FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $produto = $result->fetch_assoc();
            
            if ($produto['quantidade'] < $item['quantidade']) {
                throw new Exception("Estoque insuficiente para {$item['nome']}");
            }
            
            // Salvar estoque anterior para o histórico
            $estoqueAnterior = $produto['quantidade'];
            $estoqueAtual = $estoqueAnterior - $item['quantidade'];
            
            // Inserir item da venda - O trigger tr_item_venda_inserted automaticamente:
            // 1. Atualizará o estoque do produto
            // 2. Registrará a movimentação de saída
            $stmt = $conn->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $vendaId, $item['id'], $item['quantidade'], $item['preco']);
            $stmt->execute();
        }
        
        // Commit da transação
        $conn->commit();
        
        jsonResponse(true, ['venda_id' => $vendaId], 'Venda finalizada com sucesso');
        
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function reabastecerEstoque() {
    $conn = getConnection();
    
    try {
        $produtoId = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        
        // Verificar se o produto existe
        $stmt = $conn->prepare("SELECT id, nome, quantidade FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $produtoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Produto não encontrado');
        }
        
        $produto = $result->fetch_assoc();
        $estoqueAnterior = $produto['quantidade'];
        $estoqueAtual = $estoqueAnterior + $quantidade;
        
        // Atualizar estoque - O trigger tr_produto_estoque_updated automaticamente
        // registrará a movimentação quando o estoque for atualizado
        $stmt = $conn->prepare("UPDATE produtos SET quantidade = ? WHERE id = ?");
        $stmt->bind_param("ii", $estoqueAtual, $produtoId);
        $stmt->execute();
        
        jsonResponse(true, [
            'produto_id' => $produtoId,
            'nome' => $produto['nome'],
            'quantidade_antiga' => $estoqueAnterior,
            'quantidade_nova' => $estoqueAtual
        ], 'Estoque reabastecido com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function salvarProduto() {
    $conn = getConnection();
    
    try {
        // Verificar se o usuário está logado
        $usuarioId = verificarLogin();
        
        // Sanitize inputs
        $nome = sanitizeInput($_POST['nome'] ?? '');
        $categoria = sanitizeInput($_POST['categoria'] ?? '');
        $precoCompra = sanitizeInput($_POST['preco_compra'] ?? '');
        $precoVenda = sanitizeInput($_POST['preco_venda'] ?? '');
        $quantidade = sanitizeInput($_POST['quantidade'] ?? '');
        $limiteMinimo = sanitizeInput($_POST['limite_minimo'] ?? '');
        $validade = sanitizeInput($_POST['validade'] ?? '') ?: null;
        $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
        
        // Validate inputs
        if (empty($nome) || strlen($nome) < 2 || strlen($nome) > 100) {
            jsonResponse(false, null, 'Nome do produto deve ter entre 2 e 100 caracteres');
        }
        
        if (!in_array($categoria, ['bebida', 'comida', 'acessorio', 'outros'])) {
            jsonResponse(false, null, 'Categoria inválida');
        }
        
        if (!validatePrice($precoCompra)) {
            jsonResponse(false, null, 'Preço de compra deve ser um valor positivo');
        }
        
        if (!validatePrice($precoVenda)) {
            jsonResponse(false, null, 'Preço de venda deve ser um valor positivo');
        }
        
        if ($precoVenda <= $precoCompra) {
            jsonResponse(false, null, 'Preço de venda deve ser maior que preço de compra');
        }
        
        if (!validateQuantity($quantidade)) {
            jsonResponse(false, null, 'Quantidade deve ser um número inteiro positivo');
        }
        
        if (!validateQuantity($limiteMinimo)) {
            jsonResponse(false, null, 'Limite mínimo deve ser um número inteiro positivo');
        }
        
        // Validate date if provided
        if ($validade && !empty($validade)) {
            $date = DateTime::createFromFormat('Y-m-d', $validade);
            if (!$date || $date < new DateTime()) {
                jsonResponse(false, null, 'Data de validade deve ser futura');
            }
        }
        
        // Verificar se o produto já existe para este usuário
        $stmt = $conn->prepare("SELECT id FROM produtos WHERE nome = ? AND usuario_id = ?");
        $stmt->bind_param("si", $nome, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            jsonResponse(false, null, 'Você já possui um produto com este nome');
        }
        
        // Inserir produto com usuario_id e novos campos de preço
        $stmt = $conn->prepare("INSERT INTO produtos (nome, preco_compra, preco_venda, quantidade, categoria, limite_minimo, validade, observacoes, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddisissi", $nome, $precoCompra, $precoVenda, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes, $usuarioId);
        $stmt->execute();
        $produtoId = $conn->insert_id;
        
        // Não é necessário registrar movimentação manualmente
        // O trigger tr_produto_inserted já faz isso automaticamente
        
        jsonResponse(true, ['produto_id' => $produtoId], 'Produto cadastrado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function atualizarProduto() {
    $conn = getConnection();
    
    try {
        $usuarioId = verificarLogin();
        
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $categoria = $_POST['categoria'];
        $precoCompra = $_POST['preco_compra'];
        $precoVenda = $_POST['preco_venda'];
        $quantidade = $_POST['quantidade'];
        $limiteMinimo = $_POST['limite_minimo'];
        $validade = $_POST['validade'] ?: null;
        $observacoes = $_POST['observacoes'] ?: '';
        
        // Verificar se o produto existe e pertence ao usuário
        $stmt = $conn->prepare("SELECT id FROM produtos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Produto não encontrado ou não pertence a você');
        }
        
        // Validar preços
        if (!validatePrice($precoCompra)) {
            jsonResponse(false, null, 'Preço de compra deve ser um valor positivo');
        }
        
        if (!validatePrice($precoVenda)) {
            jsonResponse(false, null, 'Preço de venda deve ser um valor positivo');
        }
        
        if ($precoVenda <= $precoCompra) {
            jsonResponse(false, null, 'Preço de venda deve ser maior que preço de compra');
        }
        
        // Atualizar produto
        $stmt = $conn->prepare("UPDATE produtos SET nome = ?, preco_compra = ?, preco_venda = ?, quantidade = ?, categoria = ?, limite_minimo = ?, validade = ?, observacoes = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("sddississi", $nome, $precoCompra, $precoVenda, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes, $id, $usuarioId);
        $stmt->execute();
        
        jsonResponse(true, ['produto_id' => $id], 'Produto atualizado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function excluirProduto() {
    $conn = getConnection();
    
    try {
        $usuarioId = verificarLogin();
        $id = $_POST['id'];
        
        // Verificar se o produto existe e pertence ao usuário
        $stmt = $conn->prepare("SELECT id, nome FROM produtos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Produto não encontrado ou não pertence a você');
        }
        
        $produto = $result->fetch_assoc();
        
        // Produto pode ser excluído independentemente de ter vendas
        // As vendas serão mantidas no histórico para auditoria
        
        // Excluir produto
        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuarioId);
        $stmt->execute();
        
        jsonResponse(true, ['produto_id' => $id, 'nome' => $produto['nome']], 'Produto excluído com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function verificarEstoqueBaixo() {
    $conn = getConnection();
    
    try {
        $usuarioId = getUsuarioLogado();
        
        // Se não estiver logado, não mostrar alertas
        if (!$usuarioId) {
            jsonResponse(false, null, 'Usuário não logado');
        }
        
        // Buscar produto com estoque baixo do usuário (incluindo produtos zerados)
        $stmt = $conn->prepare("SELECT id, nome, quantidade, limite_minimo FROM produtos WHERE quantidade <= limite_minimo AND ativo = 1 AND usuario_id = ? ORDER BY quantidade ASC, nome ASC LIMIT 1");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            jsonResponse(true, ['produto' => $produto]);
        } else {
            jsonResponse(false, null, 'Nenhum produto com estoque baixo');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function getProduto() {
    $conn = getConnection();
    
    try {
        $usuarioId = getUsuarioLogado();
        $id = $_GET['id'];
        
        // Se não estiver logado, não permitir acesso
        if (!$usuarioId) {
            jsonResponse(false, null, 'Usuário não está logado');
        }
        
        // Buscar apenas produtos do usuário logado
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            jsonResponse(true, ['produto' => $produto]);
        } else {
            jsonResponse(false, null, 'Produto não encontrado ou não pertence a você');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function getProdutosMaisVendidos() {
    $conn = getConnection();
    
    try {
        $usuarioId = getUsuarioLogado();
        
        // Se não estiver logado, retornar vazio
        if (!$usuarioId) {
            jsonResponse(true, []);
        }
        
        // Buscar produtos mais vendidos dos últimos 30 dias (não apenas hoje)
        $sql = "SELECT p.nome, p.categoria, SUM(iv.quantidade) as total_vendido, 
                       COUNT(DISTINCT iv.venda_id) as num_vendas
                FROM itens_venda iv 
                JOIN produtos p ON iv.produto_id = p.id 
                JOIN vendas v ON iv.venda_id = v.id 
                WHERE v.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND p.usuario_id = ? 
                GROUP BY p.id, p.nome, p.categoria 
                ORDER BY total_vendido DESC 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $produtos = [];
        while ($row = $result->fetch_assoc()) {
            $produtos[] = [
                'nome' => $row['nome'],
                'categoria' => $row['categoria'],
                'total_vendido' => (int)$row['total_vendido'],
                'num_vendas' => (int)$row['num_vendas']
            ];
        }
        
        jsonResponse(true, $produtos);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function criarNotificacao() {
    $conn = getConnection();
    
    try {
        $usuarioId = verificarLogin();
        
        $titulo = sanitizeInput($_POST['titulo'] ?? '');
        $mensagem = sanitizeInput($_POST['mensagem'] ?? '');
        $tipo = sanitizeInput($_POST['tipo'] ?? 'info');
        $produtoId = $_POST['produto_id'] ?? null;
        $acao = sanitizeInput($_POST['acao'] ?? '');
        
        if (empty($titulo) || empty($mensagem)) {
            jsonResponse(false, null, 'Título e mensagem são obrigatórios');
        }
        
        $stmt = $conn->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, produto_id, acao) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssIs", $usuarioId, $tipo, $titulo, $mensagem, $produtoId, $acao);
        $stmt->execute();
        $notificacaoId = $conn->insert_id;
        
        jsonResponse(true, ['notificacao_id' => $notificacaoId], 'Notificação criada com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

// Função auxiliar para criar notificação de ação de produto
function notificarAcao($usuarioId, $acao, $produtoNome, $produtoId = null) {
    $conn = getConnection();
    
    $acoes = [
        'cadastrado' => ['tipo' => 'success', 'titulo' => 'Produto Cadastrado', 'icone' => '➕'],
        'atualizado' => ['tipo' => 'info', 'titulo' => 'Produto Atualizado', 'icone' => '✏️'],
        'reabastecido' => ['tipo' => 'success', 'titulo' => 'Estoque Reabastecido', 'icone' => '📦'],
        'excluido' => ['tipo' => 'warning', 'titulo' => 'Produto Excluído', 'icone' => '🗑️']
    ];
    
    if (isset($acoes[$acao])) {
        $config = $acoes[$acao];
        $mensagem = $config['icone'] . ' Produto "' . $produtoNome . '" foi ' . $acao . ' com sucesso!';
        
        try {
            $stmt = $conn->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, produto_id, acao) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssIs", $usuarioId, $config['tipo'], $config['titulo'], $mensagem, $produtoId, $acao);
            $stmt->execute();
        } catch (Exception $e) {
            // Silenciar erros de notificação para não afetar a operação principal
        }
    }
    
    $conn->close();
}
?>
