<?php

namespace CarrinhoDePreia;

/**
 * Classe Database - Gerencia conexões com o banco de dados
 * Mantém compatibilidade total com a função getConnection() original
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $servername;
    private $username;
    private $password;
    private $dbname;

    /**
     * Construtor privado para Singleton
     */
    private function __construct()
    {
        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "sistema_carrinho";
        
        $this->connect();
    }

    /**
     * Método para obter instância única (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conecta ao banco de dados
     */
    private function connect()
    {
        try {
            $this->connection = new \mysqli(
                $this->servername, 
                $this->username, 
                $this->password, 
                $this->dbname
            );

            if ($this->connection->connect_error) {
                throw new \Exception("Connection failed: " . $this->connection->connect_error);
            }

            // Configurar charset para UTF-8
            $this->connection->set_charset("utf8mb4");
            
        } catch (\Exception $e) {
            throw new \Exception("Erro na conexão com o banco: " . $e->getMessage());
        }
    }

    /**
     * Retorna a conexão MySQLi - compatível com getConnection() original
     */
    public function getConnection()
    {
        // Verificar se a conexão ainda está ativa
        if (!$this->connection->ping()) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Retorna uma conexão PDO para operações específicas
     * 
     * @return \PDO Conexão PDO
     */
    public function getPDOConnection()
    {
        $dsn = "mysql:host={$this->servername};dbname={$this->dbname};charset=utf8mb4";
        
        try {
            $pdo = new \PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return $pdo;
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao criar conexão PDO: " . $e->getMessage());
        }
    }

    /**
     * Método estático para compatibilidade com código existente
     */
    public static function getStaticConnection()
    {
        return self::getInstance()->getConnection();
    }

    /**
     * Executar query preparada com segurança
     */
    public function executeQuery($sql, $types = "", $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt === false) {
                throw new \Exception("Erro ao preparar query: " . $this->connection->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            return $stmt;

        } catch (\Exception $e) {
            throw new \Exception("Erro ao executar query: " . $e->getMessage());
        }
    }

    /**
     * Executar query SELECT e retornar resultados
     * Suporta parâmetros como array associativo ou com tipos
     */
    public function select($sql, $paramsOrTypes = [], $params = [])
    {
        // Se o segundo parâmetro é array associativo, usar PDO
        if (is_array($paramsOrTypes) && !empty($paramsOrTypes) && empty($params)) {
            return $this->selectPDO($sql, $paramsOrTypes);
        }
        
        $stmt = $this->executeQuery($sql, $paramsOrTypes, $params);
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        return $data;
    }
    
    /**
     * SELECT usando PDO com parâmetros associativos
     */
    private function selectPDO($sql, $params)
    {
        try {
            $pdo = $this->getPDOConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao executar SELECT: " . $e->getMessage());
        }
    }

    /**
     * Executar query SELECT e retornar uma única linha
     */
    public function selectOne($sql, $types = "", $params = [])
    {
        $stmt = $this->executeQuery($sql, $types, $params);
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Executar query INSERT e retornar ID inserido
     * Suporta parâmetros como array associativo ou com tipos
     */
    public function insert($sql, $paramsOrTypes = [], $params = [])
    {
        // Se o segundo parâmetro é array associativo, usar PDO
        if (is_array($paramsOrTypes) && !empty($paramsOrTypes) && empty($params)) {
            return $this->insertPDO($sql, $paramsOrTypes);
        }
        
        $stmt = $this->executeQuery($sql, $paramsOrTypes, $params);
        $insertId = $this->connection->insert_id;
        $stmt->close();
        return $insertId;
    }
    
    /**
     * INSERT usando PDO com parâmetros associativos
     */
    private function insertPDO($sql, $params)
    {
        try {
            $pdo = $this->getPDOConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao executar INSERT: " . $e->getMessage());
        }
    }

    /**
     * Executar query UPDATE/DELETE e retornar linhas afetadas
     * Suporta parâmetros como array associativo ou com tipos
     */
    public function execute($sql, $paramsOrTypes = [], $params = [])
    {
        // Se o segundo parâmetro é array associativo, usar PDO
        if (is_array($paramsOrTypes) && !empty($paramsOrTypes) && empty($params)) {
            return $this->executePDO($sql, $paramsOrTypes);
        }
        
        $stmt = $this->executeQuery($sql, $paramsOrTypes, $params);
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
    
    /**
     * UPDATE/DELETE usando PDO com parâmetros associativos
     */
    private function executePDO($sql, $params)
    {
        try {
            $pdo = $this->getPDOConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao executar UPDATE/DELETE: " . $e->getMessage());
        }
    }
    
    /**
     * Alias para update
     */
    public function update($sql, $paramsOrTypes = [], $params = [])
    {
        return $this->execute($sql, $paramsOrTypes, $params) > 0;
    }
    
    /**
     * Alias para delete
     */
    public function delete($sql, $paramsOrTypes = [], $params = [])
    {
        return $this->execute($sql, $paramsOrTypes, $params) > 0;
    }

    /**
     * Iniciar transação
     */
    public function beginTransaction()
    {
        return $this->connection->begin_transaction();
    }

    /**
     * Confirmar transação
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Reverter transação
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }

    /**
     * Escapar string para prevenir SQL injection
     */
    public function escape($string)
    {
        return $this->connection->real_escape_string($string);
    }

    /**
     * Obter último erro
     */
    public function getError()
    {
        return $this->connection->error;
    }
    
    /**
     * Retorna configuração do banco de dados
     * 
     * @return array Configuração do banco
     */
    public function getConfig()
    {
        return [
            'host' => $this->servername,
            'user' => $this->username,
            'pass' => $this->password,
            'dbname' => $this->dbname
        ];
    }
    
    /**
     * SELECT com paginação
     * 
     * @param string $sql Query SQL base (sem LIMIT)
     * @param string $types Tipos de parâmetros
     * @param array $params Parâmetros
     * @param int $page Página atual (começa em 1)
     * @param int $perPage Itens por página
     * @return array ['data' => array, 'pagination' => array]
     */
    public function selectPaginated($sql, $types = "", $params = [], $page = 1, $perPage = 50)
    {
        $offset = ($page - 1) * $perPage;
        
        // Query com LIMIT para paginação
        $paginatedSql = $sql . " LIMIT ? OFFSET ?";
        $paginatedTypes = $types . "ii";
        $paginatedParams = array_merge($params, [$perPage, $offset]);
        
        // Buscar dados paginados
        $data = $this->select($paginatedSql, $paginatedTypes, $paginatedParams);
        
        // Contar total de registros
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_query";
        $countResult = $this->selectOne($countSql, $types, $params);
        $total = $countResult['total'] ?? 0;
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Validar tipos de parâmetros
     * 
     * @param string $types String de tipos esperados
     * @param array $params Parâmetros a validar
     * @return bool
     * @throws \Exception se tipos não corresponderem
     */
    public function validateParams($types, $params)
    {
        if (strlen($types) !== count($params)) {
            throw new \Exception("Número de parâmetros não corresponde aos tipos especificados");
        }
        
        $typeMap = [
            'i' => 'is_int',
            'd' => 'is_numeric',
            's' => 'is_string',
            'b' => 'is_string' // blob também é string
        ];
        
        for ($i = 0; $i < strlen($types); $i++) {
            $type = $types[$i];
            $param = $params[$i];
            
            if (!isset($typeMap[$type])) {
                throw new \Exception("Tipo '{$type}' não é válido");
            }
            
            // Para inteiros, aceitar valores numéricos que possam ser convertidos
            if ($type === 'i' && is_numeric($param) && intval($param) == $param) {
                continue;
            }
            
            // Para doubles, aceitar qualquer valor numérico
            if ($type === 'd' && is_numeric($param)) {
                continue;
            }
            
            $validator = $typeMap[$type];
            if (!$validator($param)) {
                $expectedType = ['i' => 'integer', 'd' => 'numeric', 's' => 'string', 'b' => 'binary'][$type];
                throw new \Exception("Parâmetro na posição $i deve ser $expectedType");
            }
        }
        
        return true;
    }

    /**
     * Prevenir clonagem
     */
    private function __clone() {}

    /**
     * Prevenir deserialização
     */
    public function __wakeup() {}

    /**
     * Fechar conexão apenas quando realmente necessário
     */
    public function __destruct()
    {
        // Não fechar automaticamente - deixar o PHP gerenciar
        // Isso evita erro de "mysqli object is already closed"
    }
    
    /**
     * Método manual para fechar conexão se necessário
     */
    public function closeConnection()
    {
        if ($this->connection && !$this->connection->connect_error) {
            try {
                $this->connection->close();
            } catch (\Exception $e) {
                // Ignorar erros de fechamento
            }
        }
    }
}

// Função de compatibilidade para não quebrar código existente
// NOTA: Esta será carregada apenas se não existir a função original
if (!function_exists('getConnection')) {
    function getConnection() {
        return \CarrinhoDePreia\Database::getStaticConnection();
    }
}
