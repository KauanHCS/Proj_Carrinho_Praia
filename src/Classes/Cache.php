<?php

namespace CarrinhoDePreia;

/**
 * Classe Cache - Sistema de cache em memória
 * 
 * Implementa cache simples para otimizar consultas repetitivas ao banco
 */
class Cache
{
    private static $cache = [];
    private static $ttl = 300; // 5 minutos padrão
    private static $hits = 0;
    private static $misses = 0;
    
    /**
     * Obter valor do cache
     * 
     * @param string $key Chave do cache
     * @return mixed|null Valor cacheado ou null se não encontrado/expirado
     */
    public static function get($key)
    {
        if (!isset(self::$cache[$key])) {
            self::$misses++;
            return null;
        }
        
        $item = self::$cache[$key];
        
        // Verificar se expirou
        if (time() >= $item['expires']) {
            unset(self::$cache[$key]);
            self::$misses++;
            return null;
        }
        
        self::$hits++;
        return $item['data'];
    }
    
    /**
     * Armazenar valor no cache
     * 
     * @param string $key Chave do cache
     * @param mixed $data Dados a cachear
     * @param int|null $ttl Tempo de vida em segundos (null usa padrão)
     */
    public static function set($key, $data, $ttl = null)
    {
        $ttl = $ttl ?? self::$ttl;
        
        self::$cache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
    }
    
    /**
     * Verificar se chave existe e não expirou
     * 
     * @param string $key Chave do cache
     * @return bool
     */
    public static function has($key)
    {
        if (!isset(self::$cache[$key])) {
            return false;
        }
        
        // Verificar se expirou
        if (time() >= self::$cache[$key]['expires']) {
            unset(self::$cache[$key]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Deletar item do cache
     * 
     * @param string $key Chave do cache
     */
    public static function delete($key)
    {
        unset(self::$cache[$key]);
    }
    
    /**
     * Limpar todo o cache
     */
    public static function clear()
    {
        self::$cache = [];
    }
    
    /**
     * Limpar cache expirado
     * 
     * @return int Número de itens removidos
     */
    public static function clearExpired()
    {
        $now = time();
        $removed = 0;
        
        foreach (self::$cache as $key => $item) {
            if ($now >= $item['expires']) {
                unset(self::$cache[$key]);
                $removed++;
            }
        }
        
        return $removed;
    }
    
    /**
     * Obter estatísticas do cache
     * 
     * @return array
     */
    public static function getStats()
    {
        $total = self::$hits + self::$misses;
        $hitRate = $total > 0 ? (self::$hits / $total) * 100 : 0;
        
        return [
            'hits' => self::$hits,
            'misses' => self::$misses,
            'hit_rate' => round($hitRate, 2),
            'items' => count(self::$cache),
            'memory_usage' => self::getMemoryUsage()
        ];
    }
    
    /**
     * Resetar estatísticas
     */
    public static function resetStats()
    {
        self::$hits = 0;
        self::$misses = 0;
    }
    
    /**
     * Obter uso de memória estimado
     * 
     * @return string
     */
    private static function getMemoryUsage()
    {
        $bytes = strlen(serialize(self::$cache));
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
    
    /**
     * Obter ou criar cache (cache aside pattern)
     * 
     * @param string $key Chave do cache
     * @param callable $callback Função para gerar dados se não estiver em cache
     * @param int|null $ttl Tempo de vida em segundos
     * @return mixed Dados cacheados ou gerados
     */
    public static function remember($key, $callback, $ttl = null)
    {
        $cached = self::get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = call_user_func($callback);
        self::set($key, $data, $ttl);
        
        return $data;
    }
    
    /**
     * Invalidar cache por padrão de chave
     * 
     * @param string $pattern Padrão regex
     * @return int Número de itens removidos
     */
    public static function invalidatePattern($pattern)
    {
        $removed = 0;
        
        foreach (self::$cache as $key => $item) {
            if (preg_match($pattern, $key)) {
                unset(self::$cache[$key]);
                $removed++;
            }
        }
        
        return $removed;
    }
    
    /**
     * Definir TTL padrão
     * 
     * @param int $seconds Segundos
     */
    public static function setDefaultTTL($seconds)
    {
        self::$ttl = $seconds;
    }
}
