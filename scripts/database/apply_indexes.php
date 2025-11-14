<?php

/**
 * Script para aplicar índices otimizados no banco de dados
 * 
 * Execução:
 * php scripts/database/apply_indexes.php
 * 
 * Ou acesse via navegador:
 * http://localhost/Proj_Carrinho_Praia/scripts/database/apply_indexes.php
 */

// Carregar bootstrap
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Logger;

// Definir se está executando via CLI ou navegador
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Aplicar Índices</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #569cd6; }
        .warning { color: #ce9178; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>";
}

function output($message, $type = 'info') {
    global $isCLI;
    
    if ($isCLI) {
        $colors = [
            'success' => "\033[32m",
            'error' => "\033[31m",
            'warning' => "\033[33m",
            'info' => "\033[36m",
            'reset' => "\033[0m"
        ];
        echo $colors[$type] . $message . $colors['reset'] . "\n";
    } else {
        echo "<div class='$type'>$message</div>";
    }
}

try {
    output("=================================================", 'info');
    output("  APLICAÇÃO DE ÍNDICES OTIMIZADOS", 'info');
    output("  Sistema Carrinho de Praia v3.0", 'info');
    output("=================================================", 'info');
    output("");
    
    // Conectar ao banco
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    output("✓ Conectado ao banco de dados", 'success');
    output("");
    
    // Ler arquivo SQL
    $sqlFile = __DIR__ . '/optimize_indexes.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo optimize_indexes.sql não encontrado!");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // Dividir em statements individuais
    $statements = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function($stmt) {
            // Ignorar comentários e linhas vazias
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^\/\*/', $stmt);
        }
    );
    
    output("📄 Arquivo lido: " . count($statements) . " comandos encontrados", 'info');
    output("");
    
    $successCount = 0;
    $skipCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        if (empty($statement)) {
            continue;
        }
        
        // Extrair nome do índice do comando
        preg_match('/INDEX\s+(`?)(\w+)(`?)\s+ON/i', $statement, $matches);
        $indexName = $matches[2] ?? "Comando #" . ($index + 1);
        
        try {
            // Executar statement
            $result = $conn->query($statement);
            
            if ($result === false) {
                // Verificar se o erro é "índice já existe"
                if (strpos($conn->error, 'Duplicate key name') !== false) {
                    output("⊖ $indexName - Já existe (pulando)", 'warning');
                    $skipCount++;
                } else {
                    throw new Exception($conn->error);
                }
            } else {
                output("✓ $indexName - Criado com sucesso", 'success');
                $successCount++;
            }
            
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Verificar se é erro de índice duplicado
            if (strpos($errorMsg, 'Duplicate key name') !== false) {
                output("⊖ $indexName - Já existe (pulando)", 'warning');
                $skipCount++;
            } else {
                output("✗ $indexName - ERRO: $errorMsg", 'error');
                $errors[] = ['index' => $indexName, 'error' => $errorMsg];
                $errorCount++;
            }
        }
    }
    
    output("");
    output("=================================================", 'info');
    output("  RESUMO DA APLICAÇÃO", 'info');
    output("=================================================", 'info');
    output("✓ Criados com sucesso: $successCount", 'success');
    output("⊖ Já existentes (pulados): $skipCount", 'warning');
    output("✗ Erros: $errorCount", $errorCount > 0 ? 'error' : 'info');
    output("");
    
    if ($errorCount > 0) {
        output("ERROS ENCONTRADOS:", 'error');
        foreach ($errors as $error) {
            output("  • {$error['index']}: {$error['error']}", 'error');
        }
        output("");
    }
    
    // Verificar índices criados
    output("=================================================", 'info');
    output("  VERIFICAÇÃO DOS ÍNDICES", 'info');
    output("=================================================", 'info');
    output("");
    
    $tables = ['produtos', 'vendas', 'movimentacoes', 'itens_venda', 'usuarios'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name LIKE 'idx_%'");
        
        if ($result && $result->num_rows > 0) {
            output("Tabela: $table", 'info');
            while ($row = $result->fetch_assoc()) {
                output("  • {$row['Key_name']} ({$row['Column_name']})", 'success');
            }
            output("");
        }
    }
    
    // Log de sucesso
    Logger::info('Índices aplicados com sucesso', [
        'success' => $successCount,
        'skipped' => $skipCount,
        'errors' => $errorCount
    ]);
    
    output("=================================================", 'info');
    output("✓ Processo concluído!", 'success');
    output("=================================================", 'info');
    
    if (!$isCLI) {
        echo "</body></html>";
    }
    
    exit(0);
    
} catch (Exception $e) {
    output("");
    output("=================================================", 'error');
    output("  ERRO FATAL", 'error');
    output("=================================================", 'error');
    output($e->getMessage(), 'error');
    output("");
    
    if (isset($e->getTrace()[0])) {
        $trace = $e->getTrace()[0];
        output("Arquivo: " . ($trace['file'] ?? 'desconhecido'), 'error');
        output("Linha: " . ($trace['line'] ?? 'desconhecida'), 'error');
    }
    
    Logger::error('Erro ao aplicar índices', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (!$isCLI) {
        echo "</body></html>";
    }
    
    exit(1);
}
