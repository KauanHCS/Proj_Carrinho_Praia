<?php

namespace CarrinhoDePreia\Controllers;

class DashboardController extends BaseController
{
    public static function metrics(): void
    {
        $userId = self::requireAuth();
        $pdo    = self::getPdo();

        $hoje          = date('Y-m-d');
        $ontem         = date('Y-m-d', strtotime('-1 day'));
        $semanaPassada = date('Y-m-d', strtotime('-7 days'));

        try {
            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(total), 0) AS faturamento_hoje,
                        COUNT(*) AS num_atendimentos,
                        COALESCE(AVG(total), 0) AS ticket_medio
                 FROM vendas WHERE DATE(data) = ? AND usuario_id = ?'
            );
            $stmt->execute([$hoje, $userId]);
            $kpisHoje = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(total), 0) AS faturamento,
                        COUNT(*) AS atendimentos,
                        COALESCE(AVG(total), 0) AS ticket_medio
                 FROM vendas WHERE DATE(data) = ? AND usuario_id = ?'
            );
            $stmt->execute([$ontem, $userId]);
            $dadosOntem = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->execute([$semanaPassada, $userId]);
            $dadosSemanaPassada = $stmt->fetch(\PDO::FETCH_ASSOC);

            $diff = static function ($atual, $anterior) {
                return $anterior > 0 ? (($atual - $anterior) / $anterior) * 100 : 0;
            };

            $diffFat   = $diff($kpisHoje['faturamento_hoje'], $dadosOntem['faturamento']);
            $diffTicket = $diff($kpisHoje['ticket_medio'], $dadosOntem['ticket_medio']);
            $diffAtend  = (int) $kpisHoje['num_atendimentos'] - (int) $dadosOntem['atendimentos'];

            $diffFatSem    = $diff($kpisHoje['faturamento_hoje'], $dadosSemanaPassada['faturamento']);
            $diffTicketSem = $diff($kpisHoje['ticket_medio'], $dadosSemanaPassada['ticket_medio']);
            $diffAtendSem  = (int) $kpisHoje['num_atendimentos'] - (int) $dadosSemanaPassada['atendimentos'];

            $stmt = $pdo->prepare(
                'SELECT HOUR(data) AS hora, COALESCE(SUM(total), 0) AS total, COUNT(*) AS quantidade
                 FROM vendas WHERE DATE(data) = ? AND usuario_id = ?
                 GROUP BY HOUR(data) ORDER BY hora'
            );
            $stmt->execute([$hoje, $userId]);
            $vendasPorHora = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $vendasPorHoraCompleto = [];
            for ($h = 0; $h < 24; $h++) {
                $found = false;
                foreach ($vendasPorHora as $v) {
                    if ((int) $v['hora'] === $h) {
                        $vendasPorHoraCompleto[] = $v;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $vendasPorHoraCompleto[] = ['hora' => str_pad((string) $h, 2, '0', STR_PAD_LEFT), 'total' => '0.00', 'quantidade' => 0];
                }
            }

            $horarioPico = null;
            $maxVendas = 0;
            foreach ($vendasPorHora as $v) {
                if ((int) $v['quantidade'] > $maxVendas) {
                    $maxVendas = (int) $v['quantidade'];
                    $horarioPico = ['hora' => str_pad((string) $v['hora'], 2, '0', STR_PAD_LEFT), 'quantidade' => $v['quantidade']];
                }
            }

            // Top 5 produtos
            $topProdutos = [];
            try {
                $stmt = $pdo->prepare(
                    "SELECT * FROM comandas WHERE DATE(data_fechamento) = ? AND usuario_id = ? AND status = 'fechado'"
                );
                $stmt->execute([$hoje, $userId]);
                $comandas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $produtosAgrupados = [];
                foreach ($comandas as $c) {
                    $produtos = json_decode($c['produtos'], true) ?: [];
                    foreach ($produtos as $prod) {
                        $nome = $prod['nome'];
                        $produtosAgrupados[$nome] = $produtosAgrupados[$nome] ?? ['nome' => $nome, 'quantidade' => 0, 'total' => 0];
                        $produtosAgrupados[$nome]['quantidade'] += $prod['quantidade'];
                        $produtosAgrupados[$nome]['total']      += $prod['subtotal'];
                    }
                }
                usort($produtosAgrupados, static fn ($a, $b) => $b['quantidade'] - $a['quantidade']);
                $topProdutos = array_slice($produtosAgrupados, 0, 5);
            } catch (\Throwable $e) {
                $topProdutos = [];
            }

            $stmt = $pdo->prepare(
                'SELECT forma_pagamento, SUM(total) AS total
                 FROM vendas WHERE DATE(data) = ? AND usuario_id = ? AND forma_pagamento IS NOT NULL
                 GROUP BY forma_pagamento ORDER BY total DESC'
            );
            $stmt->execute([$hoje, $userId]);
            $formasPagamento = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            self::json(true, [
                'faturamento_hoje'                => (float) $kpisHoje['faturamento_hoje'],
                'num_atendimentos'                => (int) $kpisHoje['num_atendimentos'],
                'ticket_medio'                    => (float) $kpisHoje['ticket_medio'],
                'comparacao_ontem_faturamento'    => round($diffFat, 1),
                'comparacao_ontem_ticket'         => round($diffTicket, 1),
                'comparacao_ontem_atendimentos'   => $diffAtend,
                'comparacao_semana_faturamento'   => round($diffFatSem, 1),
                'comparacao_semana_ticket'        => round($diffTicketSem, 1),
                'comparacao_semana_atendimentos'  => $diffAtendSem,
                'dados_ontem' => [
                    'faturamento'      => (float) $dadosOntem['faturamento'],
                    'atendimentos'     => (int)   $dadosOntem['atendimentos'],
                    'ticket_medio'     => (float) $dadosOntem['ticket_medio'],
                    'diff_faturamento' => round($diffFat, 1),
                    'diff_ticket'      => round($diffTicket, 1),
                    'diff_atendimentos'=> $diffAtend,
                ],
                'dados_semana_passada' => [
                    'faturamento'      => (float) $dadosSemanaPassada['faturamento'],
                    'atendimentos'     => (int)   $dadosSemanaPassada['atendimentos'],
                    'ticket_medio'     => (float) $dadosSemanaPassada['ticket_medio'],
                    'diff_faturamento' => round($diffFatSem, 1),
                    'diff_ticket'      => round($diffTicketSem, 1),
                    'diff_atendimentos'=> $diffAtendSem,
                ],
                'vendas_por_hora'  => $vendasPorHoraCompleto,
                'horario_pico'     => $horarioPico,
                'top_produtos'     => $topProdutos,
                'formas_pagamento' => $formasPagamento,
            ], 'Métricas carregadas com sucesso');
        } catch (\Throwable $e) {
            self::logError('Erro métricas dashboard', ['error' => $e->getMessage()]);
            self::error('Erro ao buscar métricas');
        }
    }
}
