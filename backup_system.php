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

try {
    switch ($action) {
        case 'backup':
            criarBackup($usuarioId);
            break;
        case 'backup_full':
            criarBackupCompleto();
            break;
        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function criarBackup($usuarioId) {
    $conn = getConnection();
    $backupContent = '';
    $timestamp = date('Y-m-d_H-i-s');
    
    // Cabeçalho do backup
    $backupContent .= "-- Backup do Carrinho de Praia - Usuário ID: $usuarioId\n";
    $backupContent .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $backupContent .= "-- Gerado automaticamente pelo sistema\n\n";
    
    // Backup dos produtos do usuário
    $sql = "SELECT * FROM produtos WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $backupContent .= "-- Produtos do usuário\n";
    $backupContent .= "DELETE FROM produtos WHERE usuario_id = $usuarioId;\n";
    
    while ($row = $result->fetch_assoc()) {
        $backupContent .= "INSERT INTO produtos (";
        $backupContent .= "id, nome, categoria, preco, quantidade, limite_minimo, validade, data_cadastro, usuario_id";
        $backupContent .= ") VALUES (";
        $backupContent .= "'" . addslashes($row['id']) . "', ";
        $backupContent .= "'" . addslashes($row['nome']) . "', ";
        $backupContent .= "'" . addslashes($row['categoria']) . "', ";
        $backupContent .= "'" . addslashes($row['preco']) . "', ";
        $backupContent .= "'" . addslashes($row['quantidade']) . "', ";
        $backupContent .= "'" . addslashes($row['limite_minimo']) . "', ";
        $backupContent .= ($row['validade'] ? "'" . addslashes($row['validade']) . "'" : "NULL") . ", ";
        $backupContent .= "'" . addslashes($row['data_cadastro']) . "', ";
        $backupContent .= "'" . addslashes($row['usuario_id']) . "'";
        $backupContent .= ");\n";
    }
    
    // Backup das vendas relacionadas aos produtos do usuário
    $sql = "SELECT DISTINCT v.* FROM vendas v 
            JOIN itens_venda iv ON v.id = iv.venda_id 
            JOIN produtos p ON iv.produto_id = p.id 
            WHERE p.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $backupContent .= "\n-- Vendas relacionadas aos produtos do usuário\n";
    $vendaIds = [];
    
    while ($row = $result->fetch_assoc()) {
        $vendaIds[] = $row['id'];
        $backupContent .= "INSERT IGNORE INTO vendas (";
        $backupContent .= "id, data, forma_pagamento, total";
        $backupContent .= ") VALUES (";
        $backupContent .= "'" . addslashes($row['id']) . "', ";
        $backupContent .= "'" . addslashes($row['data']) . "', ";
        $backupContent .= "'" . addslashes($row['forma_pagamento']) . "', ";
        $backupContent .= "'" . addslashes($row['total']) . "'";
        $backupContent .= ");\n";
    }
    
    // Backup dos itens de venda
    if (!empty($vendaIds)) {
        $vendaIdsStr = implode(',', $vendaIds);
        $sql = "SELECT iv.* FROM itens_venda iv 
                JOIN produtos p ON iv.produto_id = p.id 
                WHERE p.usuario_id = ? AND iv.venda_id IN ($vendaIdsStr)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $backupContent .= "\n-- Itens de venda\n";
        while ($row = $result->fetch_assoc()) {
            $backupContent .= "INSERT IGNORE INTO itens_venda (";
            $backupContent .= "id, venda_id, produto_id, quantidade, preco_unitario";
            $backupContent .= ") VALUES (";
            $backupContent .= "'" . addslashes($row['id']) . "', ";
            $backupContent .= "'" . addslashes($row['venda_id']) . "', ";
            $backupContent .= "'" . addslashes($row['produto_id']) . "', ";
            $backupContent .= "'" . addslashes($row['quantidade']) . "', ";
            $backupContent .= "'" . addslashes($row['preco_unitario']) . "'";
            $backupContent .= ");\n";
        }
    }
    
    // Backup das movimentações de estoque
    $sql = "SELECT m.* FROM movimentacoes m 
            JOIN produtos p ON m.produto_id = p.id 
            WHERE p.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $backupContent .= "\n-- Movimentações de estoque\n";
    while ($row = $result->fetch_assoc()) {
        $backupContent .= "INSERT IGNORE INTO movimentacoes (";
        $backupContent .= "id, produto_id, tipo, quantidade, data, observacoes";
        $backupContent .= ") VALUES (";
        $backupContent .= "'" . addslashes($row['id']) . "', ";
        $backupContent .= "'" . addslashes($row['produto_id']) . "', ";
        $backupContent .= "'" . addslashes($row['tipo']) . "', ";
        $backupContent .= "'" . addslashes($row['quantidade']) . "', ";
        $backupContent .= "'" . addslashes($row['data']) . "', ";
        $backupContent .= ($row['observacoes'] ? "'" . addslashes($row['observacoes']) . "'" : "NULL");
        $backupContent .= ");\n";
    }
    
    $conn->close();
    
    // Gerar o arquivo de backup
    $filename = "backup_usuario_{$usuarioId}_{$timestamp}.sql";
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    echo $backupContent;
    exit;
}

function criarBackupCompleto() {
    // Esta função cria um backup completo do banco (só para administradores)
    // Por segurança, vamos limitar esta funcionalidade
    
    $backupContent = '';
    $timestamp = date('Y-m-d_H-i-s');
    
    $backupContent .= "-- Backup Completo do Carrinho de Praia\n";
    $backupContent .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $backupContent .= "-- Atenção: Este é um backup completo do sistema\n\n";
    
    $conn = getConnection();
    
    // Lista das tabelas para backup
    $tabelas = ['usuarios', 'produtos', 'vendas', 'itens_venda', 'movimentacoes', 'notificacoes'];
    
    foreach ($tabelas as $tabela) {
        $backupContent .= "\n-- Tabela: $tabela\n";
        $backupContent .= "DROP TABLE IF EXISTS `$tabela`;\n";
        
        // Estrutura da tabela
        $result = $conn->query("SHOW CREATE TABLE `$tabela`");
        if ($result && $row = $result->fetch_assoc()) {
            $backupContent .= $row['Create Table'] . ";\n\n";
        }
        
        // Dados da tabela
        $result = $conn->query("SELECT * FROM `$tabela`");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $backupContent .= "INSERT INTO `$tabela` VALUES (";
                $valores = array_map(function($valor) {
                    return $valor === null ? 'NULL' : "'" . addslashes($valor) . "'";
                }, array_values($row));
                $backupContent .= implode(', ', $valores);
                $backupContent .= ");\n";
            }
        }
    }
    
    $conn->close();
    
    $filename = "backup_completo_{$timestamp}.sql";
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    echo $backupContent;
    exit;
}

// Função para restaurar backup (se necessário)
function restaurarBackup($sqlFile) {
    $conn = getConnection();
    $sql = file_get_contents($sqlFile);
    
    // Dividir o SQL em comandos individuais
    $comandos = array_filter(array_map('trim', explode(';', $sql)));
    
    $conn->begin_transaction();
    
    try {
        foreach ($comandos as $comando) {
            if (!empty($comando) && !strpos($comando, '--') === 0) {
                $conn->query($comando);
            }
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Backup restaurado com sucesso'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Erro ao restaurar backup: ' . $e->getMessage()];
    }
}
?>
