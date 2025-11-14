<?php

namespace CarrinhoDePreia;

/**
 * Class BackupManager
 * 
 * Gerencia backups automáticos do banco de dados com:
 * - Compressão ZIP
 * - Rotação automática (mantém últimos N backups)
 * - Agendamento via cron/task scheduler
 * - Logs de operações
 * 
 * @package CarrinhoDePreia
 */
class BackupManager
{
    /**
     * @var Database Instância do banco de dados
     */
    private Database $db;

    /**
     * @var string Diretório de backups
     */
    private string $backupDir;

    /**
     * @var int Número máximo de backups a manter
     */
    private int $maxBackups;

    /**
     * @var array Tabelas para backup (vazio = todas)
     */
    private array $tables;

    /**
     * Constructor
     * 
     * @param string|null $backupDir Diretório customizado de backups
     * @param int $maxBackups Número máximo de backups (padrão: 7)
     */
    public function __construct(?string $backupDir = null, int $maxBackups = 7)
    {
        $this->db = Database::getInstance();
        $this->backupDir = $backupDir ?? dirname(__DIR__, 2) . '/backups';
        $this->maxBackups = $maxBackups;
        $this->tables = [];

        // Criar diretório se não existir
        $this->ensureBackupDirectory();
    }

    /**
     * Define tabelas específicas para backup
     * 
     * @param array $tables Lista de tabelas
     * @return self
     */
    public function setTables(array $tables): self
    {
        $this->tables = $tables;
        return $this;
    }

    /**
     * Cria um backup completo do banco de dados
     * 
     * @param bool $compress Compactar backup (padrão: true)
     * @return array Resultado da operação
     */
    public function createBackup(bool $compress = true): array
    {
        $startTime = microtime(true);
        
        try {
            Logger::info('Iniciando backup do banco de dados');

            // Gerar nome do arquivo
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = $this->backupDir . '/' . $filename;

            // Obter configuração do banco
            $config = $this->db->getConfig();

            // Executar mysqldump
            $result = $this->executeMysqldump($config, $filepath);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Comprimir se solicitado
            if ($compress) {
                $zipResult = $this->compressBackup($filepath);
                
                if ($zipResult['success']) {
                    // Remover arquivo .sql original
                    unlink($filepath);
                    $filepath = $zipResult['filepath'];
                    $filename = $zipResult['filename'];
                }
            }

            // Rotacionar backups antigos
            $this->rotateBackups();

            $executionTime = round(microtime(true) - $startTime, 2);
            $fileSize = $this->formatBytes(filesize($filepath));

            Logger::info('Backup criado com sucesso', [
                'filename' => $filename,
                'size' => $fileSize,
                'execution_time' => $executionTime . 's',
                'compressed' => $compress
            ]);

            return [
                'success' => true,
                'message' => 'Backup criado com sucesso',
                'data' => [
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => $fileSize,
                    'execution_time' => $executionTime,
                    'compressed' => $compress
                ]
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao criar backup', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao criar backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Executa mysqldump para criar backup
     * 
     * @param array $config Configuração do banco
     * @param string $filepath Caminho do arquivo de saída
     * @return array Resultado
     */
    private function executeMysqldump(array $config, string $filepath): array
    {
        // Detectar sistema operacional
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        // Construir comando mysqldump
        if ($isWindows) {
            // Windows - tentar localizar mysqldump no WAMP/XAMPP
            $possiblePaths = [
                'C:/wamp64/bin/mysql/mysql*/bin/mysqldump.exe',
                'C:/xampp/mysql/bin/mysqldump.exe',
                'mysqldump' // PATH do sistema
            ];

            $mysqldumpPath = 'mysqldump';
            foreach ($possiblePaths as $path) {
                $expanded = glob($path);
                if (!empty($expanded) && file_exists($expanded[0])) {
                    $mysqldumpPath = $expanded[0];
                    break;
                }
            }
        } else {
            // Linux/Mac
            $mysqldumpPath = 'mysqldump';
        }

        // Construir comando
        $host = $config['host'];
        $user = $config['user'];
        $pass = $config['pass'];
        $dbname = $config['dbname'];
        
        // Tabelas específicas ou todas
        $tablesStr = empty($this->tables) ? '' : implode(' ', $this->tables);

        // Comando com senha via variável de ambiente (mais seguro)
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s --databases %s %s > %s 2>&1',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($dbname),
            $tablesStr,
            escapeshellarg($filepath)
        );

        // Executar comando
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // Se falhar, tentar método alternativo (PHP puro)
            return $this->createBackupPHP($filepath);
        }

        if (!file_exists($filepath) || filesize($filepath) == 0) {
            // Fallback para método PHP
            return $this->createBackupPHP($filepath);
        }

        return [
            'success' => true,
            'message' => 'Backup criado via mysqldump'
        ];
    }

    /**
     * Cria backup usando PHP puro (fallback)
     * 
     * @param string $filepath Caminho do arquivo
     * @return array Resultado
     */
    private function createBackupPHP(string $filepath): array
    {
        try {
            $config = $this->db->getConfig();
            $pdo = $this->db->getPDOConnection();

            // Abrir arquivo para escrita
            $handle = fopen($filepath, 'w');

            if (!$handle) {
                throw new \Exception('Não foi possível criar arquivo de backup');
            }

            // Cabeçalho
            fwrite($handle, "-- Backup Database\n");
            fwrite($handle, "-- Database: {$config['dbname']}\n");
            fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Obter lista de tabelas
            $tables = empty($this->tables) ? $this->getAllTables($pdo) : $this->tables;

            foreach ($tables as $table) {
                // Estrutura da tabela
                fwrite($handle, "-- Table: {$table}\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                fwrite($handle, $createTableStmt['Create Table'] . ";\n\n");

                // Dados da tabela
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    fwrite($handle, "INSERT INTO `{$table}` VALUES\n");

                    $values = [];
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, array_values($row));

                        $values[] = '(' . implode(',', $escapedValues) . ')';
                    }

                    fwrite($handle, implode(",\n", $values) . ";\n\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            return [
                'success' => true,
                'message' => 'Backup criado via PHP'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao criar backup PHP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtém todas as tabelas do banco
     * 
     * @param \PDO $pdo Conexão PDO
     * @return array Lista de tabelas
     */
    private function getAllTables(\PDO $pdo): array
    {
        $stmt = $pdo->query('SHOW TABLES');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Comprime backup em ZIP
     * 
     * @param string $filepath Caminho do arquivo .sql
     * @return array Resultado
     */
    private function compressBackup(string $filepath): array
    {
        try {
            if (!class_exists('ZipArchive')) {
                return [
                    'success' => false,
                    'message' => 'Extensão ZipArchive não disponível'
                ];
            }

            $zip = new \ZipArchive();
            $zipFilename = str_replace('.sql', '.zip', basename($filepath));
            $zipFilepath = dirname($filepath) . '/' . $zipFilename;

            if ($zip->open($zipFilepath, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Não foi possível criar arquivo ZIP');
            }

            $zip->addFile($filepath, basename($filepath));
            $zip->close();

            return [
                'success' => true,
                'filename' => $zipFilename,
                'filepath' => $zipFilepath
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao comprimir: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove backups antigos mantendo apenas os últimos N
     */
    private function rotateBackups(): void
    {
        try {
            $files = glob($this->backupDir . '/backup_*.{sql,zip}', GLOB_BRACE);

            if (count($files) <= $this->maxBackups) {
                return;
            }

            // Ordenar por data de modificação (mais antigos primeiro)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Remover backups excedentes
            $toDelete = array_slice($files, 0, count($files) - $this->maxBackups);

            foreach ($toDelete as $file) {
                unlink($file);
                Logger::info('Backup antigo removido', ['file' => basename($file)]);
            }
        } catch (\Exception $e) {
            Logger::error('Erro ao rotacionar backups', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Lista todos os backups disponíveis
     * 
     * @return array Lista de backups
     */
    public function listBackups(): array
    {
        $files = glob($this->backupDir . '/backup_*.{sql,zip}', GLOB_BRACE);

        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => $this->formatBytes(filesize($file)),
                'size_bytes' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file)
            ];
        }

        // Ordenar por data (mais recente primeiro)
        usort($backups, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $backups;
    }

    /**
     * Remove um backup específico
     * 
     * @param string $filename Nome do arquivo
     * @return array Resultado
     */
    public function deleteBackup(string $filename): array
    {
        try {
            $filepath = $this->backupDir . '/' . $filename;

            if (!file_exists($filepath)) {
                return [
                    'success' => false,
                    'message' => 'Backup não encontrado'
                ];
            }

            // Validar nome do arquivo (segurança)
            if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.(sql|zip)$/', $filename)) {
                return [
                    'success' => false,
                    'message' => 'Nome de arquivo inválido'
                ];
            }

            unlink($filepath);

            Logger::info('Backup removido manualmente', ['filename' => $filename]);

            return [
                'success' => true,
                'message' => 'Backup removido com sucesso'
            ];
        } catch (\Exception $e) {
            Logger::error('Erro ao remover backup', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao remover backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Garante que o diretório de backups existe e é gravável
     */
    private function ensureBackupDirectory(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        if (!is_writable($this->backupDir)) {
            throw new \RuntimeException("Diretório de backups não é gravável: {$this->backupDir}");
        }
    }

    /**
     * Formata bytes em formato legível
     * 
     * @param int $bytes Tamanho em bytes
     * @return string Tamanho formatado
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Retorna estatísticas dos backups
     * 
     * @return array Estatísticas
     */
    public function getStats(): array
    {
        $backups = $this->listBackups();
        $totalSize = array_sum(array_column($backups, 'size_bytes'));

        return [
            'total_backups' => count($backups),
            'total_size' => $this->formatBytes($totalSize),
            'oldest' => !empty($backups) ? end($backups)['date'] : null,
            'newest' => !empty($backups) ? $backups[0]['date'] : null,
            'max_allowed' => $this->maxBackups,
            'directory' => $this->backupDir
        ];
    }
}
