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

        $vendasDiaUsuario = 'DATE(data) = ? AND usuario_id = ? AND (status IS NULL OR status <> \'cancelada\')';

        try {
            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(total), 0) AS faturamento_hoje,
                        COUNT(*) AS num_atendimentos,
                        COALESCE(AVG(total), 0) AS ticket_medio
                 FROM vendas WHERE {$vendasDiaUsuario}"
            );
            $stmt->execute([$hoje, $userId]);
            $kpisHoje = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(total), 0) AS faturamento,
                        COUNT(*) AS atendimentos,
                        COALESCE(AVG(total), 0) AS ticket_medio
                 FROM vendas WHERE {$vendasDiaUsuario}"
            );
            $stmt->execute([$ontem, $userId]);
            $dadosOntem = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->execute([$semanaPassada, $userId]);
            $dadosSemanaPassada = $stmt->fetch(\PDO::FETCH_ASSOC);

            /** Variação percentual: se a base (ontem) for zero e hoje houver valor, retorna 100% em vez de 0. */
            $diffPct = static function (float $atual, float $anterior): float {
                if ($anterior > 0) {
                    return (($atual - $anterior) / $anterior) * 100;
                }
                if ($atual > 0) {
                    return 100.0;
                }

                return 0.0;
            };

            $diffFat   = $diffPct((float) $kpisHoje['faturamento_hoje'], (float) $dadosOntem['faturamento']);
            $diffTicket = $diffPct((float) $kpisHoje['ticket_medio'], (float) $dadosOntem['ticket_medio']);
            $diffAtend  = (int) $kpisHoje['num_atendimentos'] - (int) $dadosOntem['atendimentos'];

            $diffFatSem    = $diffPct((float) $kpisHoje['faturamento_hoje'], (float) $dadosSemanaPassada['faturamento']);
            $diffTicketSem = $diffPct((float) $kpisHoje['ticket_medio'], (float) $dadosSemanaPassada['ticket_medio']);
            $diffAtendSem  = (int) $kpisHoje['num_atendimentos'] - (int) $dadosSemanaPassada['atendimentos'];

            $stmt = $pdo->prepare(
                "SELECT HOUR(data) AS hora, COALESCE(SUM(total), 0) AS total, COUNT(*) AS quantidade
                 FROM vendas WHERE {$vendasDiaUsuario}
                 GROUP BY HOUR(data) ORDER BY hora"
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

            // Top 5 produtos: vendas do dia (itens_venda) + comandas fechadas no dia (guarda-sol)
            $porNome = [];
            try {
                $stmt = $pdo->prepare(
                    'SELECT MAX(COALESCE(NULLIF(TRIM(p.nome), \'\'), CONCAT(\'Produto #\', iv.produto_id))) AS nome,
                            SUM(iv.quantidade) AS quantidade,
                            SUM(iv.subtotal) AS total
                     FROM itens_venda iv
                     INNER JOIN vendas v ON iv.venda_id = v.id
                     LEFT JOIN produtos p ON p.id = iv.produto_id
                     WHERE DATE(v.data) = ? AND v.usuario_id = ?
                       AND (v.status IS NULL OR v.status <> \'cancelada\')
                     GROUP BY iv.produto_id
                     ORDER BY quantidade DESC'
                );
                $stmt->execute([$hoje, $userId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $n = (string) ($row['nome'] ?? '');
                    if ($n === '') {
                        $n = 'Produto';
                    }
                    if (!isset($porNome[$n])) {
                        $porNome[$n] = ['nome' => $n, 'quantidade' => 0, 'total' => 0.0];
                    }
                    $porNome[$n]['quantidade'] += (int) $row['quantidade'];
                    $porNome[$n]['total'] += (float) $row['total'];
                }
            } catch (\Throwable $e) {
                // itens_venda / join pode falhar em bases legadas
            }

            try {
                $stmt = $pdo->prepare(
                    "SELECT produtos FROM comandas WHERE DATE(data_fechamento) = ? AND usuario_id = ? AND status = 'fechado'"
                );
                $stmt->execute([$hoje, $userId]);
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $c) {
                    $produtos = json_decode($c['produtos'], true) ?: [];
                    foreach ($produtos as $prod) {
                        $nome = isset($prod['nome']) ? trim((string) $prod['nome']) : '';
                        if ($nome === '') {
                            $nome = 'Item';
                        }
                        if (!isset($porNome[$nome])) {
                            $porNome[$nome] = ['nome' => $nome, 'quantidade' => 0, 'total' => 0.0];
                        }
                        $porNome[$nome]['quantidade'] += (int) ($prod['quantidade'] ?? 0);
                        $porNome[$nome]['total'] += (float) ($prod['subtotal'] ?? 0);
                    }
                }
            } catch (\Throwable $e) {
                // tabela comandas opcional
            }

            $listaTop = array_values($porNome);
            usort($listaTop, static fn ($a, $b) => $b['quantidade'] <=> $a['quantidade']);
            $topProdutos = array_slice($listaTop, 0, 5);

            // Build payment methods totals, treating sales with cliente_fiado_id as Fiado
            $formasMap = [];
            try {
                $stmt = $pdo->prepare(
                    "SELECT forma_pagamento, forma_pagamento_secundaria, forma_pagamento_terciaria, cliente_fiado_id, total
                     FROM vendas WHERE {$vendasDiaUsuario}"
                );
                $stmt->execute([$hoje, $userId]);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($rows as $r) {
                    $totalVenda = (float) ($r['total'] ?? 0);

                    // If sale used fiado (cliente_fiado_id set), count entire sale as Fiado
                    if (!empty($r['cliente_fiado_id'])) {
                        $formasMap['Fiado'] = ($formasMap['Fiado'] ?? 0) + $totalVenda;
                        continue;
                    }

                    // Collect non-empty payment parts
                    $parts = [];
                    if (!empty($r['forma_pagamento'])) $parts[] = $r['forma_pagamento'];
                    if (!empty($r['forma_pagamento_secundaria'])) $parts[] = $r['forma_pagamento_secundaria'];
                    if (!empty($r['forma_pagamento_terciaria'])) $parts[] = $r['forma_pagamento_terciaria'];

                    $parts = array_values(array_unique($parts));
                    $count = count($parts);

                    if ($count === 0) {
                        $formasMap['Outros'] = ($formasMap['Outros'] ?? 0) + $totalVenda;
                        continue;
                    }

                    // Distribute total equally among payment parts (best-effort)
                    $partValue = $totalVenda / $count;
                    foreach ($parts as $p) {
                        $formasMap[$p] = ($formasMap[$p] ?? 0) + $partValue;
                    }
                }

                $formasPagamento = [];
                foreach ($formasMap as $k => $v) {
                    $formasPagamento[] = ['forma_pagamento' => $k, 'total' => $v];
                }
            } catch (\Throwable $e) {
                // Fallback to simple aggregation if something fails
                $stmt = $pdo->prepare(
                    "SELECT forma_pagamento, SUM(total) AS total
                     FROM vendas WHERE {$vendasDiaUsuario} AND forma_pagamento IS NOT NULL AND forma_pagamento <> ''
                     GROUP BY forma_pagamento ORDER BY total DESC"
                );
                $stmt->execute([$hoje, $userId]);
                $formasPagamento = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

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
