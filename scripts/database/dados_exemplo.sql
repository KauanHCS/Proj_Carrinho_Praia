-- ===============================================
-- DADOS DE EXEMPLO - CARRINHO DE PRAIA
-- ===============================================
-- Execute este script para criar dados de demonstração

USE sistema_carrinho;

-- Usuário administrador padrão (senha: admin123)
INSERT IGNORE INTO usuarios (id, nome, email, senha, tipo, ativo) VALUES
(1, 'Administrador', 'admin@carrinhopraia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Produtos de exemplo
INSERT IGNORE INTO produtos (id, nome, categoria, preco_compra, preco_venda, quantidade, limite_minimo, observacoes, usuario_id) VALUES
(1, 'Cadeira de Praia', 'acessorio', 15.00, 25.00, 6, 2, 'Cadeira dobravel resistente', 1),
(2, 'Guarda-sol', 'acessorio', 20.00, 35.00, 4, 1, 'Guarda-sol grande com protetor UV', 1),
(3, 'Caipirinhas 400ml', 'bebida', 3.00, 8.00, 15, 5, 'Bebida gelada para adultos', 1),
(4, 'Agua de Coco', 'bebida', 2.00, 5.00, 25, 8, 'Natural e gelada', 1),
(5, 'Protetor Solar FPS 50', 'acessorio', 8.00, 18.00, 10, 3, 'À prova d\'agua, 200ml', 1),
(6, 'Cerveja Lata', 'bebida', 2.50, 6.00, 30, 10, 'Cerveja gelada', 1),
(7, 'Sanduiche Natural', 'comida', 4.00, 10.00, 8, 3, 'Presunto e queijo', 1),
(8, 'Pipoca Doce', 'comida', 1.50, 4.00, 20, 5, 'Pacote pequeno', 1);

-- Vendas de exemplo
INSERT IGNORE INTO vendas (id, data, forma_pagamento, total, valor_pago, troco, desconto, usuario_id, status) VALUES
(1, '2024-01-15 10:30:00', 'dinheiro', 33.00, 35.00, 2.00, 0.00, 1, 'concluida'),
(2, '2024-01-15 11:45:00', 'pix', 43.00, 43.00, 0.00, 0.00, 1, 'concluida'),
(3, '2024-01-15 14:20:00', 'cartao', 18.00, 18.00, 0.00, 0.00, 1, 'concluida'),
(4, '2024-01-15 15:10:00', 'dinheiro', 25.00, 30.00, 5.00, 0.00, 1, 'concluida'),
(5, '2024-01-15 16:30:00', 'pix', 16.00, 16.00, 0.00, 0.00, 1, 'concluida');

-- Itens das vendas
INSERT IGNORE INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES
-- Venda 1: Cadeira + Agua de coco
(1, 1, 1, 25.00),  -- Cadeira
(1, 4, 2, 5.00),   -- 2 Aguas de coco

-- Venda 2: Guarda-sol + Caipirinhas
(2, 2, 1, 35.00),  -- Guarda-sol
(2, 3, 1, 8.00),   -- Caipirinha

-- Venda 3: Protetor solar
(3, 5, 1, 18.00),  -- Protetor solar

-- Venda 4: Cadeira de praia
(4, 1, 1, 25.00),  -- Cadeira

-- Venda 5: Cervejas
(5, 6, 2, 6.00),   -- 2 Cervejas
(5, 8, 1, 4.00);   -- 1 Pipoca

-- Atualizar quantidades dos produtos baseado nas vendas
UPDATE produtos SET quantidade = quantidade - (
    SELECT COALESCE(SUM(iv.quantidade), 0) 
    FROM itens_venda iv 
    JOIN vendas v ON iv.venda_id = v.id 
    WHERE iv.produto_id = produtos.id AND v.status = 'concluida'
) WHERE id IN (1,2,3,4,5,6,7,8);

SELECT 'Dados de exemplo inseridos com sucesso!' as Status;