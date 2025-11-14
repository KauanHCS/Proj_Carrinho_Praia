-- MIGRAÇÃO: Adicionar suporte para pagamento misto
-- Data: 2025-01-13
-- Descrição: Permite que uma venda seja paga com até 3 formas de pagamento diferentes

USE sistema_carrinho;

-- Adicionar colunas para pagamento secundário
ALTER TABLE vendas 
ADD COLUMN forma_pagamento_secundaria VARCHAR(50) NULL AFTER valor_pago,
ADD COLUMN valor_pago_secundario DECIMAL(10,2) NULL DEFAULT 0.00 AFTER forma_pagamento_secundaria;

-- Adicionar colunas para pagamento terciário
ALTER TABLE vendas 
ADD COLUMN forma_pagamento_terciaria VARCHAR(50) NULL AFTER valor_pago_secundario,
ADD COLUMN valor_pago_terciario DECIMAL(10,2) NULL DEFAULT 0.00 AFTER forma_pagamento_terciaria;

-- Adicionar índices para melhor performance
ALTER TABLE vendas 
ADD INDEX idx_forma_pagamento_secundaria (forma_pagamento_secundaria),
ADD INDEX idx_forma_pagamento_terciaria (forma_pagamento_terciaria);

-- Comentários nas colunas
ALTER TABLE vendas 
MODIFY COLUMN forma_pagamento VARCHAR(50) COMMENT 'Forma de pagamento principal',
MODIFY COLUMN valor_pago DECIMAL(10,2) COMMENT 'Valor pago na forma principal',
MODIFY COLUMN forma_pagamento_secundaria VARCHAR(50) COMMENT 'Forma de pagamento secundária (opcional)',
MODIFY COLUMN valor_pago_secundario DECIMAL(10,2) COMMENT 'Valor pago na forma secundária',
MODIFY COLUMN forma_pagamento_terciaria VARCHAR(50) COMMENT 'Forma de pagamento terciária (opcional)',
MODIFY COLUMN valor_pago_terciario DECIMAL(10,2) COMMENT 'Valor pago na forma terciária';

-- Verificar estrutura
DESCRIBE vendas;

-- Exemplo de uso:
-- INSERT INTO vendas (usuario_id, nome_cliente, total, 
--                     forma_pagamento, valor_pago,
--                     forma_pagamento_secundaria, valor_pago_secundario,
--                     forma_pagamento_terciaria, valor_pago_terciario,
--                     status_pagamento, data)
-- VALUES (1, 'João Silva', 100.00,
--         'pix', 50.00,
--         'dinheiro', 30.00,
--         'cartao', 20.00,
--         'pago', NOW());
