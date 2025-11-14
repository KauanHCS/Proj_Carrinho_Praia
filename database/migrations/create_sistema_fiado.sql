-- ============================================
-- MIGRAÇÃO: Sistema de Fiado/Caderneta
-- Descrição: Cria tabelas para controle de vendas fiadas
-- ============================================

-- Tabela de Clientes com Crédito (Fiado)
CREATE TABLE IF NOT EXISTS `clientes_fiado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `cpf` VARCHAR(14) DEFAULT NULL,
  `endereco` TEXT DEFAULT NULL,
  `limite_credito` DECIMAL(10,2) DEFAULT 500.00,
  `saldo_devedor` DECIMAL(10,2) DEFAULT 0.00,
  `observacoes` TEXT DEFAULT NULL,
  `ativo` TINYINT(1) DEFAULT 1,
  `data_cadastro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultima_compra` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_nome` (`nome`),
  KEY `idx_telefone` (`telefone`),
  KEY `idx_ativo` (`ativo`),
  CONSTRAINT `fk_clientes_fiado_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Pagamentos de Fiado
CREATE TABLE IF NOT EXISTS `pagamentos_fiado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` INT(11) NOT NULL,
  `venda_id` INT(11) DEFAULT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `tipo` ENUM('pagamento', 'compra', 'ajuste') DEFAULT 'pagamento',
  `forma_pagamento` VARCHAR(50) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `data_pagamento` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registrado_por` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cliente_id` (`cliente_id`),
  KEY `idx_venda_id` (`venda_id`),
  KEY `idx_data_pagamento` (`data_pagamento`),
  KEY `idx_tipo` (`tipo`),
  CONSTRAINT `fk_pagamentos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_fiado` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pagamentos_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pagamentos_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar coluna cliente_fiado_id na tabela vendas (se não existir)
ALTER TABLE `vendas` 
ADD COLUMN IF NOT EXISTS `cliente_fiado_id` INT(11) DEFAULT NULL AFTER `usuario_id`,
ADD KEY IF NOT EXISTS `idx_cliente_fiado` (`cliente_fiado_id`),
ADD CONSTRAINT `fk_vendas_cliente_fiado` FOREIGN KEY (`cliente_fiado_id`) REFERENCES `clientes_fiado` (`id`) ON DELETE SET NULL;

-- Inserir alguns clientes de exemplo (opcional - comentar se não quiser)
-- INSERT INTO clientes_fiado (usuario_id, nome, telefone, limite_credito, saldo_devedor, observacoes)
-- SELECT id, 'João da Silva', '(13) 99999-1111', 300.00, 0.00, 'Cliente desde janeiro'
-- FROM usuarios WHERE tipo_usuario = 'administrador' LIMIT 1;

-- Criar view para relatório rápido de fiado
CREATE OR REPLACE VIEW `view_resumo_fiado` AS
SELECT 
    cf.id,
    cf.nome,
    cf.telefone,
    cf.saldo_devedor,
    cf.limite_credito,
    cf.ultima_compra,
    COUNT(DISTINCT v.id) as total_compras,
    COALESCE(SUM(CASE WHEN pf.tipo = 'pagamento' THEN pf.valor ELSE 0 END), 0) as total_pago,
    DATEDIFF(NOW(), cf.ultima_compra) as dias_sem_comprar
FROM clientes_fiado cf
LEFT JOIN vendas v ON v.cliente_fiado_id = cf.id
LEFT JOIN pagamentos_fiado pf ON pf.cliente_id = cf.id
WHERE cf.ativo = 1
GROUP BY cf.id, cf.nome, cf.telefone, cf.saldo_devedor, cf.limite_credito, cf.ultima_compra;

-- ============================================
-- FIM DA MIGRAÇÃO
-- ============================================
