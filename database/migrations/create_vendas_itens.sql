-- ============================================
-- MIGRAÇÃO: Criar tabela vendas_itens
-- Descrição: Armazena os itens individuais de cada venda
-- ============================================

CREATE TABLE IF NOT EXISTS `vendas_itens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `venda_id` INT(11) NOT NULL,
  `produto_id` INT(11) NOT NULL,
  `produto_nome` VARCHAR(255) NOT NULL,
  `quantidade` INT(11) NOT NULL DEFAULT 1,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_venda_id` (`venda_id`),
  KEY `idx_produto_id` (`produto_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_vendas_itens_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vendas_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados históricos das vendas existentes (se existirem produtos cadastrados)
-- Nota: Como não temos histórico detalhado de itens, vamos criar registros genéricos
-- apenas para vendas que ainda não têm itens associados

-- Esta query é segura pois só insere se não houver itens para a venda
INSERT INTO vendas_itens (venda_id, produto_id, produto_nome, quantidade, preco_unitario, subtotal)
SELECT 
    v.id as venda_id,
    COALESCE((SELECT id FROM produtos WHERE usuario_id = v.usuario_id LIMIT 1), 1) as produto_id,
    'Venda Histórica' as produto_nome,
    1 as quantidade,
    v.total as preco_unitario,
    v.total as subtotal
FROM vendas v
WHERE NOT EXISTS (
    SELECT 1 FROM vendas_itens vi WHERE vi.venda_id = v.id
)
AND EXISTS (
    SELECT 1 FROM produtos WHERE usuario_id = v.usuario_id LIMIT 1
);

-- ============================================
-- FIM DA MIGRAÇÃO
-- ============================================
