<?php
session_start();
require_once 'config/database.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? '';

function outputCSV($filename, $data, $headers) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Cabeçalhos
    fputcsv($output, $headers, ';');
    
    // Dados
    foreach ($data as $row) {
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}

try {
    $conn = getConnection();
    
    switch ($action) {
        case 'vendas':
            exportarVendas($conn, $usuarioId);
            break;
        case 'produtos':
            exportarProdutos($conn, $usuarioId);
            break;
        default:
            throw new Exception('Ação inválida');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function exportarVendas($conn, $usuarioId) {
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Primeiro dia do mês atual
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Hoje
    
    $sql = "SELECT 
                v.id as venda_id,
                v.data,
                v.forma_pagamento,
                v.total as total_venda,
                p.nome as produto,
                p.categoria,
                iv.quantidade,
                iv.preco_unitario,
                (iv.quantidade * iv.preco_unitario) as subtotal
            FROM vendas v
            JOIN itens_venda iv ON v.id = iv.venda_id
            JOIN produtos p ON iv.produto_id = p.id
            WHERE p.usuario_id = ? 
            AND DATE(v.data) BETWEEN ? AND ?
            ORDER BY v.data DESC, v.id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $usuarioId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $headers = [
        'ID Venda',
        'Data',
        'Forma Pagamento',
        'Total Venda',
        'Produto',
        'Categoria',
        'Quantidade',
        'Preço Unitário',
        'Subtotal'
    ];
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['venda_id'],
            date('d/m/Y H:i:s', strtotime($row['data'])),
            ucfirst($row['forma_pagamento']),
            'R$ ' . number_format($row['total_venda'], 2, ',', '.'),
            $row['produto'],
            ucfirst($row['categoria']),
            $row['quantidade'],
            'R$ ' . number_format($row['preco_unitario'], 2, ',', '.'),
            'R$ ' . number_format($row['subtotal'], 2, ',', '.')
        ];
    }
    
    $filename = 'vendas_' . date('Y-m-d_H-i-s') . '.csv';
    outputCSV($filename, $data, $headers);
}

function exportarProdutos($conn, $usuarioId) {
    $sql = "SELECT 
                id,
                nome,
                categoria,
                preco,
                quantidade,
                limite_minimo,
                validade,
                data_cadastro
            FROM produtos 
            WHERE usuario_id = ?
            ORDER BY nome";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $headers = [
        'ID',
        'Nome',
        'Categoria',
        'Preço',
        'Quantidade',
        'Limite Mínimo',
        'Validade',
        'Data Cadastro'
    ];
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['id'],
            $row['nome'],
            ucfirst($row['categoria']),
            'R$ ' . number_format($row['preco'], 2, ',', '.'),
            $row['quantidade'],
            $row['limite_minimo'],
            $row['validade'] ? date('d/m/Y', strtotime($row['validade'])) : 'Sem validade',
            date('d/m/Y H:i:s', strtotime($row['data_cadastro']))
        ];
    }
    
    $filename = 'produtos_' . date('Y-m-d_H-i-s') . '.csv';
    outputCSV($filename, $data, $headers);
}
?>
