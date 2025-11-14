<?php

/**
 * Script de Backup Automático
 * 
 * Pode ser executado via:
 * - Linha de comando: php run_backup.php
 * - Cron (Linux): 0 2 * * * cd /path/to/project && php scripts/backup/run_backup.php
 * - Task Scheduler (Windows): Ver setup_task_scheduler.bat
 */

// Carregar bootstrap
require_once dirname(__DIR__, 2) . '/bootstrap.php';

use CarrinhoDePreia\BackupManager;
use CarrinhoDePreia\Logger;

try {
    echo "=== Sistema de Backup Automático ===\n";
    echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

    // Criar instância do BackupManager
    // Parâmetros: diretório de backups (null = padrão), máximo de backups (7 = última semana)
    $backupManager = new BackupManager(null, 7);

    echo "Iniciando backup...\n";

    // Criar backup com compressão
    $result = $backupManager->createBackup(true);

    if ($result['success']) {
        echo "✓ Backup criado com sucesso!\n";
        echo "  Arquivo: {$result['data']['filename']}\n";
        echo "  Tamanho: {$result['data']['size']}\n";
        echo "  Tempo: {$result['data']['execution_time']}s\n";
        echo "  Comprimido: " . ($result['data']['compressed'] ? 'Sim' : 'Não') . "\n\n";

        // Exibir estatísticas
        $stats = $backupManager->getStats();
        echo "=== Estatísticas de Backups ===\n";
        echo "Total de backups: {$stats['total_backups']}\n";
        echo "Tamanho total: {$stats['total_size']}\n";
        echo "Mais antigo: {$stats['oldest']}\n";
        echo "Mais recente: {$stats['newest']}\n";
        echo "Máximo permitido: {$stats['max_allowed']}\n";
        echo "Diretório: {$stats['directory']}\n";

        exit(0); // Sucesso
    } else {
        echo "✗ Erro ao criar backup:\n";
        echo "  {$result['message']}\n";
        
        exit(1); // Erro
    }
} catch (Exception $e) {
    echo "✗ Erro fatal:\n";
    echo "  {$e->getMessage()}\n";
    
    Logger::critical('Erro fatal no backup automático', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    exit(1);
}
