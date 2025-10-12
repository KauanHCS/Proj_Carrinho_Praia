<?php
/**
 * EXPORT DATA - Sistema de Exportação de Dados
 * Exporta vendas e produtos em formato CSV
 */

// Configurações de segurança
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Definir PROJECT_ROOT
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

// Incluir arquivos necessários
require_once PROJECT_ROOT . '/config/database.php';

function exportarVendas($startDate = null, $endDate = null) {
    try {
        // Verificar usuário logado
        $usuarioId = $_SESSION['user_id'] ?? ($_GET['usuario_id'] ?? 1);
        
        $conn = getConnection();
        
        // Construir query com filtros de data e usuário
        $sql = "SELECT 
                    v.id as venda_id,
                    v.data,
                    v.forma_pagamento,
                    v.total as total_venda,
                    v.valor_pago,
                    v.troco,
                    p.nome as produto,
                    p.categoria,
                    p.preco_compra,
                    p.preco_venda,
                    iv.quantidade,
                    iv.preco_unitario,
                    (iv.quantidade * iv.preco_unitario) as subtotal,
                    ((iv.preco_unitario - COALESCE(p.preco_compra, 0)) * iv.quantidade) as lucro_item
                FROM vendas v 
                JOIN itens_venda iv ON v.id = iv.venda_id 
                JOIN produtos p ON iv.produto_id = p.id
                WHERE p.usuario_id = ?";
        
        $params = [$usuarioId];
        $types = "i";
        
        if ($startDate && $endDate) {
            $sql .= " AND DATE(v.data) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= "ss";
        } elseif ($startDate) {
            $sql .= " AND DATE(v.data) >= ?";
            $params[] = $startDate;
            $types .= "s";
        } elseif ($endDate) {
            $sql .= " AND DATE(v.data) <= ?";
            $params[] = $endDate;
            $types .= "s";
        }
        
        $sql .= " ORDER BY v.data DESC, v.id DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Definir headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vendas_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 no Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // Cabeçalhos CSV
        fputcsv($output, [
            'ID Venda',
            'Data',
            'Forma de Pagamento',
            'Total da Venda',
            'Valor Pago',
            'Troco',
            'Produto',
            'Categoria',
            'Preço de Compra',
            'Preço de Venda',
            'Quantidade',
            'Preço Unitário',
            'Subtotal',
            'Lucro do Item'
        ], ';');
        
        // Dados
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['venda_id'],
                date('d/m/Y H:i:s', strtotime($row['data'])),
                $row['forma_pagamento'],
                'R$ ' . number_format($row['total_venda'], 2, ',', '.'),
                'R$ ' . number_format($row['valor_pago'], 2, ',', '.'),
                'R$ ' . number_format($row['troco'], 2, ',', '.'),
                $row['produto'],
                $row['categoria'],
                'R$ ' . number_format($row['preco_compra'] ?? 0, 2, ',', '.'),
                'R$ ' . number_format($row['preco_venda'], 2, ',', '.'),
                $row['quantidade'],
                'R$ ' . number_format($row['preco_unitario'], 2, ',', '.'),
                'R$ ' . number_format($row['subtotal'], 2, ',', '.'),
                'R$ ' . number_format($row['lucro_item'], 2, ',', '.')
            ], ';');
        }
        
        fclose($output);
        $stmt->close();
        $conn->close();
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        die('Erro ao exportar vendas: ' . $e->getMessage());
    }
}

function exportarProdutos() {
    try {
        // Verificar usuário logado
        $usuarioId = $_SESSION['user_id'] ?? ($_GET['usuario_id'] ?? 1);
        
        $conn = getConnection();
        
        $sql = "SELECT 
                    p.id,
                    p.nome,
                    p.categoria,
                    p.preco_compra,
                    p.preco_venda,
                    p.quantidade,
                    p.limite_minimo,
                    p.validade,
                    p.observacoes,
                    CASE 
                        WHEN p.preco_compra > 0 THEN 
                            ROUND(((p.preco_venda - p.preco_compra) / p.preco_compra) * 100, 2)
                        ELSE 0
                    END as margem_lucro,
                    CASE 
                        WHEN p.quantidade <= p.limite_minimo THEN 'SIM'
                        ELSE 'NÃO'
                    END as estoque_baixo,
                    COALESCE(SUM(iv.quantidade), 0) as total_vendido
                FROM produtos p
                LEFT JOIN itens_venda iv ON p.id = iv.produto_id
                WHERE p.usuario_id = ?
                GROUP BY p.id
                ORDER BY p.nome";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Definir headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="produtos_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 no Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // Cabeçalhos CSV
        fputcsv($output, [
            'ID',
            'Nome do Produto',
            'Categoria',
            'Preço de Compra',
            'Preço de Venda',
            'Quantidade em Estoque',
            'Limite Mínimo',
            'Validade',
            'Observações',
            'Margem de Lucro (%)',
            'Estoque Baixo',
            'Total Vendido'
        ], ';');
        
        // Dados
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['nome'],
                $row['categoria'],
                'R$ ' . number_format($row['preco_compra'] ?? 0, 2, ',', '.'),
                'R$ ' . number_format($row['preco_venda'], 2, ',', '.'),
                $row['quantidade'],
                $row['limite_minimo'],
                $row['validade'] ? date('d/m/Y', strtotime($row['validade'])) : '',
                $row['observacoes'],
                $row['margem_lucro'] . '%',
                $row['estoque_baixo'],
                $row['total_vendido']
            ], ';');
        }
        
        fclose($output);
        $stmt->close();
        $conn->close();
        exit;
        
    } catch (Exception $e) {
        http_response_code(500);
        die('Erro ao exportar produtos: ' . $e->getMessage());
    }
}

// Processar requisição
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'vendas':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        exportarVendas($startDate, $endDate);
        break;
        
    case 'produtos':
        exportarProdutos();
        break;
        
    default:
        http_response_code(400);
        die('Ação inválida');
}
?>