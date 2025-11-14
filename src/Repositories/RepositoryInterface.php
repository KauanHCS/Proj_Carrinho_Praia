<?php

namespace CarrinhoDePreia\Repositories;

/**
 * Interface RepositoryInterface
 * 
 * Define o contrato base para todos os repositórios do sistema.
 * Implementa operações CRUD padrão que devem ser suportadas
 * por todos os repositórios concretos.
 * 
 * @package CarrinhoDePreia\Repositories
 */
interface RepositoryInterface
{
    /**
     * Busca um registro por ID
     * 
     * @param int $id ID do registro
     * @return array|null Dados do registro ou null se não encontrado
     */
    public function findById(int $id): ?array;

    /**
     * Busca todos os registros
     * 
     * @param array $filters Filtros opcionais [coluna => valor]
     * @param array $options Opções [limit, offset, orderBy, orderDir]
     * @return array Lista de registros
     */
    public function findAll(array $filters = [], array $options = []): array;

    /**
     * Busca um único registro por critério
     * 
     * @param array $criteria Critérios de busca [coluna => valor]
     * @return array|null Dados do registro ou null
     */
    public function findOneBy(array $criteria): ?array;

    /**
     * Busca múltiplos registros por critério
     * 
     * @param array $criteria Critérios de busca [coluna => valor]
     * @param array $options Opções [limit, offset, orderBy, orderDir]
     * @return array Lista de registros
     */
    public function findBy(array $criteria, array $options = []): array;

    /**
     * Cria um novo registro
     * 
     * @param array $data Dados para inserção
     * @return int|false ID do registro criado ou false em caso de erro
     */
    public function create(array $data): int|false;

    /**
     * Atualiza um registro existente
     * 
     * @param int $id ID do registro
     * @param array $data Dados para atualização
     * @return bool Sucesso da operação
     */
    public function update(int $id, array $data): bool;

    /**
     * Remove um registro
     * 
     * @param int $id ID do registro
     * @return bool Sucesso da operação
     */
    public function delete(int $id): bool;

    /**
     * Conta registros que atendem aos critérios
     * 
     * @param array $criteria Critérios de busca [coluna => valor]
     * @return int Total de registros
     */
    public function count(array $criteria = []): int;

    /**
     * Verifica se existe algum registro com os critérios
     * 
     * @param array $criteria Critérios de busca [coluna => valor]
     * @return bool True se existe, false caso contrário
     */
    public function exists(array $criteria): bool;
}
