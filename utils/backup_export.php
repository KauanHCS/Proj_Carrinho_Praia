<?php
// Backup and Export Utilities
require_once '../config/database.php';

class BackupExport {
    
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    // Export sales data to CSV
    public function exportSalesCSV($startDate = null, $endDate = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($startDate && $endDate) {
                $whereClause = 'WHERE DATE(v.data) BETWEEN ? AND ?';
                $params = [$startDate, $endDate];
            } elseif ($startDate) {
                $whereClause = 'WHERE DATE(v.data) >= ?';
                $params = [$startDate];
            } elseif ($endDate) {
                $whereClause = 'WHERE DATE(v.data) <= ?';
                $params = [$endDate];
            }
            
            $sql = "SELECT 
                        v.id as venda_id,
                        v.data,
                        v.forma_pagamento,
                        v.total,
                        v.valor_pago,
                        v.troco,
                        p.nome as produto,
                        p.categoria,
                        iv.quantidade,
                        iv.preco_unitario,
                        iv.subtotal
                    FROM vendas v
                    JOIN itens_venda iv ON v.id = iv.venda_id
                    JOIN produtos p ON iv.produto_id = p.id
                    {$whereClause}
                    ORDER BY v.data DESC";
            
            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Generate CSV
            $filename = 'vendas_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fputs($output, "\xEF\xBB\xBF");
            
            // CSV Headers
            fputcsv($output, [
                'ID Venda', 'Data/Hora', 'Forma Pagamento', 'Total Venda', 
                'Valor Pago', 'Troco', 'Produto', 'Categoria', 
                'Quantidade', 'Preço Unitário', 'Subtotal'
            ], ';');
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['venda_id'],
                    $row['data'],
                    $row['forma_pagamento'],
                    number_format($row['total'], 2, ',', '.'),
                    number_format($row['valor_pago'] ?? 0, 2, ',', '.'),
                    number_format($row['troco'] ?? 0, 2, ',', '.'),
                    $row['produto'],
                    $row['categoria'],
                    $row['quantidade'],
                    number_format($row['preco_unitario'], 2, ',', '.'),
                    number_format($row['subtotal'], 2, ',', '.')
                ], ';');
            }
            
            fclose($output);
            return true;
            
        } catch (Exception $e) {
            error_log('Erro ao exportar CSV: ' . $e->getMessage());
            return false;
        }
    }
    
    // Export products data to CSV
    public function exportProductsCSV() {
        try {
            $sql = "SELECT 
                        id, nome, categoria, preco, quantidade, 
                        limite_minimo, validade, observacoes, data_cadastro
                    FROM produtos 
                    ORDER BY nome";
            
            $result = $this->conn->query($sql);
            
            $filename = 'produtos_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fputs($output, "\xEF\xBB\xBF");
            
            // CSV Headers
            fputcsv($output, [
                'ID', 'Nome', 'Categoria', 'Preço', 'Quantidade', 
                'Limite Mínimo', 'Validade', 'Observações', 'Data Cadastro'
            ], ';');
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    $row['id'],
                    $row['nome'],
                    $row['categoria'],
                    number_format($row['preco'], 2, ',', '.'),
                    $row['quantidade'],
                    $row['limite_minimo'],
                    $row['validade'] ?: '',
                    $row['observacoes'],
                    $row['data_cadastro']
                ], ';');
            }
            
            fclose($output);
            return true;
            
        } catch (Exception $e) {
            error_log('Erro ao exportar produtos CSV: ' . $e->getMessage());
            return false;
        }
    }
    
    // Create database backup (SQL dump)
    public function createDatabaseBackup() {
        try {
            $database = 'sistema_carrinho'; // From config/database.php
            $filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Get database structure and data
            $backup = $this->generateSQLDump();
            
            if ($backup === false) {
                return ['success' => false, 'message' => 'Erro ao gerar backup'];
            }
            
            // Send as download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Length: ' . strlen($backup));
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo $backup;
            return ['success' => true, 'filename' => $filename];
            
        } catch (Exception $e) {
            error_log('Erro ao criar backup: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function generateSQLDump() {
        try {
            $output = '';
            $output .= "-- Backup gerado em: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Sistema: Carrinho de Praia\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            // Get all tables
            $tables = ['usuarios', 'produtos', 'vendas', 'itens_venda', 'movimentacoes', 'configuracoes'];
            
            foreach ($tables as $table) {
                // Table structure
                $createTableResult = $this->conn->query("SHOW CREATE TABLE `$table`");
                if ($createTableResult && $row = $createTableResult->fetch_array()) {
                    $output .= "-- Estrutura da tabela `$table`\n";
                    $output .= "DROP TABLE IF EXISTS `$table`;\n";
                    $output .= $row[1] . ";\n\n";
                }
                
                // Table data
                $result = $this->conn->query("SELECT * FROM `$table`");
                if ($result && $result->num_rows > 0) {
                    $output .= "-- Dados da tabela `$table`\n";
                    
                    while ($row = $result->fetch_assoc()) {
                        $columns = array_keys($row);
                        $values = array_map(function($value) {
                            return $value === null ? 'NULL' : "'" . $this->conn->real_escape_string($value) . "'";
                        }, array_values($row));
                        
                        $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            return $output;
            
        } catch (Exception $e) {
            error_log('Erro ao gerar SQL dump: ' . $e->getMessage());
            return false;
        }
    }
    
    // Generate sales report data
    public function getSalesReport($startDate = null, $endDate = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($startDate && $endDate) {
                $whereClause = 'WHERE DATE(v.data) BETWEEN ? AND ?';
                $params = [$startDate, $endDate];
            }
            
            // Sales summary
            $summarySQL = "SELECT 
                            COUNT(*) as total_vendas,
                            SUM(total) as receita_total,
                            AVG(total) as ticket_medio,
                            forma_pagamento,
                            COUNT(*) as vendas_por_tipo
                          FROM vendas v
                          {$whereClause}
                          GROUP BY forma_pagamento";
            
            $stmt = $this->conn->prepare($summarySQL);
            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            $stmt->execute();
            $summaryResult = $stmt->get_result();
            
            $summary = [];
            while ($row = $summaryResult->fetch_assoc()) {
                $summary[] = $row;
            }
            
            // Top products
            $productsSQL = "SELECT 
                              p.nome,
                              p.categoria,
                              SUM(iv.quantidade) as quantidade_vendida,
                              SUM(iv.subtotal) as receita_produto
                            FROM itens_venda iv
                            JOIN produtos p ON iv.produto_id = p.id
                            JOIN vendas v ON iv.venda_id = v.id
                            {$whereClause}
                            GROUP BY p.id, p.nome, p.categoria
                            ORDER BY quantidade_vendida DESC
                            LIMIT 10";
            
            $stmt = $this->conn->prepare($productsSQL);
            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }
            $stmt->execute();
            $productsResult = $stmt->get_result();
            
            $topProducts = [];
            while ($row = $productsResult->fetch_assoc()) {
                $topProducts[] = $row;
            }
            
            return [
                'success' => true,
                'summary' => $summary,
                'top_products' => $topProducts,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ];
            
        } catch (Exception $e) {
            error_log('Erro ao gerar relatório: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $backup = new BackupExport();
    
    switch ($action) {
        case 'export_sales':
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $backup->exportSalesCSV($startDate, $endDate);
            break;
            
        case 'export_products':
            $backup->exportProductsCSV();
            break;
            
        case 'backup_database':
            $result = $backup->createDatabaseBackup();
            if (!$result['success']) {
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode($result);
            }
            break;
            
        case 'sales_report':
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            $report = $backup->getSalesReport($startDate, $endDate);
            header('Content-Type: application/json');
            echo json_encode($report);
            break;
            
        default:
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>
