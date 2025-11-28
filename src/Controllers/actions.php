<?php
/**
 * ACTIONS.PHP - Versão Corrigida para SDK 54
 */

// Headers CORS para permitir requisições do app mobile
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Headers de segurança e configuração
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Habilitar exibição de erros para debug (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capturar qualquer output indesejado
ob_start();

// Função para resposta JSON limpa
function cleanJsonResponse($success, $data = null, $message = '') {
    // Limpar qualquer output anterior
    while (ob_get_level()) {
        ob_end_clean();
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

// Função para log de erros
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../../logs/php_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] ERROR: $message$contextStr\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    // Conexão direta com banco para evitar problemas de autoload
    $host = 'localhost';
    $dbname = 'sistema_carrinho';
    $username = 'root';
    $password = '';
    
    try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError('Erro de conexão com banco de dados', ['error' => $e->getMessage()]);
        cleanJsonResponse(false, null, 'Erro de conexão com o banco de dados');
    }
    
    // Processar requisições POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if (empty($action)) {
            cleanJsonResponse(false, null, 'Ação não especificada');
        }
        
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
                    
                    // Verificar código
                    $stmt = $pdo->prepare("SELECT * FROM codigos_funcionarios WHERE codigo = ? AND ativo = 1");
                    $stmt->execute([$codigoAdmin]);
                    $codigo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$codigo) {
                        cleanJsonResponse(false, null, 'Código inválido ou inativo');
                    }
                    
                    // Inserir funcionário
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo_usuario, funcao_funcionario, codigo_admin, codigo_unico, data_cadastro) VALUES (?, ?, ?, ?, 'funcionario', NULL, ?, ?, NOW())");
                    $stmt->execute([$nomeCompleto, $email, $telefone, $hashedPassword, $codigo['admin_id'], $codigoAdmin]);
                    $userId = $pdo->lastInsertId();
                    
                    // Registrar uso do código
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
                
            case 'finalizar_venda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                $carrinho = $_POST['carrinho'] ?? '';
                $formaPagamento = $_POST['forma_pagamento'] ?? '';
                $nomeCliente = $_POST['nome_cliente'] ?? '';
                $telefoneCliente = $_POST['telefone_cliente'] ?? '';
                $criarPedido = $_POST['criar_pedido'] ?? '0';
                $valorPago = $_POST['valor_pago'] ?? 0;
                
                // Pagamento misto (opcional)
                $formaPagamentoSecundaria = $_POST['forma_pagamento_secundaria'] ?? null;
                $valorPagoSecundario = $_POST['valor_pago_secundario'] ?? null;
                $formaPagamentoTerciaria = $_POST['forma_pagamento_terciaria'] ?? null;
                $valorPagoTerciario = $_POST['valor_pago_terciario'] ?? null;
                
                // Cliente fiado (opcional)
                $clienteFiadoId = $_POST['cliente_fiado_id'] ?? null;
                
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
                    
                    // Verificar se há funcionários do financeiro
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE funcao_funcionario IN ('financeiro', 'financeiro_e_anotar') AND ativo = 1");
                    $stmt->execute();
                    $temFinanceiro = $stmt->fetchColumn() > 0;
                    
                    // Status inicial da venda
                    $statusPagamento = $temFinanceiro ? 'pendente' : 'pago';
                    
                    // 1. Registrar venda (com cliente fiado se houver)
                    $stmt = $pdo->prepare("
                        INSERT INTO vendas (
                            usuario_id, nome_cliente, total, 
                            forma_pagamento, valor_pago, 
                            forma_pagamento_secundaria, valor_pago_secundario,
                            forma_pagamento_terciaria, valor_pago_terciario,
                            cliente_fiado_id, status_pagamento, data
                        ) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $usuarioId, 
                        $nomeCliente, 
                        $total, 
                        $formaPagamento, 
                        $valorPago,
                        $formaPagamentoSecundaria,
                        $valorPagoSecundario,
                        $formaPagamentoTerciaria,
                        $valorPagoTerciario,
                        $clienteFiadoId,
                        $statusPagamento
                    ]);
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
                            $carrinho,
                            $total,
                            $usuarioId
                        ]);
                        $pedidoId = $pdo->lastInsertId();
                        $pedidoCriado = true;
                    }
                    
                    // 4. Registrar compra fiada se houver cliente
                    if ($clienteFiadoId) {
                        // Calcular valor fiado (pode ser parcial se houver pagamento misto)
                        $valorFiado = 0;
                        if ($formaPagamento === 'fiado') {
                            $valorFiado += floatval($valorPago);
                        }
                        if ($formaPagamentoSecundaria === 'fiado') {
                            $valorFiado += floatval($valorPagoSecundario);
                        }
                        if ($formaPagamentoTerciaria === 'fiado') {
                            $valorFiado += floatval($valorPagoTerciario);
                        }
                        
                        if ($valorFiado > 0) {
                            // Registrar compra no histórico
                            $stmt = $pdo->prepare("
                                INSERT INTO pagamentos_fiado 
                                (cliente_id, venda_id, valor, tipo, forma_pagamento, observacoes, data_pagamento, registrado_por) 
                                VALUES (?, ?, ?, 'compra', 'Fiado', ?, NOW(), ?)
                            ");
                            $stmt->execute([
                                $clienteFiadoId,
                                $vendaId,
                                $valorFiado,
                                'Venda Rápida - Carrinho de Praia',
                                $usuarioId
                            ]);
                            
                            // Atualizar saldo devedor e última compra do cliente
                            $stmt = $pdo->prepare("
                                UPDATE clientes_fiado 
                                SET saldo_devedor = saldo_devedor + ?, 
                                    ultima_compra = NOW() 
                                WHERE id = ?
                            ");
                            $stmt->execute([$valorFiado, $clienteFiadoId]);
                        }
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
                    logError('Erro ao processar venda', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    cleanJsonResponse(false, null, 'Erro ao processar venda: ' . $e->getMessage());
                }
                break;
                
            case 'salvar_produto':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $nome = $_POST['nome'] ?? '';
                $categoria = $_POST['categoria'] ?? '';
                $precoCompra = $_POST['preco_compra'] ?? 0;
                $precoVenda = $_POST['preco_venda'] ?? 0;
                $quantidade = $_POST['quantidade'] ?? 0;
                $limiteMinimo = $_POST['limite_minimo'] ?? 0;
                $validade = $_POST['validade'] ?? null;
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (empty($nome) || empty($categoria)) {
                    cleanJsonResponse(false, null, 'Nome e categoria são obrigatórios');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO produtos 
                        (nome, categoria, preco_compra, preco_venda, quantidade, limite_minimo, validade, observacoes, usuario_id, data_cadastro) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $nome,
                        $categoria,
                        $precoCompra,
                        $precoVenda,
                        $quantidade,
                        $limiteMinimo,
                        $validade,
                        $observacoes,
                        $usuarioId
                    ]);
                    
                    cleanJsonResponse(true, ['produto_id' => $pdo->lastInsertId(), 'nome' => $nome], 'Produto cadastrado com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao cadastrar produto', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao cadastrar produto: ' . $e->getMessage());
                }
                break;
                
            case 'atualizar_produto':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $id = $_POST['id'] ?? '';
                $nome = $_POST['nome'] ?? '';
                $categoria = $_POST['categoria'] ?? '';
                $precoCompra = $_POST['preco_compra'] ?? 0;
                $precoVenda = $_POST['preco_venda'] ?? 0;
                $quantidade = $_POST['quantidade'] ?? 0;
                $limiteMinimo = $_POST['limite_minimo'] ?? 0;
                $validade = $_POST['validade'] ?? null;
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (empty($id) || empty($nome) || empty($categoria)) {
                    cleanJsonResponse(false, null, 'ID, nome e categoria são obrigatórios');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        UPDATE produtos 
                        SET nome = ?, categoria = ?, preco_compra = ?, preco_venda = ?, 
                            quantidade = ?, limite_minimo = ?, validade = ?, observacoes = ?
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([
                        $nome,
                        $categoria,
                        $precoCompra,
                        $precoVenda,
                        $quantidade,
                        $limiteMinimo,
                        $validade,
                        $observacoes,
                        $id,
                        $usuarioId
                    ]);
                    
                    if ($stmt->rowCount() > 0) {
                        cleanJsonResponse(true, ['produto_id' => $id, 'nome' => $nome], 'Produto atualizado com sucesso');
                    } else {
                        cleanJsonResponse(false, null, 'Produto não encontrado ou sem alterações');
                    }
                } catch (Exception $e) {
                    logError('Erro ao atualizar produto', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao atualizar produto: ' . $e->getMessage());
                }
                break;
                
            case 'excluir_produto':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $id = $_POST['id'] ?? '';
                
                if (empty($id)) {
                    cleanJsonResponse(false, null, 'ID do produto é obrigatório');
                }
                
                try {
                    // Buscar nome antes de excluir
                    $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$id, $usuarioId]);
                    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$produto) {
                        cleanJsonResponse(false, null, 'Produto não encontrado');
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$id, $usuarioId]);
                    
                    cleanJsonResponse(true, ['nome' => $produto['nome']], 'Produto excluído com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao excluir produto', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao excluir produto: ' . $e->getMessage());
                }
                break;
                
            case 'reabastecer':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                $produtoId = $_POST['produto_id'] ?? '';
                $quantidade = $_POST['quantidade'] ?? 0;
                
                if (empty($produtoId) || $quantidade <= 0) {
                    cleanJsonResponse(false, null, 'Dados inválidos');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        UPDATE produtos 
                        SET quantidade = quantidade + ? 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$quantidade, $produtoId, $usuarioId]);
                    
                    // Buscar nome do produto
                    $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE id = ?");
                    $stmt->execute([$produtoId]);
                    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, ['nome' => $produto['nome'] ?? 'Produto'], 'Estoque reabastecido com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao reabastecer', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao reabastecer: ' . $e->getMessage());
                }
                break;
            
            case 'cadastrarClienteFiado':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $nome = $_POST['nome'] ?? '';
                $telefone = $_POST['telefone'] ?? '';
                $cpf = $_POST['cpf'] ?? '';
                $endereco = $_POST['endereco'] ?? '';
                $limiteCredito = $_POST['limite_credito'] ?? 500.00;
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (empty($nome)) {
                    cleanJsonResponse(false, null, 'Nome do cliente é obrigatório');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO clientes_fiado 
                        (usuario_id, nome, telefone, cpf, endereco, limite_credito, observacoes, data_cadastro) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $usuarioId,
                        $nome,
                        $telefone,
                        $cpf,
                        $endereco,
                        $limiteCredito,
                        $observacoes
                    ]);
                    
                    $clienteId = $pdo->lastInsertId();
                    
                    cleanJsonResponse(true, [
                        'cliente_id' => $clienteId,
                        'nome' => $nome
                    ], 'Cliente cadastrado com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao cadastrar cliente fiado', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao cadastrar cliente: ' . $e->getMessage());
                }
                break;
                
            case 'registrarPagamentoFiado':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $clienteId = $_POST['cliente_id'] ?? '';
                $valor = $_POST['valor'] ?? 0;
                $formaPagamento = $_POST['forma_pagamento'] ?? 'Dinheiro';
                $observacoes = $_POST['observacoes'] ?? '';
                
                if (empty($clienteId) || $valor <= 0) {
                    cleanJsonResponse(false, null, 'Dados inválidos');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Verificar se o cliente existe e pertence ao usuário
                    $stmt = $pdo->prepare("SELECT * FROM clientes_fiado WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$clienteId, $usuarioId]);
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$cliente) {
                        throw new Exception('Cliente não encontrado');
                    }
                    
                    $saldoAtual = floatval($cliente['saldo_devedor']);
                    $valorPagamento = floatval($valor);
                    
                    if ($valorPagamento > $saldoAtual) {
                        throw new Exception('Valor do pagamento maior que o saldo devedor');
                    }
                    
                    // Registrar pagamento
                    $stmt = $pdo->prepare("
                        INSERT INTO pagamentos_fiado 
                        (cliente_id, valor, tipo, forma_pagamento, observacoes, data_pagamento, registrado_por) 
                        VALUES (?, ?, 'pagamento', ?, ?, NOW(), ?)
                    ");
                    $stmt->execute([
                        $clienteId,
                        $valorPagamento,
                        $formaPagamento,
                        $observacoes,
                        $usuarioId
                    ]);
                    
                    // Atualizar saldo devedor do cliente
                    $novoSaldo = $saldoAtual - $valorPagamento;
                    $stmt = $pdo->prepare("UPDATE clientes_fiado SET saldo_devedor = ? WHERE id = ?");
                    $stmt->execute([$novoSaldo, $clienteId]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, [
                        'pagamento_id' => $pdo->lastInsertId(),
                        'saldo_anterior' => $saldoAtual,
                        'valor_pago' => $valorPagamento,
                        'novo_saldo' => $novoSaldo
                    ], 'Pagamento registrado com sucesso');
                    
                } catch (Exception $e) {
                    $pdo->rollback();
                    logError('Erro ao registrar pagamento fiado', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao registrar pagamento: ' . $e->getMessage());
                }
                break;
            
            // ========================================
            // SISTEMA DE GUARDA-SÓIS
            // ========================================
            
            case 'cadastrarGuardasol':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $numero = $_POST['numero'] ?? '';
                
                if (empty($numero)) {
                    cleanJsonResponse(false, null, 'Número do guarda-sol é obrigatório');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO guardasois (numero, usuario_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$numero, $usuarioId]);
                    
                    cleanJsonResponse(true, [
                        'guardasol_id' => $pdo->lastInsertId(),
                        'numero' => $numero
                    ], 'Guarda-sol cadastrado');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro ao cadastrar: ' . $e->getMessage());
                }
                break;
            
            case 'ocuparGuardasol':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                $clienteNome = $_POST['cliente_nome'] ?? '';
                
                if (empty($guardasolId)) {
                    cleanJsonResponse(false, null, 'ID do guarda-sol é obrigatório');
                }
                
                try {
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET status = 'ocupado', 
                            cliente_nome = ?, 
                            horario_ocupacao = NOW() 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$clienteNome, $guardasolId, $usuarioId]);
                    
                    cleanJsonResponse(true, null, 'Guarda-sol ocupado');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'adicionarComanda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                $produtos = $_POST['produtos'] ?? '';
                $subtotal = $_POST['subtotal'] ?? 0;
                
                if (empty($guardasolId) || empty($produtos)) {
                    cleanJsonResponse(false, null, 'Dados inválidos');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Buscar informações do guarda-sol e cliente
                    $stmt = $pdo->prepare("
                        SELECT numero, cliente_nome 
                        FROM guardasois 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    $guardasol = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$guardasol) {
                        $pdo->rollback();
                        cleanJsonResponse(false, null, 'Guarda-sol não encontrado');
                    }
                    
                    // Inserir comanda
                    $stmt = $pdo->prepare("
                        INSERT INTO comandas (guardasol_id, usuario_id, produtos, subtotal) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$guardasolId, $usuarioId, $produtos, $subtotal]);
                    $comandaId = $pdo->lastInsertId();
                    
                    // Atualizar total do guarda-sol
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET total_consumido = total_consumido + ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$subtotal, $guardasolId]);
                    
                    // Criar pedido automático para a cozinha/preparo
                    $numeroPedido = 'GS' . str_pad($guardasol['numero'], 3, '0', STR_PAD_LEFT) . '-' . str_pad($comandaId, 4, '0', STR_PAD_LEFT);
                    $nomeCliente = $guardasol['cliente_nome'] ?: 'Guarda-sol ' . $guardasol['numero'];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO pedidos (
                            numero_pedido, 
                            nome_cliente, 
                            produtos, 
                            total, 
                            usuario_vendedor_id, 
                            status,
                            observacoes
                        ) VALUES (?, ?, ?, ?, ?, 'pendente', ?)
                    ");
                    $stmt->execute([
                        $numeroPedido,
                        $nomeCliente,
                        $produtos,
                        $subtotal,
                        $usuarioId,
                        'Pedido do Guarda-sol ' . $guardasol['numero'] . ' - Comanda #' . $comandaId
                    ]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, [
                        'comanda_id' => $comandaId,
                        'pedido_numero' => $numeroPedido
                    ], 'Comanda adicionada e pedido enviado para preparo');
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'finalizarGuardasol':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                $vendaId = $_POST['venda_id'] ?? null;
                
                if (empty($guardasolId)) {
                    cleanJsonResponse(false, null, 'ID do guarda-sol é obrigatório');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Fechar todas as comandas abertas
                    $stmt = $pdo->prepare("
                        UPDATE comandas 
                        SET status = 'fechado', data_fechamento = NOW() 
                        WHERE guardasol_id = ? AND status = 'aberto'
                    ");
                    $stmt->execute([$guardasolId]);
                    
                    // Liberar guarda-sol
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET status = 'vazio', 
                            cliente_nome = NULL, 
                            horario_ocupacao = NULL, 
                            total_consumido = 0.00 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, null, 'Guarda-sol finalizado e liberado');
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'removerGuardasol':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                
                if (empty($guardasolId)) {
                    cleanJsonResponse(false, null, 'ID do guarda-sol é obrigatório');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Verificar se o guarda-sol está vazio
                    $stmt = $pdo->prepare("
                        SELECT status FROM guardasois 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    $guardasol = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$guardasol) {
                        cleanJsonResponse(false, null, 'Guarda-sol não encontrado');
                    }
                    
                    // Desativar guarda-sol
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET ativo = 0 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, null, 'Guarda-sol removido');
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'fecharComanda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                
                if (empty($guardasolId)) {
                    cleanJsonResponse(false, null, 'ID do guarda-sol é obrigatório');
                }
                
                try {
                    // Atualizar status do guarda-sol para aguardando pagamento
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET status = 'aguardando_pagamento' 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    
                    cleanJsonResponse(true, null, 'Comanda fechada, aguardando pagamento');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'finalizarPagamentoComanda':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não autenticado');
                }
                
                $guardasolId = $_POST['guardasol_id'] ?? '';
                $formaPagamento = $_POST['forma_pagamento'] ?? '';
                $total = $_POST['total'] ?? 0;
                
                if (empty($guardasolId) || empty($formaPagamento)) {
                    cleanJsonResponse(false, null, 'Dados inválidos');
                }
                
                try {
                    $pdo->beginTransaction();
                    
                    // Buscar comandas abertas
                    $stmt = $pdo->prepare("
                        SELECT * FROM comandas 
                        WHERE guardasol_id = ? AND status = 'aberto'
                    ");
                    $stmt->execute([$guardasolId]);
                    $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($comandas)) {
                        $pdo->rollback();
                        cleanJsonResponse(false, null, 'Nenhuma comanda aberta encontrada');
                    }
                    
                    // Preparar itens da venda
                    $itens = [];
                    foreach ($comandas as $comanda) {
                        $produtos = json_decode($comanda['produtos'], true);
                        foreach ($produtos as $prod) {
                            $itens[] = [
                                'produto_id' => $prod['produto_id'],
                                'nome' => $prod['nome'],
                                'quantidade' => $prod['quantidade'],
                                'preco' => $prod['preco_unitario']
                            ];
                        }
                    }
                    
                    // Registrar venda (usando apenas colunas que existem)
                    $stmt = $pdo->prepare("
                        INSERT INTO vendas (usuario_id, total, forma_pagamento) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$usuarioId, $total, $formaPagamento]);
                    $vendaId = $pdo->lastInsertId();
                    
                    // Registrar cada item da venda na tabela vendas_itens (se existir)
                    // Ou atualizar estoque diretamente
                    foreach ($itens as $item) {
                        // Atualizar estoque
                        $stmt = $pdo->prepare("
                            UPDATE produtos 
                            SET quantidade = quantidade - ? 
                            WHERE id = ? AND usuario_id = ?
                        ");
                        $stmt->execute([$item['quantidade'], $item['produto_id'], $usuarioId]);
                    }
                    
                    // Fechar comandas
                    $stmt = $pdo->prepare("
                        UPDATE comandas 
                        SET status = 'fechado', data_fechamento = NOW() 
                        WHERE guardasol_id = ? AND status = 'aberto'
                    ");
                    $stmt->execute([$guardasolId]);
                    
                    // Liberar guarda-sol
                    $stmt = $pdo->prepare("
                        UPDATE guardasois 
                        SET status = 'vazio', 
                            cliente_nome = NULL, 
                            horario_ocupacao = NULL, 
                            total_consumido = 0.00 
                        WHERE id = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    
                    $pdo->commit();
                    
                    cleanJsonResponse(true, ['venda_id' => $pdo->lastInsertId()], 'Pagamento realizado e guarda-sol liberado');
                } catch (Exception $e) {
                    $pdo->rollback();
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'alterarSenha':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $senhaAtual = $_POST['senha_atual'] ?? '';
                    $novaSenha = $_POST['nova_senha'] ?? '';
                    
                    if (empty($senhaAtual) || empty($novaSenha)) {
                        cleanJsonResponse(false, null, 'Todos os campos são obrigatórios');
                    }
                    
                    if (strlen($novaSenha) < 6) {
                        cleanJsonResponse(false, null, 'A nova senha deve ter pelo menos 6 caracteres');
                    }
                    
                    // Buscar usuário
                    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
                    $stmt->execute([$usuarioId]);
                    $user = $stmt->fetch();
                    
                    if (!$user) {
                        cleanJsonResponse(false, null, 'Usuário não encontrado');
                    }
                    
                    // Verificar senha atual
                    if (!password_verify($senhaAtual, $user['senha'])) {
                        cleanJsonResponse(false, null, 'Senha atual incorreta');
                    }
                    
                    // Atualizar senha
                    $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt->execute([$novaSenhaHash, $usuarioId]);
                    
                    cleanJsonResponse(true, null, 'Senha alterada com sucesso');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'excluirConta':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $senha = $_POST['senha'] ?? '';
                    
                    if (empty($senha)) {
                        cleanJsonResponse(false, null, 'Senha é obrigatória para confirmar exclusão');
                    }
                    
                    // Buscar usuário
                    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
                    $stmt->execute([$usuarioId]);
                    $user = $stmt->fetch();
                    
                    if (!$user) {
                        cleanJsonResponse(false, null, 'Usuário não encontrado');
                    }
                    
                    // Verificar senha
                    if (!password_verify($senha, $user['senha'])) {
                        cleanJsonResponse(false, null, 'Senha incorreta');
                    }
                    
                    // Desativar conta (não excluir fisicamente para manter integridade dos dados)
                    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0, data_exclusao = NOW() WHERE id = ?");
                    $stmt->execute([$usuarioId]);
                    
                    // Limpar sessão
                    session_destroy();
                    
                    cleanJsonResponse(true, null, 'Conta excluída com sucesso');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'atualizarStatusPedido':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $pedidoId = $_POST['pedido_id'] ?? '';
                    $novoStatus = $_POST['novo_status'] ?? '';
                    
                    if (empty($pedidoId) || empty($novoStatus)) {
                        cleanJsonResponse(false, null, 'Dados inválidos');
                    }
                    
                    // Validar status
                    $statusValidos = ['pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado'];
                    if (!in_array($novoStatus, $statusValidos)) {
                        cleanJsonResponse(false, null, 'Status inválido');
                    }
                    
                    // Atualizar status
                    $stmt = $pdo->prepare("
                        UPDATE pedidos 
                        SET status = ?, data_atualizacao = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$novoStatus, $pedidoId]);
                    
                    cleanJsonResponse(true, ['status' => $novoStatus], 'Status atualizado com sucesso');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação inválida: ' . $action);
        }
    }
    
    // Processar requisições GET
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if (empty($action)) {
            cleanJsonResponse(false, null, 'Ação não especificada');
        }
        
        switch ($action) {
            case 'listar_produtos':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? $_GET['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                try {
                    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE usuario_id = ? AND ativo = 1 ORDER BY nome ASC");
                    $stmt->execute([$usuarioId]);
                    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, $produtos, 'Produtos listados com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao listar produtos', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao listar produtos: ' . $e->getMessage());
                }
                break;
                
            case 'getDashboardMetrics':
                session_start();
                $usuarioId = $_SESSION['usuario_id'] ?? null;
                
                if (!$usuarioId) {
                    cleanJsonResponse(false, null, 'Usuário não está logado');
                }
                
                try {
                    $hoje = date('Y-m-d');
                    $ontem = date('Y-m-d', strtotime('-1 day'));
                    $semanaPassada = date('Y-m-d', strtotime('-7 days'));
                    
                    // KPIs do dia
                    $stmt = $pdo->prepare("
                        SELECT 
                            COALESCE(SUM(total), 0) as faturamento_hoje,
                            COUNT(*) as num_atendimentos,
                            COALESCE(AVG(total), 0) as ticket_medio
                        FROM vendas 
                        WHERE DATE(data) = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$hoje, $usuarioId]);
                    $kpisHoje = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Comparação com ontem
                    $stmt = $pdo->prepare("
                        SELECT 
                            COALESCE(SUM(total), 0) as faturamento,
                            COUNT(*) as atendimentos,
                            COALESCE(AVG(total), 0) as ticket_medio
                        FROM vendas 
                        WHERE DATE(data) = ? AND usuario_id = ?
                    ");
                    $stmt->execute([$ontem, $usuarioId]);
                    $dadosOntem = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Comparação com semana passada
                    $stmt->execute([$semanaPassada, $usuarioId]);
                    $dadosSemanaPassada = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Calcular diferenças - Ontem
                    $diffFaturamento = $dadosOntem['faturamento'] > 0 
                        ? (($kpisHoje['faturamento_hoje'] - $dadosOntem['faturamento']) / $dadosOntem['faturamento']) * 100 
                        : 0;
                    $diffTicket = $dadosOntem['ticket_medio'] > 0 
                        ? (($kpisHoje['ticket_medio'] - $dadosOntem['ticket_medio']) / $dadosOntem['ticket_medio']) * 100 
                        : 0;
                    $diffAtendimentos = $kpisHoje['num_atendimentos'] - $dadosOntem['atendimentos'];
                    
                    // Calcular diferenças - Semana Passada
                    $diffFaturamentoSemana = $dadosSemanaPassada['faturamento'] > 0 
                        ? (($kpisHoje['faturamento_hoje'] - $dadosSemanaPassada['faturamento']) / $dadosSemanaPassada['faturamento']) * 100 
                        : 0;
                    $diffTicketSemana = $dadosSemanaPassada['ticket_medio'] > 0 
                        ? (($kpisHoje['ticket_medio'] - $dadosSemanaPassada['ticket_medio']) / $dadosSemanaPassada['ticket_medio']) * 100 
                        : 0;
                    $diffAtendimentosSemana = $kpisHoje['num_atendimentos'] - $dadosSemanaPassada['atendimentos'];
                    
                    // Vendas por hora
                    $stmt = $pdo->prepare("
                        SELECT 
                            HOUR(data) as hora,
                            COALESCE(SUM(total), 0) as total,
                            COUNT(*) as quantidade
                        FROM vendas 
                        WHERE DATE(data) = ? AND usuario_id = ?
                        GROUP BY HOUR(data)
                        ORDER BY hora
                    ");
                    $stmt->execute([$hoje, $usuarioId]);
                    $vendasPorHora = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Preencher todas as horas
                    $vendasPorHoraCompleto = [];
                    for ($h = 0; $h < 24; $h++) {
                        $encontrado = false;
                        foreach ($vendasPorHora as $venda) {
                            if ((int)$venda['hora'] === $h) {
                                $vendasPorHoraCompleto[] = $venda;
                                $encontrado = true;
                                break;
                            }
                        }
                        if (!$encontrado) {
                            $vendasPorHoraCompleto[] = [
                                'hora' => str_pad($h, 2, '0', STR_PAD_LEFT),
                                'total' => '0.00',
                                'quantidade' => 0
                            ];
                        }
                    }
                    
                    // Horário de pico
                    $horarioPico = null;
                    if (!empty($vendasPorHora)) {
                        $maxVendas = 0;
                        foreach ($vendasPorHora as $venda) {
                            if ((int)$venda['quantidade'] > $maxVendas) {
                                $maxVendas = (int)$venda['quantidade'];
                                $horarioPico = [
                                    'hora' => str_pad($venda['hora'], 2, '0', STR_PAD_LEFT),
                                    'quantidade' => $venda['quantidade']
                                ];
                            }
                        }
                    }
                    
                    // Top 5 produtos (buscar das comandas fechadas hoje)
                    $topProdutos = [];
                    try {
                        $stmt = $pdo->prepare("
                            SELECT * FROM comandas 
                            WHERE DATE(data_fechamento) = ? 
                            AND usuario_id = ? 
                            AND status = 'fechado'
                        ");
                        $stmt->execute([$hoje, $usuarioId]);
                        $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Processar produtos das comandas
                        $produtosAgrupados = [];
                        foreach ($comandas as $comanda) {
                            $produtos = json_decode($comanda['produtos'], true);
                            if ($produtos) {
                                foreach ($produtos as $prod) {
                                    $nome = $prod['nome'];
                                    if (!isset($produtosAgrupados[$nome])) {
                                        $produtosAgrupados[$nome] = [
                                            'nome' => $nome,
                                            'quantidade' => 0,
                                            'total' => 0
                                        ];
                                    }
                                    $produtosAgrupados[$nome]['quantidade'] += $prod['quantidade'];
                                    $produtosAgrupados[$nome]['total'] += $prod['subtotal'];
                                }
                            }
                        }
                        
                        // Ordenar por quantidade e pegar top 5
                        usort($produtosAgrupados, function($a, $b) {
                            return $b['quantidade'] - $a['quantidade'];
                        });
                        $topProdutos = array_slice($produtosAgrupados, 0, 5);
                    } catch (PDOException $e) {
                        $topProdutos = [];
                    }
                    
                    // Formas de pagamento (simplificado para estrutura atual da tabela)
                    $stmt = $pdo->prepare("
                        SELECT 
                            forma_pagamento,
                            SUM(total) as total
                        FROM vendas 
                        WHERE DATE(data) = ? AND usuario_id = ? AND forma_pagamento IS NOT NULL
                        GROUP BY forma_pagamento
                        ORDER BY total DESC
                    ");
                    $stmt->execute([$hoje, $usuarioId]);
                    $formasPagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $response = [
                        'faturamento_hoje' => floatval($kpisHoje['faturamento_hoje']),
                        'num_atendimentos' => intval($kpisHoje['num_atendimentos']),
                        'ticket_medio' => floatval($kpisHoje['ticket_medio']),
                        'comparacao_ontem_faturamento' => round($diffFaturamento, 1),
                        'comparacao_ontem_ticket' => round($diffTicket, 1),
                        'comparacao_ontem_atendimentos' => intval($diffAtendimentos),
                        'comparacao_semana_faturamento' => round($diffFaturamentoSemana, 1),
                        'comparacao_semana_ticket' => round($diffTicketSemana, 1),
                        'comparacao_semana_atendimentos' => intval($diffAtendimentosSemana),
                        'dados_ontem' => [
                            'faturamento' => floatval($dadosOntem['faturamento']),
                            'atendimentos' => intval($dadosOntem['atendimentos']),
                            'ticket_medio' => floatval($dadosOntem['ticket_medio']),
                            'diff_faturamento' => round($diffFaturamento, 1),
                            'diff_ticket' => round($diffTicket, 1),
                            'diff_atendimentos' => intval($diffAtendimentos)
                        ],
                        'dados_semana_passada' => [
                            'faturamento' => floatval($dadosSemanaPassada['faturamento']),
                            'atendimentos' => intval($dadosSemanaPassada['atendimentos']),
                            'ticket_medio' => floatval($dadosSemanaPassada['ticket_medio']),
                            'diff_faturamento' => round($diffFaturamentoSemana, 1),
                            'diff_ticket' => round($diffTicketSemana, 1),
                            'diff_atendimentos' => intval($diffAtendimentosSemana)
                        ],
                        'vendas_por_hora' => $vendasPorHoraCompleto,
                        'horario_pico' => $horarioPico,
                        'top_produtos' => $topProdutos,
                        'formas_pagamento' => $formasPagamento
                    ];
                    
                    cleanJsonResponse(true, $response, 'Métricas carregadas com sucesso');
                } catch (Exception $e) {
                    logError('Erro ao buscar métricas', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    cleanJsonResponse(false, null, 'Erro ao buscar métricas: ' . $e->getMessage());
                }
                break;
            
            case 'getDashboardFiado':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $hoje = date('Y-m-d');
                    $mesAtual = date('Y-m');
                    
                    // Total a receber
                    $stmt = $pdo->prepare("SELECT SUM(saldo_devedor) as total FROM clientes_fiado WHERE usuario_id = ? AND ativo = 1");
                    $stmt->execute([$usuarioId]);
                    $totalReceber = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                    
                    // Quantidade de clientes com dívida
                    $stmt = $pdo->prepare("SELECT COUNT(*) as qtd FROM clientes_fiado WHERE usuario_id = ? AND ativo = 1 AND saldo_devedor > 0");
                    $stmt->execute([$usuarioId]);
                    $qtdClientes = $stmt->fetch(PDO::FETCH_ASSOC)['qtd'] ?? 0;
                    
                    // Clientes inadimplentes
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as qtd,
                            COALESCE(SUM(saldo_devedor), 0) as valor
                        FROM clientes_fiado 
                        WHERE usuario_id = ? 
                        AND ativo = 1 
                        AND saldo_devedor > 0 
                        AND DATEDIFF(NOW(), ultima_compra) > 30
                    ");
                    $stmt->execute([$usuarioId]);
                    $inadimplentes = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Recebido hoje
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as qtd,
                            COALESCE(SUM(valor), 0) as total
                        FROM pagamentos_fiado pf
                        INNER JOIN clientes_fiado cf ON pf.cliente_id = cf.id
                        WHERE cf.usuario_id = ? 
                        AND DATE(pf.data_pagamento) = ?
                        AND pf.tipo = 'pagamento'
                    ");
                    $stmt->execute([$usuarioId, $hoje]);
                    $recebidoHoje = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Vendas fiadas no mês
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(*) as qtd,
                            COALESCE(SUM(valor), 0) as total
                        FROM pagamentos_fiado pf
                        INNER JOIN clientes_fiado cf ON pf.cliente_id = cf.id
                        WHERE cf.usuario_id = ? 
                        AND DATE_FORMAT(pf.data_pagamento, '%Y-%m') = ?
                        AND pf.tipo = 'compra'
                    ");
                    $stmt->execute([$usuarioId, $mesAtual]);
                    $vendasMes = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $response = [
                        'total_receber' => floatval($totalReceber),
                        'qtd_clientes' => intval($qtdClientes),
                        'clientes_inadimplentes' => intval($inadimplentes['qtd']),
                        'valor_inadimplente' => floatval($inadimplentes['valor']),
                        'recebido_hoje' => floatval($recebidoHoje['total']),
                        'qtd_pagamentos_hoje' => intval($recebidoHoje['qtd']),
                        'vendas_mes' => floatval($vendasMes['total']),
                        'qtd_vendas_mes' => intval($vendasMes['qtd'])
                    ];
                    
                    cleanJsonResponse(true, $response, 'Dashboard carregado');
                } catch (Exception $e) {
                    logError('Erro ao carregar dashboard fiado', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao carregar dashboard: ' . $e->getMessage());
                }
                break;
                
            case 'listarClientesFiado':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            cf.*,
                            DATEDIFF(NOW(), cf.ultima_compra) as dias_sem_comprar,
                            COALESCE(SUM(CASE WHEN pf.tipo = 'compra' THEN pf.valor ELSE 0 END), 0) as total_compras,
                            COALESCE(SUM(CASE WHEN pf.tipo = 'pagamento' THEN pf.valor ELSE 0 END), 0) as total_pago
                        FROM clientes_fiado cf
                        LEFT JOIN pagamentos_fiado pf ON cf.id = pf.cliente_id
                        WHERE cf.usuario_id = ? AND cf.ativo = 1
                        GROUP BY cf.id
                        ORDER BY cf.saldo_devedor DESC, cf.nome ASC
                    ");
                    $stmt->execute([$usuarioId]);
                    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, $clientes, 'Clientes carregados');
                } catch (Exception $e) {
                    logError('Erro ao listar clientes fiado', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao listar clientes: ' . $e->getMessage());
                }
                break;
                
            case 'obterHistoricoCliente':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    $clienteId = $_GET['cliente_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    if (!$clienteId) {
                        cleanJsonResponse(false, null, 'ID do cliente não informado');
                    }
                    
                    // Verificar se o cliente pertence ao usuário
                    $stmt = $pdo->prepare("SELECT * FROM clientes_fiado WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$clienteId, $usuarioId]);
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$cliente) {
                        cleanJsonResponse(false, null, 'Cliente não encontrado');
                    }
                    
                    // Buscar histórico
                    $stmt = $pdo->prepare("
                        SELECT * FROM pagamentos_fiado 
                        WHERE cliente_id = ? 
                        ORDER BY data_pagamento DESC
                        LIMIT 100
                    ");
                    $stmt->execute([$clienteId]);
                    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, [
                        'cliente' => $cliente,
                        'historico' => $historico
                    ], 'Histórico carregado');
                } catch (Exception $e) {
                    logError('Erro ao obter histórico cliente', ['error' => $e->getMessage()]);
                    cleanJsonResponse(false, null, 'Erro ao carregar histórico: ' . $e->getMessage());
                }
                break;
            
            // ========================================
            // ENDPOINTS GET - GUARDA-SÓIS
            // ========================================
            
            case 'listarGuardasois':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $stmt = $pdo->prepare("
                        SELECT * FROM view_resumo_guardasois 
                        WHERE usuario_id = ? 
                        ORDER BY 
                            CASE status 
                                WHEN 'aguardando_pagamento' THEN 1
                                WHEN 'ocupado' THEN 2
                                WHEN 'vazio' THEN 3
                            END,
                            CAST(numero AS UNSIGNED), numero
                    ");
                    $stmt->execute([$usuarioId]);
                    $guardasois = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, $guardasois, 'Guarda-sóis carregados');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'obterComandasGuardasol':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    $guardasolId = $_GET['guardasol_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    if (!$guardasolId) {
                        cleanJsonResponse(false, null, 'ID do guarda-sol não informado');
                    }
                    
                    // Buscar guarda-sol
                    $stmt = $pdo->prepare("SELECT * FROM guardasois WHERE id = ? AND usuario_id = ?");
                    $stmt->execute([$guardasolId, $usuarioId]);
                    $guardasol = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$guardasol) {
                        cleanJsonResponse(false, null, 'Guarda-sol não encontrado');
                    }
                    
                    // Buscar comandas abertas
                    $stmt = $pdo->prepare("
                        SELECT * FROM comandas 
                        WHERE guardasol_id = ? AND status = 'aberto' 
                        ORDER BY data_pedido ASC
                    ");
                    $stmt->execute([$guardasolId]);
                    $comandas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Retornar direto as comandas para simplificar o acesso
                    cleanJsonResponse(true, $comandas, 'Comandas carregadas');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'listarPedidos':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $filtroStatus = $_GET['status'] ?? '';
                    $filtroData = $_GET['data'] ?? '';
                    
                    $sql = "SELECT * FROM pedidos WHERE usuario_vendedor_id = ?";
                    $params = [$usuarioId];
                    
                    if (!empty($filtroStatus)) {
                        $sql .= " AND status = ?";
                        $params[] = $filtroStatus;
                    }
                    
                    if (!empty($filtroData)) {
                        $sql .= " AND DATE(data_pedido) = ?";
                        $params[] = $filtroData;
                    }
                    
                    $sql .= " ORDER BY data_pedido DESC";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, $pedidos, 'Pedidos carregados');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'listarVendasFinanceiro':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $filtroStatus = $_GET['status'] ?? '';
                    $filtroVendedor = $_GET['vendedor'] ?? '';
                    $filtroData = $_GET['data'] ?? '';
                    
                    // Query para buscar vendas individuais com detalhes
                    $sql = "
                        SELECT 
                            v.id,
                            v.data,
                            v.forma_pagamento,
                            v.total,
                            v.valor_pago,
                            v.troco,
                            v.desconto,
                            v.cliente_nome,
                            v.cliente_telefone,
                            v.observacoes,
                            v.status,
                            u.nome as vendedor_nome,
                            v.usuario_id
                        FROM vendas v
                        LEFT JOIN usuarios u ON v.usuario_id = u.id
                        WHERE v.usuario_id = ?
                    ";
                    $params = [$usuarioId];
                    
                    if (!empty($filtroStatus)) {
                        $sql .= " AND v.status = ?";
                        $params[] = $filtroStatus;
                    }
                    
                    if (!empty($filtroData)) {
                        $sql .= " AND DATE(v.data) = ?";
                        $params[] = $filtroData;
                    }
                    
                    $sql .= " ORDER BY v.data DESC";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Para cada venda, buscar os produtos vendidos
                    foreach ($vendas as &$venda) {
                        $stmt = $pdo->prepare("
                            SELECT 
                                iv.quantidade,
                                iv.preco_unitario,
                                iv.subtotal,
                                p.nome as produto_nome
                            FROM itens_venda iv
                            LEFT JOIN produtos p ON iv.produto_id = p.id
                            WHERE iv.venda_id = ?
                        ");
                        $stmt->execute([$venda['id']]);
                        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Formatar produtos para exibição
                        $produtos = [];
                        foreach ($itens as $item) {
                            $produtos[] = $item['quantidade'] . 'x ' . $item['produto_nome'];
                        }
                        $venda['produtos'] = $itens;
                        $venda['produtos_info'] = implode(', ', $produtos);
                        
                        // Tentar identificar número do guarda-sol (se houver nas observações)
                        $venda['numero_guardasol'] = null;
                        if (!empty($venda['observacoes'])) {
                            if (preg_match('/Guarda-sol\s+(\d+)/i', $venda['observacoes'], $matches)) {
                                $venda['numero_guardasol'] = $matches[1];
                            } elseif (preg_match('/GS(\d+)/i', $venda['observacoes'], $matches)) {
                                $venda['numero_guardasol'] = $matches[1];
                            }
                        }
                    }
                    
                    cleanJsonResponse(true, $vendas, 'Vendas carregadas');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'listarCodigosFuncionarios':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    // Buscar códigos
                    $stmt = $pdo->prepare("
                        SELECT 
                            cf.*,
                            COUNT(DISTINCT uc.usuario_id) as total_usos
                        FROM codigos_funcionarios cf
                        LEFT JOIN usos_codigo uc ON cf.id = uc.codigo_id
                        WHERE cf.admin_id = ?
                        GROUP BY cf.id
                        ORDER BY cf.data_criacao DESC
                    ");
                    $stmt->execute([$usuarioId]);
                    $codigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Para cada código, buscar funcionários que usaram esse código
                    foreach ($codigos as &$codigo) {
                        $stmt = $pdo->prepare("
                            SELECT u.id, u.nome, u.email, u.funcao_funcionario as funcao
                            FROM usuarios u
                            WHERE u.codigo_admin = ? AND u.tipo_usuario = 'funcionario'
                            ORDER BY u.nome
                        ");
                        $stmt->execute([$usuarioId]);
                        $codigo['funcionarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                    cleanJsonResponse(true, $codigos, 'Códigos carregados');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'estatisticasPerfil':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    $hoje = date('Y-m-d');
                    
                    // Total de vendas hoje
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vendas WHERE usuario_id = ? AND DATE(data) = ?");
                    $stmt->execute([$usuarioId, $hoje]);
                    $totalVendas = $stmt->fetch()['total'] ?? 0;
                    
                    // Faturamento hoje
                    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total FROM vendas WHERE usuario_id = ? AND DATE(data) = ?");
                    $stmt->execute([$usuarioId, $hoje]);
                    $faturamento = $stmt->fetch()['total'] ?? 0;
                    
                    // Produtos cadastrados
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE usuario_id = ?");
                    $stmt->execute([$usuarioId]);
                    $produtos = $stmt->fetch()['total'] ?? 0;
                    
                    cleanJsonResponse(true, [
                        'vendas_hoje' => $totalVendas,
                        'faturamento_hoje' => number_format($faturamento, 2, ',', '.'),
                        'produtos_cadastrados' => $produtos
                    ], 'Estatísticas carregadas');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
            
            case 'atividadeRecente':
                try {
                    session_start();
                    $usuarioId = $_SESSION['usuario_id'] ?? null;
                    
                    if (!$usuarioId) {
                        cleanJsonResponse(false, null, 'Usuário não autenticado');
                    }
                    
                    // Buscar últimas vendas
                    $stmt = $pdo->prepare("
                        SELECT 'venda' as tipo, data, total 
                        FROM vendas 
                        WHERE usuario_id = ? 
                        ORDER BY data DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$usuarioId]);
                    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    cleanJsonResponse(true, $atividades, 'Atividades carregadas');
                } catch (Exception $e) {
                    cleanJsonResponse(false, null, 'Erro: ' . $e->getMessage());
                }
                break;
                
            default:
                cleanJsonResponse(false, null, 'Ação GET inválida: ' . $action);
        }
    }
    
    cleanJsonResponse(false, null, 'Método não permitido');
    
} catch (Exception $e) {
    logError('Erro geral no actions.php', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    cleanJsonResponse(false, null, 'Erro interno: ' . $e->getMessage());
} catch (Error $e) {
    logError('Erro fatal no actions.php', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    cleanJsonResponse(false, null, 'Erro fatal: ' . $e->getMessage());
}
