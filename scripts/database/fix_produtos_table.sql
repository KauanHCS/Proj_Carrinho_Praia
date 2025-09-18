-- ===============================================
-- FIX TABELA PRODUTOS - ADICIONAR COLUNAS
-- ===============================================
USE sistema_carrinho;

-- Verificar estrutura atual
DESCRIBE produtos;

-- Adicionar colunas se não existirem
ALTER TABLE produtos 
ADD COLUMN IF NOT EXISTS preco_compra DECIMAL(10,2) DEFAULT 0.00 AFTER categoria,
ADD COLUMN IF NOT EXISTS preco_venda DECIMAL(10,2) DEFAULT 0.00 AFTER preco_compra,
ADD COLUMN IF NOT EXISTS usuario_id INT DEFAULT 1 AFTER ativo;

-- Se a coluna 'preco' existir, copiar valores para preco_venda
UPDATE produtos SET preco_venda = preco WHERE preco_venda = 0 AND preco > 0;

-- Adicionar constraint de foreign key para usuario_id se não existir
-- ALTER TABLE produtos ADD CONSTRAINT fk_produtos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE;

SELECT 'Tabela produtos corrigida!' as Status;