<?php

namespace CarrinhoDePreia\Config;

/**
 * Env - Gerenciador de variáveis de ambiente
 */
class Env
{
    private static $vars = [];
    private static $loaded = false;
    
    /**
     * Carregar arquivo .env
     */
    public static function load($file = null)
    {
        if (self::$loaded) {
            return;
        }
        
        if ($file === null) {
            $file = defined('PROJECT_ROOT') ? PROJECT_ROOT . '/.env' : __DIR__ . '/../../.env';
        }
        
        if (!file_exists($file)) {
            // Se não existir .env, usar valores padrão do config/database.php
            self::$loaded = true;
            return;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parsear linha KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover aspas se existirem
                $value = trim($value, '"\'');
                
                self::$vars[$key] = $value;
                
                // Definir também como variável de ambiente do PHP
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obter variável de ambiente
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }
        
        // Verificar cache interno primeiro
        if (isset(self::$vars[$key])) {
            return self::$vars[$key];
        }
        
        // Verificar variável de ambiente do sistema
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Definir variável de ambiente
     */
    public static function set($key, $value)
    {
        self::$vars[$key] = $value;
        putenv("$key=$value");
    }
    
    /**
     * Verificar se variável existe
     */
    public static function has($key)
    {
        return isset(self::$vars[$key]) || getenv($key) !== false;
    }
}
