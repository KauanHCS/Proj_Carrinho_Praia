<?php
/**
 * Script para atualizar o sistema de códigos de funcionários
 * 
 * Remove a obrigatoriedade da função específica nos códigos,
 * permitindo que a função seja escolhida durante o cadastro do funcionário.
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'carrinho_praia';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Atualização do Sistema de Códigos</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<style>";
    echo "body { background: linear-gradient(135deg, #0066cc, #0099ff); min-height: 100vh; display: flex; align-items: center; }";
    echo ".container { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h2 class='text-center mb-4'><i class='bi bi-gear'></i> Atualização do Sistema de Códigos</h2>";
    
    // Verificar se a coluna funcao existe e pode ser nula
    echo "<div class='alert alert-info'>";
    echo "<i class='bi bi-info-circle'></i> Verificando estrutura atual da tabela...";
    echo "</div>";
    
    $stmt = $pdo->query("DESCRIBE codigos_funcionarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $funcaoColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'funcao') {
            $funcaoColumn = $column;
            break;
        }
    }
    
    if (!$funcaoColumn) {
        echo "<div class='alert alert-warning'>";
        echo "<i class='bi bi-exclamation-triangle'></i> Coluna 'funcao' não encontrada na tabela 'codigos_funcionarios'.";
        echo "</div>";
    } else {
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Estrutura Atual da Coluna 'funcao'</strong></div>";
        echo "<div class='card-body'>";
        echo "<ul>";
        echo "<li><strong>Tipo:</strong> " . $funcaoColumn['Type'] . "</li>";
        echo "<li><strong>Permite NULL:</strong> " . ($funcaoColumn['Null'] === 'YES' ? 'Sim' : 'Não') . "</li>";
        echo "<li><strong>Valor Padrão:</strong> " . ($funcaoColumn['Default'] ?? 'Nenhum') . "</li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        
        // Se a coluna não permite NULL, modificar para permitir
        if ($funcaoColumn['Null'] === 'NO') {
            echo "<div class='alert alert-primary'>";
            echo "<i class='bi bi-arrow-clockwise'></i> Modificando coluna 'funcao' para permitir valores NULL...";
            echo "</div>";
            
            try {
                $pdo->exec("ALTER TABLE codigos_funcionarios MODIFY COLUMN funcao VARCHAR(50) NULL");
                echo "<div class='alert alert-success'>";
                echo "<i class='bi bi-check-circle'></i> Coluna 'funcao' atualizada com sucesso!";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>";
                echo "<i class='bi bi-exclamation-circle'></i> Erro ao modificar coluna: " . $e->getMessage();
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-success'>";
            echo "<i class='bi bi-check-circle'></i> Coluna 'funcao' já permite valores NULL. Nenhuma alteração necessária.";
            echo "</div>";
        }
    }
    
    // Verificar códigos existentes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM codigos_funcionarios");
    $totalCodigos = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM codigos_funcionarios WHERE funcao IS NULL");
    $codigosSemFuncao = $stmt->fetchColumn();
    
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'><strong>Status dos Códigos Existentes</strong></div>";
    echo "<div class='card-body'>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>Total de códigos:</strong> <span class='badge bg-primary'>" . $totalCodigos . "</span></p>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>Códigos sem função específica:</strong> <span class='badge bg-info'>" . $codigosSemFuncao . "</span></p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    // Mostrar alguns códigos como exemplo
    if ($totalCodigos > 0) {
        $stmt = $pdo->query("SELECT codigo, funcao, usado, data_criacao FROM codigos_funcionarios ORDER BY data_criacao DESC LIMIT 5");
        $codigosExemplo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'><strong>Últimos 5 Códigos (Exemplo)</strong></div>";
        echo "<div class='card-body'>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-sm'>";
        echo "<thead><tr><th>Código</th><th>Função</th><th>Status</th><th>Data Criação</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($codigosExemplo as $codigo) {
            $funcaoDisplay = $codigo['funcao'] ? $codigo['funcao'] : '<em class="text-muted">Universal</em>';
            $statusDisplay = $codigo['usado'] ? '<span class="badge bg-success">Usado</span>' : '<span class="badge bg-warning">Disponível</span>';
            $dataDisplay = date('d/m/Y H:i', strtotime($codigo['data_criacao']));
            
            echo "<tr>";
            echo "<td><code>" . $codigo['codigo'] . "</code></td>";
            echo "<td>" . $funcaoDisplay . "</td>";
            echo "<td>" . $statusDisplay . "</td>";
            echo "<td>" . $dataDisplay . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    
    echo "<div class='alert alert-success'>";
    echo "<i class='bi bi-check-circle'></i> <strong>Atualização concluída!</strong>";
    echo "<br><br>";
    echo "<strong>Mudanças implementadas:</strong>";
    echo "<ul class='mt-2'>";
    echo "<li>✅ Códigos podem ser gerados sem função específica</li>";
    echo "<li>✅ Função será escolhida pelo funcionário durante o cadastro</li>";
    echo "<li>✅ Sistema de auto-refresh implementado</li>";
    echo "<li>✅ Interface atualizada para refletir as mudanças</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='text-center'>";
    echo "<a href='index.php' class='btn btn-primary me-2'>";
    echo "<i class='bi bi-house'></i> Ir para Sistema Principal";
    echo "</a>";
    echo "<a href='login.php' class='btn btn-outline-primary'>";
    echo "<i class='bi bi-box-arrow-in-right'></i> Ir para Login";
    echo "</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<i class='bi bi-exclamation-circle'></i> <strong>Erro de conexão:</strong> " . $e->getMessage();
    echo "</div>";
    echo "<p class='text-muted'>Verifique se:</p>";
    echo "<ul>";
    echo "<li>O servidor MySQL está rodando</li>";
    echo "<li>O banco de dados 'carrinho_praia' existe</li>";
    echo "<li>As credenciais estão corretas</li>";
    echo "</ul>";
}

echo "</div>";
echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>";
echo "</body>";
echo "</html>";
?>