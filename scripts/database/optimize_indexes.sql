-- ===============================================
-- OTIMIZAÇÃO DE ÍNDICES - Sistema Carrinho de Praia
-- ===============================================
-- Adiciona índices compostos para melhorar performance de consultas

USE sistema_carrinho;

-- ===============================================
-- TABELA: vendas
-- ===============================================

-- Índice composto para consultas por usuário e data
ALTER TABLE vendas 
ADD INDEX IF NOT EXISTS idx_usuario_data (usuario_id, data);

-- Índice composto para consultas por data e forma de pagamento
ALTER TABLE vendas 
ADD INDEX IF NOT EXISTS idx_data_pagamento (data, forma_pagamento);

-- Índice composto para status e data
ALTER TABLE vendas 
ADD INDEX IF NOT EXISTS idx_status_data (status, data);

-- ===============================================
-- TABELA: produtos
-- ===============================================

-- Índice composto para consultas de produtos por usuário e categoria
ALTER TABLE produtos 
ADD INDEX IF NOT EXISTS idx_usuario_categoria (usuario_id, categoria, ativo);

-- Índice composto para produtos ativos por usuário
ALTER TABLE produtos 
ADD INDEX IF NOT EXISTS idx_usuario_ativo (usuario_id, ativo);

-- Índice composto para busca por nome de produto ativo
ALTER TABLE produtos 
ADD INDEX IF NOT EXISTS idx_nome_ativo (nome, ativo);

-- Índice composto para estoque baixo
ALTER TABLE produtos 
ADD INDEX IF NOT EXISTS idx_estoque_alerta (usuario_id, quantidade, limite_minimo, ativo);

-- Índice para validade de produtos
ALTER TABLE produtos 
ADD INDEX IF NOT EXISTS idx_validade (validade, ativo);

-- ===============================================
-- TABELA: movimentacoes
-- ===============================================

-- Índice composto para histórico de movimentações por produto
ALTER TABLE movimentacoes 
ADD INDEX IF NOT EXISTS idx_produto_data (produto_id, data DESC);

-- Índice composto para movimentações por tipo e data
ALTER TABLE movimentacoes 
ADD INDEX IF NOT EXISTS idx_tipo_data (tipo, data DESC);

-- Índice composto para movimentações por usuário
ALTER TABLE movimentacoes 
ADD INDEX IF NOT EXISTS idx_usuario_data (usuario_id, data DESC);

-- Índice para movimentações relacionadas a vendas
ALTER TABLE movimentacoes 
ADD INDEX IF NOT EXISTS idx_venda (venda_id, tipo);

-- ===============================================
-- TABELA: itens_venda
-- ===============================================

-- Índice composto para itens por venda
ALTER TABLE itens_venda 
ADD INDEX IF NOT EXISTS idx_venda_produto (venda_id, produto_id);

-- Índice composto para produtos mais vendidos
ALTER TABLE itens_venda 
ADD INDEX IF NOT EXISTS idx_produto_quantidade (produto_id, quantidade);

-- ===============================================
-- TABELA: usuarios
-- ===============================================

-- Índice composto para busca de usuários ativos
ALTER TABLE usuarios 
ADD INDEX IF NOT EXISTS idx_tipo_ativo (tipo, ativo);

-- Índice para data de cadastro
ALTER TABLE usuarios 
ADD INDEX IF NOT EXISTS idx_data_cadastro (data_cadastro DESC);

-- ===============================================
-- VERIFICAR ÍNDICES CRIADOS
-- ===============================================

-- Listar todos os índices da tabela vendas
SHOW INDEX FROM vendas;

-- Listar todos os índices da tabela produtos
SHOW INDEX FROM produtos;

-- Listar todos os índices da tabela movimentacoes
SHOW INDEX FROM movimentacoes;

-- Listar todos os índices da tabela itens_venda
SHOW INDEX FROM itens_venda;

-- ===============================================
-- ESTATÍSTICAS DAS TABELAS
-- ===============================================

-- Atualizar estatísticas das tabelas para melhor otimização
ANALYZE TABLE vendas;
ANALYZE TABLE produtos;
ANALYZE TABLE movimentacoes;
ANALYZE TABLE itens_venda;
ANALYZE TABLE usuarios;

-- ===============================================
-- QUERIES DE EXEMPLO OTIMIZADAS
-- ===============================================

-- Exemplo 1: Vendas de um usuário em período específico
-- OTIMIZADO por idx_usuario_data
-- SELECT * FROM vendas 
-- WHERE usuario_id = 1 
-- AND data BETWEEN '2025-01-01' AND '2025-12-31';

-- Exemplo 2: Produtos com estoque baixo de um usuário
-- OTIMIZADO por idx_estoque_alerta
-- SELECT * FROM produtos 
-- WHERE usuario_id = 1 
-- AND quantidade <= limite_minimo 
-- AND ativo = 1;

-- Exemplo 3: Movimentações de um produto por data
-- OTIMIZADO por idx_produto_data
-- SELECT * FROM movimentacoes 
-- WHERE produto_id = 1 
-- ORDER BY data DESC 
-- LIMIT 50;

-- Exemplo 4: Produtos mais vendidos
-- OTIMIZADO por idx_produto_quantidade
-- SELECT p.nome, SUM(iv.quantidade) as total_vendido
-- FROM itens_venda iv
-- JOIN produtos p ON iv.produto_id = p.id
-- GROUP BY p.id, p.nome
-- ORDER BY total_vendido DESC
-- LIMIT 10;

SELECT 'Índices otimizados criados com sucesso!' AS Status;
