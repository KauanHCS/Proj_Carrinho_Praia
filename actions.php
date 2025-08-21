<?php
// actions.php

require_once 'config/database.php';

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

// Verificar ação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
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

// Funções de ação

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
            // Verificar estoque
            $stmt = $conn->prepare("SELECT quantidade FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $produto = $result->fetch_assoc();
            
            if ($produto['quantidade'] < $item['quantidade']) {
                throw new Exception("Estoque insuficiente para {$item['nome']}");
            }
            
            // Inserir item da venda
            $stmt = $conn->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $vendaId, $item['id'], $item['quantidade'], $item['preco']);
            $stmt->execute();
            
            // Atualizar estoque
            $stmt = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantidade'], $item['id']);
            $stmt->execute();
            
            // Registrar movimentação
            $stmt = $conn->prepare("INSERT INTO movimentacoes (data, produto_id, tipo, quantidade, descricao) VALUES (NOW(), ?, 'saida', ?, 'Venda')");
            $stmt->bind_param("ii", $item['id'], $item['quantidade']);
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
        
        // Atualizar estoque
        $stmt = $conn->prepare("UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantidade, $produtoId);
        $stmt->execute();
        
        // Registrar movimentação
        $stmt = $conn->prepare("INSERT INTO movimentacoes (data, produto_id, tipo, quantidade, descricao) VALUES (NOW(), ?, 'entrada', ?, 'Reabastecimento')");
        $stmt->bind_param("ii", $produtoId, $quantidade);
        $stmt->execute();
        
        jsonResponse(true, [
            'produto_id' => $produtoId,
            'nome' => $produto['nome'],
            'quantidade_antiga' => $produto['quantidade'],
            'quantidade_nova' => $produto['quantidade'] + $quantidade
        ], 'Estoque reabastecido com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function salvarProduto() {
    $conn = getConnection();
    
    try {
        $nome = $_POST['nome'];
        $categoria = $_POST['categoria'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];
        $limiteMinimo = $_POST['limite_minimo'];
        $validade = $_POST['validade'] ?: null;
        $observacoes = $_POST['observacoes'] ?: '';
        
        // Verificar se o produto já existe
        $stmt = $conn->prepare("SELECT id FROM produtos WHERE nome = ?");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            jsonResponse(false, null, 'Produto com este nome já existe');
        }
        
        // Inserir produto
        $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, quantidade, categoria, limite_minimo, validade, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdissis", $nome, $preco, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes);
        $stmt->execute();
        $produtoId = $conn->insert_id;
        
        // Registrar movimentação de entrada
        $stmt = $conn->prepare("INSERT INTO movimentacoes (data, produto_id, tipo, quantidade, descricao) VALUES (NOW(), ?, 'entrada', ?, 'Cadastro inicial')");
        $stmt->bind_param("ii", $produtoId, $quantidade);
        $stmt->execute();
        
        jsonResponse(true, ['produto_id' => $produtoId], 'Produto cadastrado com sucesso');
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function atualizarProduto() {
    $conn = getConnection();
    
    try {
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $categoria = $_POST['categoria'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];
        $limiteMinimo = $_POST['limite_minimo'];
        $validade = $_POST['validade'] ?: null;
        $observacoes = $_POST['observacoes'] ?: '';
        
        // Verificar se o produto existe
        $stmt = $conn->prepare("SELECT id FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Produto não encontrado');
        }
        
        // Atualizar produto
        $stmt = $conn->prepare("UPDATE produtos SET nome = ?, preco = ?, quantidade = ?, categoria = ?, limite_minimo = ?, validade = ?, observacoes = ? WHERE id = ?");
        $stmt->bind_param("sdissisi", $nome, $preco, $quantidade, $categoria, $limiteMinimo, $validade, $observacoes, $id);
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
        $id = $_POST['id'];
        
        // Verificar se o produto existe
        $stmt = $conn->prepare("SELECT id, nome FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, null, 'Produto não encontrado');
        }
        
        $produto = $result->fetch_assoc();
        
        // Verificar se o produto tem vendas
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM itens_venda WHERE produto_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            jsonResponse(false, null, 'Não é possível excluir um produto que já teve vendas');
        }
        
        // Excluir produto
        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
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
        $stmt = $conn->prepare("SELECT id, nome, quantidade, limite_minimo FROM produtos WHERE quantidade <= limite_minimo AND quantidade > 0 ORDER BY quantidade ASC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            jsonResponse(true, ['produto' => $produto]);
        } else {
            jsonResponse(false);
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function getProduto() {
    $conn = getConnection();
    
    try {
        $id = $_GET['id'];
        
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            jsonResponse(true, ['produto' => $produto]);
        } else {
            jsonResponse(false, null, 'Produto não encontrado');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}

function getProdutosMaisVendidos() {
    $conn = getConnection();
    
    try {
        $sql = "SELECT p.nome, SUM(iv.quantidade) as total_vendido 
                FROM itens_venda iv 
                JOIN produtos p ON iv.produto_id = p.id 
                JOIN vendas v ON iv.venda_id = v.id 
                WHERE DATE(v.data) = CURDATE() 
                GROUP BY p.id, p.nome 
                ORDER BY total_vendido DESC 
                LIMIT 5";
        
        $result = $conn->query($sql);
        $produtos = [];
        
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
        
        jsonResponse(true, ['produtos' => $produtos]);
        
    } catch (Exception $e) {
        jsonResponse(false, null, $e->getMessage());
    }
    
    $conn->close();
}
?>