<?php
/**
 * ACTIONS.PHP CORRIGIDO - Versão Simplificada
 */

// Headers de segurança e configuração
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar erros para produção
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Capturar qualquer output indesejado
ob_start();

// Função para resposta JSON limpa
function cleanJsonResponse($success, $data = null, $message = '') {
    // Limpar qualquer output anterior
    if (ob_get_level()) {
        ob_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Conexão direta com banco para evitar problemas de autoload
    $host = 'localhost';
    $dbname = 'sistema_carrinho';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Processar requisições POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($email) || empty($password)) {
                    cleanJsonResponse(false, null, 'Email e senha são obrigatórios');
                }
                
                // Login demo rápido
                if ($email === 'demo@carrinho.com' && $password === '123456') {
                    cleanJsonResponse(true, [
                        'nome' => 'Usuário Demo',
                        'tipo_usuario' => 'administrador',
                        'usuario_id' => 999
                    ], 'Login demo realizado com sucesso!');
                }
                
                // Buscar usuário
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    cleanJsonResponse(false, null, 'Email ou senha incorretos');
                }
                
                // Verificar senha
                if (!password_verify($password, $user['senha'])) {
                    cleanJsonResponse(false, null, 'Email ou senha incorretos');
                }
                
                // Iniciar sessão
                session_start();
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['usuario_tipo'] = $user['tipo_usuario'] ?? 'administrador';
                
                cleanJsonResponse(true, [
                    'usuario_id' => $user['id'],
                    'nome' => $user['nome'],
                    'tipo_usuario' => $user['tipo_usuario'] ?? 'administrador',
                    'funcao_funcionario' => $user['funcao_funcionario'] ?? null,
                    'codigo_unico' => $user['codigo_unico'] ?? null,
                    'admin_id' => $user['codigo_admin'] ?? null
                ], 'Login realizado com sucesso');
                break;
                
            case 'register':
                $tipoCadastro = $_POST['tipo_cadastro'] ?? 'administrador';
                $nome = $_POST['nome'] ?? '';
                $sobrenome = $_POST['sobrenome'] ?? '';
                $email = $_POST['email'] ?? '';
                $telefone = $_POST['telefone'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Validações básicas
                if (empty($nome) || empty($sobrenome) || empty($email) || empty($password)) {
                    cleanJsonResponse(false, null, 'Todos os campos são obrigatórios');
                }
                
                if ($password !== $confirmPassword) {
                    cleanJsonResponse(false, null, 'As senhas não coincidem');
                }
                
                // Verificar se email já existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    cleanJsonResponse(false, null, 'Email já cadastrado');
                }
                
                $nomeCompleto = $nome . ' ' . $sobrenome;
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                if ($tipoCadastro === 'funcionario') {
                    // Cadastro de funcionário
                    $codigoAdmin = $_POST['codigo_admin'] ?? '';
                    
                    if (empty($codigoAdmin)) {
                        cleanJsonResponse(false, null, 'Código do administrador é obrigatório');
                    }
                    
                    // Verificar código (agora pode ser usado múltiplas vezes)
                    $stmt = $pdo->prepare("SELECT * FROM codigos_funcionarios WHERE codigo = ? AND ativo = 1");
                    $stmt->execute([$codigoAdmin]);
                    $codigo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$codigo) {
                        cleanJsonResponse(false, null, 'Código inválido ou inativo');
                    }
                    
                    // Inserir funcionário sem função (será definida pelo admin depois)
                    // Salvar o código digitado no campo codigo_unico
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, funcao_funcionario, codigo_admin, codigo_unico, data_cadastro) VALUES (?, ?, ?, ?, 'funcionario', NULL, ?, ?, NOW())");
                    $stmt->execute([$nomeCompleto, $email, $telefone, $hashedPassword, $codigo['admin_id'], $codigoAdmin]);
                    $userId = $pdo->lastInsertId();
                    
                    // Registrar uso do código (mas não marcar como "usado" para permitir múltiplos usos)
                    $stmt = $pdo->prepare("INSERT INTO usos_codigo (codigo_id, usuario_id, data_uso) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE data_uso = NOW()");
                    $stmt->execute([$codigo['id'], $userId]);
                    
                } else {
                    // Cadastro de administrador
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, data_cadastro) VALUES (?, ?, ?, ?, 'administrador', NOW())");
                    $stmt->execute([$nomeCompleto, $email, $telefone, $hashedPassword]);
                    $userId = $pdo->lastInsertId();
                    
                    // Gerar código único para administrador
                    $codigoUnico = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET codigo_unico = ? WHERE id = ?");
                    $stmt->execute([$codigoUnico, $userId]);
                }
                
                cleanJsonResponse(true, [
                    'usuario_id' => $userId,
                    'nome' => $nomeCompleto
                ], 'Cadastro realizado com sucesso');
                break;
                
            case 'gerarCodigoFuncionario':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                // Verificar se é administrador
                $stmt = $pdo->prepare("SELECT tipo_usuario FROM usuarios WHERE id = ?");
                $stmt->execute([$usuarioId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user || $user['tipo_usuario'] !== 'administrador') {
                    cleanJsonResponse(false, null, 'Apenas administradores podem gerar códigos');
                }
                
                // Gerar código único
                $tentativas = 0;
                do {
                    $codigo = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                    
                    $stmt = $pdo->prepare("SELECT id FROM codigos_funcionarios WHERE codigo = ?");
                    $stmt->execute([$codigo]);
                    $existe = $stmt->fetch();
                    
                    $tentativas++;
                } while ($existe && $tentativas < 100);
                
                if ($existe) {
                    cleanJsonResponse(false, null, 'Erro ao gerar código único');
                }
                
                // Inserir código na tabela (sem função específica)
                $stmt = $pdo->prepare("INSERT INTO codigos_funcionarios (codigo, admin_id, ativo, data_criacao) VALUES (?, ?, 1, NOW())");
                $stmt->execute([$codigo, $usuarioId]);
                
                cleanJsonResponse(true, [
                    'codigo' => $codigo,
                    'data_criacao' => date('Y-m-d H:i:s')
                ], 'Código universal gerado com sucesso');
                break;
                
            case 'listarCodigosFuncionarios':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                // Buscar códigos e seus usuários
                $stmt = $pdo->prepare("
                    SELECT cf.*, 
                           GROUP_CONCAT(
                               CONCAT(u.nome, '|', u.id, '|', COALESCE(u.funcao_funcionario, ''), '|', u.email)
                               ORDER BY u.data_cadastro DESC 
                               SEPARATOR ';;'
                           ) as funcionarios_info
                    FROM codigos_funcionarios cf 
                    LEFT JOIN usos_codigo uc ON cf.id = uc.codigo_id
                    LEFT JOIN usuarios u ON uc.usuario_id = u.id 
                    WHERE cf.admin_id = ? AND cf.ativo = 1 
                    GROUP BY cf.id
                    ORDER BY cf.data_criacao DESC
                ");
                $stmt->execute([$usuarioId]);
                $codigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Processar funcionários por código
                foreach ($codigos as &$codigo) {
                    $codigo['funcionarios'] = [];
                    if ($codigo['funcionarios_info']) {
                        $funcionarios_raw = explode(';;', $codigo['funcionarios_info']);
                        foreach ($funcionarios_raw as $func_raw) {
                            $parts = explode('|', $func_raw);
                            if (count($parts) >= 4) {
                                $codigo['funcionarios'][] = [
                                    'nome' => $parts[0],
                                    'id' => $parts[1], 
                                    'funcao' => $parts[2],
                                    'email' => $parts[3]
                                ];
                            }
                        }
                    }
                }
                
                cleanJsonResponse(true, $codigos, 'Códigos listados com sucesso');
                break;
                
            case 'atualizarFuncaoFuncionario':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $funcionarioId = $_POST['funcionario_id'] ?? '';
                $novaFuncao = $_POST['nova_funcao'] ?? '';
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                if (empty($funcionarioId) || empty($novaFuncao)) {
                    cleanJsonResponse(false, null, 'ID do funcionário e nova função são obrigatórios');
                }
                
                // Verificar se é admin e se o funcionário pertence a ele
                $stmt = $pdo->prepare("
                    SELECT u.nome 
                    FROM usuarios u 
                    WHERE u.id = ? AND u.codigo_admin = ? AND u.tipo_usuario = 'funcionario'
                ");
                $stmt->execute([$funcionarioId, $usuarioId]);
                $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$funcionario) {
                    cleanJsonResponse(false, null, 'Funcionário não encontrado ou você não tem permissão');
                }
                
                // Atualizar função
                $stmt = $pdo->prepare("UPDATE usuarios SET funcao_funcionario = ? WHERE id = ?");
                $stmt->execute([$novaFuncao, $funcionarioId]);
                
                cleanJsonResponse(true, [
                    'funcionario_nome' => $funcionario['nome'],
                    'nova_funcao' => $novaFuncao
                ], 'Função atualizada com sucesso');
                break;
                
            case 'criarPedidoDeVenda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $nomeCliente = $_POST['nome_cliente'] ?? '';
                $telefoneCliente = $_POST['telefone_cliente'] ?? '';
                $produtos = $_POST['produtos'] ?? '';
                $total = $_POST['total'] ?? 0;
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                if (empty($produtos) || empty($total)) {
                    cleanJsonResponse(false, null, 'Produtos e total são obrigatórios');
                }
                
                // Inserir pedido
                $stmt = $pdo->prepare("
                    INSERT INTO pedidos (nome_cliente, telefone_cliente, produtos, total, usuario_vendedor_id, observacoes, data_pedido) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $nomeCliente,
                    $telefoneCliente,
                    $produtos,
                    $total,
                    $usuarioId,
                    $observacoes
                ]);
                
                $pedidoId = $pdo->lastInsertId();
                
                cleanJsonResponse(true, [
                    'pedido_id' => $pedidoId
                ], 'Pedido criado com sucesso');
                break;
                
            case 'atualizarStatusPedido':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $pedidoId = $_POST['pedido_id'] ?? '';
                $novoStatus = $_POST['novo_status'] ?? '';
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                if (empty($pedidoId) || empty($novoStatus)) {
                    cleanJsonResponse(false, null, 'ID do pedido e novo status são obrigatórios');
                }
                
                // Verificar se o pedido existe
                $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = ?");
                $stmt->execute([$pedidoId]);
                if (!$stmt->fetch()) {
                    cleanJsonResponse(false, null, 'Pedido não encontrado');
                }
                
                // Atualizar status
                $stmt = $pdo->prepare("UPDATE pedidos SET status = ?, data_atualizacao = NOW() WHERE id = ?");
                $stmt->execute([$novoStatus, $pedidoId]);
                
                cleanJsonResponse(true, [
                    'pedido_id' => $pedidoId,
                    'novo_status' => $novoStatus
                ], 'Status do pedido atualizado com sucesso');
                break;
                
            case 'finalizar_venda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $carrinho = $_POST['carrinho'] ?? '';
                $formaPagamento = $_POST['forma_pagamento'] ?? '';
                $nomeCliente = $_POST['nome_cliente'] ?? '';
                $telefoneCliente = $_POST['telefone_cliente'] ?? '';
                $criarPedido = $_POST['criar_pedido'] ?? '0';
                $valorPago = $_POST['valor_pago'] ?? 0;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                if (empty($carrinho)) {
                    cleanJsonResponse(false, null, 'Carrinho não pode estar vazio');
                }
                
                // Decodificar carrinho
                $itensCarrinho = json_decode($carrinho, true);
                if (!$itensCarrinho) {
                    cleanJsonResponse(false, null, 'Formato inválido do carrinho');
                }
                
                // Calcular total
                $total = 0;
                foreach ($itensCarrinho as $item) {
                    $total += $item['preco'] * $item['quantidade'];
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Verificar se há funcionários do financeiro no sistema
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE funcao_funcionario IN ('financeiro', 'financeiro_e_anotar') AND ativo = 1");
                    $stmt->execute();
                    $temFinanceiro = $stmt->fetchColumn() > 0;
                    
                    // Status inicial da venda baseado na existência de funcionários do financeiro
                    $statusPagamento = $temFinanceiro ? 'pendente' : 'pago';
                    
                    // 1. Registrar venda
                    $stmt = $pdo->prepare("
                        INSERT INTO vendas (usuario_id, nome_cliente, total, forma_pagamento, valor_pago, status_pagamento, data_venda) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$usuarioId, $nomeCliente, $total, $formaPagamento, $valorPago, $statusPagamento]);
                    $vendaId = $pdo->lastInsertId();
                    
                    // 2. Atualizar estoque dos produtos
                    foreach ($itensCarrinho as $item) {
                        $stmt = $pdo->prepare("
                            UPDATE produtos 
                            SET quantidade = quantidade - ? 
                            WHERE id = ? AND usuario_id = ? AND quantidade >= ?
                        ");
                        $result = $stmt->execute([
                            $item['quantidade'],
                            $item['id'],
                            $usuarioId,
                            $item['quantidade']
                        ]);
                        
                        if ($stmt->rowCount() === 0) {
                            throw new Exception('Estoque insuficiente para: ' . $item['nome']);
                        }
                    }
                    
                    $pedidoId = null;
                    $pedidoCriado = false;
                    
                    // 3. Criar pedido se solicitado
                    if ($criarPedido === '1') {
                        $stmt = $pdo->prepare("
                            INSERT INTO pedidos (nome_cliente, telefone_cliente, produtos, total, usuario_vendedor_id, status, data_pedido) 
                            VALUES (?, ?, ?, ?, ?, 'pendente', NOW())
                        ");
                        $stmt->execute([
                            $nomeCliente,
                            $telefoneCliente,
                            $carrinho, // Usar JSON original do carrinho
                            $total,
                            $usuarioId
                        ]);
                        $pedidoId = $pdo->lastInsertId();
                        $pedidoCriado = true;
                    }
                    
                    $pdo->commit();
                    
                    $mensagemSucesso = $temFinanceiro ? 
                        'Venda registrada! Aguardando processamento do pagamento pelo financeiro.' : 
                        'Venda finalizada com sucesso!';
                    
                    cleanJsonResponse(true, [
                        'venda_id' => $vendaId,
                        'pedido_criado' => $pedidoCriado,
                        'pedido_id' => $pedidoId,
                        'total' => $total,
                        'status_pagamento' => $statusPagamento,
                        'tem_financeiro' => $temFinanceiro
                    ], $mensagemSucesso);
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro ao processar venda: ' . $e->getMessage());
                }
                break;
                
            case 'processarPagamento':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $vendaId = $_POST['venda_id'] ?? '';
                $metodoPagamento = $_POST['metodo_pagamento'] ?? '';
                $valorRecebido = $_POST['valor_recebido'] ?? 0;
                $observacoes = $_POST['observacoes'] ?? '';
                $troco = $_POST['troco'] ?? 0;
                
                if (!$vendaId || !$metodoPagamento) {
                    cleanJsonResponse(false, null, 'Dados obrigatórios não informados');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Verificar se a venda existe e está pendente
                    $stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ? AND status_pagamento = 'pendente'");
                    $stmt->execute([$vendaId]);
                    $venda = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$venda) {
                        throw new Exception('Venda não encontrada ou já processada');
                    }
                    
                    // Atualizar venda com dados do pagamento
                    $stmt = $pdo->prepare("
                        UPDATE vendas 
                        SET status_pagamento = 'pago',
                            metodo_pagamento = ?,
                            processado_por_financeiro = ?,
                            valor_pago = ?,
                            observacoes_pagamento = ?,
                            data_pagamento = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $metodoPagamento,
                        $usuarioId,
                        $valorRecebido,
                        $observacoes,
                        $vendaId
                    ]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, [
                        'venda_id' => $vendaId,
                        'metodo_pagamento' => $metodoPagamento,
                        'valor_total' => number_format($venda['total'], 2, ',', '.'),
                        'troco' => $metodoPagamento === 'dinheiro' ? number_format($troco, 2, ',', '.') : null
                    ], 'Pagamento processado com sucesso');
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro ao processar pagamento: ' . $e->getMessage());
                }
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação inválida: ' . $action);
        }
    }
    
    // Processar requisições GET
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'listarCodigosFuncionarios':
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                // Buscar todos os códigos ativos do admin
                $stmt = $pdo->prepare("
                    SELECT codigo, data_criacao
                    FROM codigos_funcionarios 
                    WHERE admin_id = ? AND ativo = 1 
                    GROUP BY codigo
                    ORDER BY data_criacao DESC
                ");
                $stmt->execute([$usuarioId]);
                $codigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Para cada código, buscar os funcionários cadastrados
                $resultado = [];
                foreach ($codigos as $codigo) {
                    $stmt = $pdo->prepare("
                        SELECT u.id, u.nome, u.email, u.funcao_funcionario as funcao
                        FROM usuarios u
                        WHERE u.codigo_unico = ? AND u.tipo_usuario = 'funcionario'
                        ORDER BY u.nome
                    ");
                    $stmt->execute([$codigo['codigo']]);
                    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $resultado[] = [
                        'codigo' => $codigo['codigo'],
                        'data_criacao' => $codigo['data_criacao'],
                        'funcionarios' => $funcionarios
                    ];
                }
                
                cleanJsonResponse(true, $resultado, 'Códigos listados com sucesso');
                break;
                
            case 'listarPedidos':
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                // Filtros opcionais
                $filtroStatus = $_GET['status'] ?? '';
                $filtroData = $_GET['data'] ?? '';
                
                $where = [];
                $params = [];
                
                if (!empty($filtroStatus)) {
                    $where[] = "status = ?";
                    $params[] = $filtroStatus;
                }
                
                if (!empty($filtroData)) {
                    $where[] = "DATE(data_pedido) = ?";
                    $params[] = $filtroData;
                }
                
                $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                
                $stmt = $pdo->prepare("
                    SELECT p.*, u.nome as vendedor_nome 
                    FROM pedidos p 
                    LEFT JOIN usuarios u ON p.usuario_vendedor_id = u.id 
                    $whereClause
                    ORDER BY p.data_pedido DESC
                    LIMIT 100
                ");
                $stmt->execute($params);
                $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                cleanJsonResponse(true, $pedidos, 'Pedidos listados com sucesso');
                break;
                
            case 'listarVendasFinanceiro':
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                // Filtros opcionais
                $filtroStatus = $_GET['status'] ?? '';
                $filtroVendedor = $_GET['vendedor'] ?? '';
                $filtroData = $_GET['data'] ?? '';
                
                $where = [];
                $params = [];
                
                if (!empty($filtroStatus)) {
                    $where[] = "v.status_pagamento = ?";
                    $params[] = $filtroStatus;
                }
                
                if (!empty($filtroVendedor)) {
                    $where[] = "v.usuario_id = ?";
                    $params[] = $filtroVendedor;
                }
                
                if (!empty($filtroData)) {
                    $where[] = "DATE(v.data) = ?";
                    $params[] = $filtroData;
                }
                
                $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                
                // Query usando a estrutura correta do banco
                $stmt = $pdo->prepare("
                    SELECT v.id, v.total, v.data as data_venda, 
                           COALESCE(v.status_pagamento, 'pago') as status_pagamento,
                           COALESCE(v.metodo_pagamento, v.forma_pagamento) as metodo_pagamento,
                           v.observacoes_pagamento,
                           v.data_pagamento, 
                           COALESCE(v.nome_cliente, v.cliente_nome) as nome_cliente, 
                           v.usuario_id,
                           u.nome as vendedor_nome,
                           'Venda finalizada' as produtos_info
                    FROM vendas v
                    LEFT JOIN usuarios u ON v.usuario_id = u.id
                    $whereClause
                    ORDER BY v.data DESC
                    LIMIT 100
                ");
                $stmt->execute($params);
                $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                cleanJsonResponse(true, $vendas, 'Vendas listadas com sucesso');
                break;
                
            case 'listarVendedores':
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $stmt = $pdo->prepare("
                    SELECT DISTINCT u.id, u.nome 
                    FROM usuarios u 
                    INNER JOIN vendas v ON u.id = v.usuario_id 
                    WHERE u.funcao_funcionario IN ('anotar_pedido', 'financeiro_e_anotar', 'ambos') 
                    ORDER BY u.nome
                ");
                $stmt->execute();
                $vendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                cleanJsonResponse(true, $vendedores, 'Vendedores listados com sucesso');
                break;
                
            case 'obterDetalhesVenda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $vendaId = $_GET['venda_id'] ?? '';
                
                if (!$vendaId) {
                    cleanJsonResponse(false, null, 'ID da venda não informado');
                }
                
                $stmt = $pdo->prepare("
                    SELECT v.*, u.nome as vendedor_nome,
                           uf.nome as financeiro_nome,
                           'Detalhes dos produtos' as produtos_info
                    FROM vendas v
                    LEFT JOIN usuarios u ON v.usuario_id = u.id
                    LEFT JOIN usuarios uf ON v.processado_por_financeiro = uf.id
                    WHERE v.id = ?
                ");
                $stmt->execute([$vendaId]);
                $venda = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$venda) {
                    cleanJsonResponse(false, null, 'Venda não encontrada');
                }
                
                cleanJsonResponse(true, $venda, 'Detalhes da venda obtidos com sucesso');
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação GET inválida: ' . $action);
        }
    }
    
    cleanJsonResponse(false, null, 'Método não permitido');
    
} catch (Exception $e) {
    cleanJsonResponse(false, null, 'Erro interno: ' . $e->getMessage());
}
?>