<?php
/**
 * Health Check Endpoint
 * Monitoramento da saúde do sistema
 */

require_once __DIR__ . '/../bootstrap.php';

use CarrinhoDePreia\Database;
use CarrinhoDePreia\Logger;

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'version' => '1.2.0',
    'checks' => []
];

// 1. Verificar conexão com banco de dados
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $result = $conn->query("SELECT 1");
    
    if ($result) {
        $health['checks']['database'] = [
            'status' => 'ok',
            'message' => 'Conectado'
        ];
    } else {
        throw new Exception('Query falhou');
    }
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Falha na conexão: ' . $e->getMessage()
    ];
    Logger::error('Health check database failed', ['error' => $e->getMessage()]);
}

// 2. Verificar espaço em disco
try {
    $freeSpace = disk_free_space(__DIR__);
    $totalSpace = disk_total_space(__DIR__);
    
    if ($freeSpace && $totalSpace) {
        $usedPercent = (1 - ($freeSpace / $totalSpace)) * 100;
        $status = $usedPercent < 90 ? 'ok' : ($usedPercent < 95 ? 'warning' : 'critical');
        
        $health['checks']['disk_space'] = [
            'status' => $status,
            'used_percent' => round($usedPercent, 2),
            'free_space' => round($freeSpace / (1024 * 1024 * 1024), 2) . ' GB'
        ];
        
        if ($status !== 'ok') {
            $health['status'] = $status;
        }
    }
} catch (Exception $e) {
    $health['checks']['disk_space'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// 3. Verificar diretórios críticos
$criticalDirs = [
    'logs' => dirname(__DIR__) . '/logs',
    'backups' => dirname(__DIR__) . '/backups'
];

foreach ($criticalDirs as $name => $path) {
    if (!is_dir($path)) {
        $health['checks']['dir_' . $name] = [
            'status' => 'warning',
            'message' => 'Diretório não existe'
        ];
    } elseif (!is_writable($path)) {
        $health['checks']['dir_' . $name] = [
            'status' => 'error',
            'message' => 'Diretório não é gravável'
        ];
        $health['status'] = 'error';
    } else {
        $health['checks']['dir_' . $name] = [
            'status' => 'ok',
            'message' => 'Gravável'
        ];
    }
}

// 4. Verificar uso de memória PHP
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = convertToBytes($memoryLimit);

if ($memoryLimitBytes > 0) {
    $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;
    $status = $memoryPercent < 80 ? 'ok' : ($memoryPercent < 90 ? 'warning' : 'critical');
    
    $health['checks']['memory'] = [
        'status' => $status,
        'used' => round($memoryUsage / (1024 * 1024), 2) . ' MB',
        'limit' => $memoryLimit,
        'used_percent' => round($memoryPercent, 2)
    ];
}

// 5. Verificar extensões PHP necessárias
$requiredExtensions = ['mysqli', 'json', 'session', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    $health['checks']['php_extensions'] = [
        'status' => 'ok',
        'message' => 'Todas extensões carregadas'
    ];
} else {
    $health['status'] = 'error';
    $health['checks']['php_extensions'] = [
        'status' => 'error',
        'missing' => $missingExtensions
    ];
}

// Definir código HTTP apropriado
http_response_code($health['status'] === 'ok' ? 200 : 503);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

/**
 * Converter string de memória para bytes
 */
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}
