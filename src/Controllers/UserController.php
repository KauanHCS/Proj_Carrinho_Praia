<?php

namespace CarrinhoDePreia\Controllers;

class UserController extends BaseController
{
    public static function listarCodigosFuncionarios(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        try {
            $stmt = $pdo->prepare(
                'SELECT cf.*, COUNT(DISTINCT uc.usuario_id) AS total_usos
                 FROM codigos_funcionarios cf
                 LEFT JOIN usos_codigo uc ON cf.id = uc.codigo_id
                 WHERE cf.admin_id = ?
                 GROUP BY cf.id
                 ORDER BY cf.data_criacao DESC'
            );
            $stmt->execute([$userId]);
            $codigos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($codigos as &$codigo) {
                $stmt = $pdo->prepare(
                    "SELECT u.id, u.nome, u.email, u.funcao_funcionario AS funcao
                     FROM usuarios u
                     WHERE u.codigo_admin = ? AND u.tipo_usuario = 'funcionario'
                     ORDER BY u.nome"
                );
                $stmt->execute([$userId]);
                $codigo['funcionarios'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            self::json(true, $codigos, 'Códigos carregados');
        } catch (\Throwable $e) {
            self::logError('Erro listar codigos funcionarios', ['error' => $e->getMessage()]);
            self::error('Erro ao listar códigos');
        }
    }

    public static function estatisticasPerfil(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();
        $hoje   = date('Y-m-d');

        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM vendas WHERE usuario_id = ? AND DATE(data) = ?');
            $stmt->execute([$userId, $hoje]);
            $totalVendas = (int) ($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $pdo->prepare('SELECT COALESCE(SUM(total), 0) AS total FROM vendas WHERE usuario_id = ? AND DATE(data) = ?');
            $stmt->execute([$userId, $hoje]);
            $faturamento = (float) ($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM produtos WHERE usuario_id = ?');
            $stmt->execute([$userId]);
            $produtos = (int) ($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            self::json(true, [
                'vendas_hoje'           => $totalVendas,
                'faturamento_hoje'      => number_format($faturamento, 2, ',', '.'),
                'produtos_cadastrados'  => $produtos,
            ], 'Estatísticas carregadas');
        } catch (\Throwable $e) {
            self::logError('Erro estatisticas perfil', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar estatísticas');
        }
    }

    public static function atividadeRecente(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        try {
            $stmt = $pdo->prepare(
                "SELECT 'venda' AS tipo, data, total
                 FROM vendas WHERE usuario_id = ?
                 ORDER BY data DESC LIMIT 5"
            );
            $stmt->execute([$userId]);
            self::json(true, $stmt->fetchAll(\PDO::FETCH_ASSOC), 'Atividades carregadas');
        } catch (\Throwable $e) {
            self::logError('Erro atividade recente', ['error' => $e->getMessage()]);
            self::error('Erro ao carregar atividades');
        }
    }
}
