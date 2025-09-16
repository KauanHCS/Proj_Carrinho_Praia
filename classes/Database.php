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
            die("Erro na conexão com o banco: " . $e->getMessage());
        }
    }

    /**
     * Retorna a conexão - compatível com getConnection() original
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
     */
    public function select($sql, $types = "", $params = [])
    {
        $stmt = $this->executeQuery($sql, $types, $params);
        $result = $stmt->get_result();
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        return $data;
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
     */
    public function insert($sql, $types = "", $params = [])
    {
        $stmt = $this->executeQuery($sql, $types, $params);
        $insertId = $this->connection->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Executar query UPDATE/DELETE e retornar linhas afetadas
     */
    public function execute($sql, $types = "", $params = [])
    {
        $stmt = $this->executeQuery($sql, $types, $params);
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
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
