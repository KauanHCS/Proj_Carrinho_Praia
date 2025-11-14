<?php

namespace CarrinhoDePreia;

/**
 * Classe Logger - Sistema de logging estruturado
 */
class Logger
{
    private static $logFile = null;
    private static $enabled = true;
    private static $minLevel = 'debug';
    
    private static $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    
    /**
     * Inicializar logger
     */
    private static function init()
    {
        if (self::$logFile === null) {
            self::$logFile = defined('PROJECT_ROOT') 
                ? PROJECT_ROOT . '/logs/app.log'
                : __DIR__ . '/../../logs/app.log';
            
            // Criar diretório de logs se não existir
            $logDir = dirname(self::$logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
        }
    }
    
    /**
     * Log genérico
     */
    private static function log($level, $message, $context = [])
    {
        if (!self::$enabled) return;
        
        self::init();
        
        // Verificar nível mínimo
        if (self::$levels[$level] < self::$levels[self::$minLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logMessage = sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr
        );
        
        error_log($logMessage, 3, self::$logFile);
        
        // Rotacionar log se necessário (> 10MB)
        self::rotateIfNeeded();
    }
    
    public static function debug($message, $context = [])
    {
        self::log('debug', $message, $context);
    }
    
    public static function info($message, $context = [])
    {
        self::log('info', $message, $context);
    }
    
    public static function warning($message, $context = [])
    {
        self::log('warning', $message, $context);
    }
    
    public static function error($message, $context = [])
    {
        self::log('error', $message, $context);
    }
    
    public static function critical($message, $context = [])
    {
        self::log('critical', $message, $context);
    }
    
    /**
     * Rotacionar arquivo de log se muito grande
     */
    private static function rotateIfNeeded()
    {
        if (!file_exists(self::$logFile)) return;
        
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (filesize(self::$logFile) > $maxSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = self::$logFile . '.' . $timestamp;
            rename(self::$logFile, $backupFile);
            
            // Manter apenas últimos 5 arquivos
            $logDir = dirname(self::$logFile);
            $logFiles = glob($logDir . '/app.log.*');
            usort($logFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            foreach (array_slice($logFiles, 5) as $oldFile) {
                unlink($oldFile);
            }
        }
    }
    
    public static function setMinLevel($level)
    {
        self::$minLevel = $level;
    }
    
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }
}
