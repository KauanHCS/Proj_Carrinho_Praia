-- ===============================================
-- CARRINHO DE PRAIA - DATABASE SCRIPT FOR WAMP
-- ===============================================
-- Compatível com MySQL 8.0+ / MariaDB 10.4+
-- Otimizado para WAMP Server

SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS sistema_carrinho 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sistema_carrinho;

-- ===============================================
-- TABELAS PRINCIPAIS
-- ===============================================

-- Tabela de usuários com suporte a Google OAuth
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255),
    google_id VARCHAR(100),
    imagem_url VARCHAR(500),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    ativo TINYINT(1) DEFAULT 1,
    tipo ENUM('admin', 'vendedor', 'usuario') DEFAULT 'vendedor',
    INDEX idx_email (email),
    INDEX idx_google_id (google_id),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de produtos com campos aprimorados
DROP TABLE IF EXISTS produtos;
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria ENUM('bebida', 'comida', 'acessorio', 'outros') DEFAULT 'outros',
    preco DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    limite_minimo INT NOT NULL DEFAULT 5,
    validade DATE NULL,
    observacoes TEXT,
    codigo_barras VARCHAR(50),
    imagem_url VARCHAR(500),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1,
    INDEX idx_nome (nome),
    INDEX idx_categoria (categoria),
    INDEX idx_estoque_baixo (quantidade, limite_minimo),
    INDEX idx_ativo (ativo),
    INDEX idx_codigo_barras (codigo_barras)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de vendas
DROP TABLE IF EXISTS vendas;
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao', 'multiplo') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    valor_pago DECIMAL(10,2) NULL,
    troco DECIMAL(10,2) NULL,
    desconto DECIMAL(10,2) DEFAULT 0.00,
    usuario_id INT,
    cliente_nome VARCHAR(100),
    cliente_telefone VARCHAR(20),
    observacoes TEXT,
    status ENUM('concluida', 'cancelada', 'pendente') DEFAULT 'concluida',
    INDEX idx_data (data),
    INDEX idx_forma_pagamento (forma_pagamento),
    INDEX idx_status (status),
    INDEX idx_usuario (usuario_id),
    CONSTRAINT fk_vendas_usuario FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens da venda
DROP TABLE IF EXISTS itens_venda;
CREATE TABLE itens_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (quantidade * preco_unitario) STORED,
    desconto_item DECIMAL(10,2) DEFAULT 0.00,
    INDEX idx_venda (venda_id),
    INDEX idx_produto (produto_id),
    CONSTRAINT fk_itens_venda FOREIGN KEY (venda_id) 
        REFERENCES vendas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) 
        REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de movimentações de estoque
DROP TABLE IF EXISTS movimentacoes;
CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    produto_id INT NOT NULL,
    tipo ENUM('entrada', 'saida', 'ajuste', 'perda', 'devolucao') NOT NULL,
    quantidade INT NOT NULL,
    quantidade_anterior INT,
    quantidade_atual INT,
    descricao VARCHAR(255),
    usuario_id INT,
    venda_id INT NULL,
    INDEX idx_data (data),
    INDEX idx_produto (produto_id),
    INDEX idx_tipo (tipo),
    INDEX idx_usuario (usuario_id),
    CONSTRAINT fk_mov_produto FOREIGN KEY (produto_id) 
        REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_mov_usuario FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_mov_venda FOREIGN KEY (venda_id) 
        REFERENCES vendas(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações do sistema
DROP TABLE IF EXISTS configuracoes;
CREATE TABLE configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT,
    descricao VARCHAR(255),
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    categoria VARCHAR(50) DEFAULT 'geral',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- CONFIGURAÇÕES PADRÃO
-- ===============================================

INSERT INTO configuracoes (chave, valor, descricao, tipo, categoria) VALUES
('sistema_nome', 'Carrinho de Praia', 'Nome do sistema', 'string', 'geral'),
('sistema_versao', '1.0.0', 'Versão do sistema', 'string', 'geral'),
('moeda_simbolo', 'R$', 'Símbolo da moeda', 'string', 'financeiro'),
('alerta_estoque_dias', '7', 'Dias antes do vencimento para alertar', 'number', 'estoque'),
('backup_automatico', 'false', 'Realizar backup automático', 'boolean', 'sistema'),
('tema_cor', '#0066cc', 'Cor principal do tema', 'string', 'interface'),
('timezone', 'America/Sao_Paulo', 'Fuso horário do sistema', 'string', 'geral'),
('limite_produtos_venda', '50', 'Limite de produtos por venda', 'number', 'vendas'),
('desconto_maximo', '50.00', 'Desconto máximo permitido (%)', 'number', 'vendas');

-- ===============================================
-- VIEWS OTIMIZADAS
-- ===============================================

-- View para produtos com estoque baixo ou próximos do vencimento
DROP VIEW IF EXISTS vw_produtos_estoque_baixo;
CREATE VIEW vw_produtos_estoque_baixo AS
SELECT 
    p.id,
    p.nome,
    p.categoria,
    p.preco,
    p.quantidade,
    p.limite_minimo,
    p.validade,
    p.observacoes,
    p.ativo,
    CASE 
        WHEN p.validade IS NOT NULL THEN DATEDIFF(p.validade, CURDATE())
        ELSE NULL 
    END as dias_para_vencer,
    CASE 
        WHEN p.quantidade <= p.limite_minimo THEN 'estoque_baixo'
        WHEN p.validade IS NOT NULL AND DATEDIFF(p.validade, CURDATE()) <= 7 THEN 'vencimento_proximo'
        ELSE 'normal'
    END as tipo_alerta
FROM produtos p 
WHERE p.ativo = 1 
  AND (p.quantidade <= p.limite_minimo 
       OR (p.validade IS NOT NULL AND DATEDIFF(p.validade, CURDATE()) <= 7))
ORDER BY p.quantidade ASC, dias_para_vencer ASC;

-- View para vendas de hoje (compatível com ONLY_FULL_GROUP_BY)
DROP VIEW IF EXISTS vw_vendas_hoje;
CREATE VIEW vw_vendas_hoje AS
SELECT 
    v.id,
    v.data,
    v.forma_pagamento,
    v.total,
    v.valor_pago,
    v.troco,
    v.desconto,
    v.usuario_id,
    v.cliente_nome,
    v.cliente_telefone,
    v.observacoes,
    v.status,
    u.nome as vendedor_nome,
    COALESCE(agg.total_itens, 0) as total_itens,
    COALESCE(agg.quantidade_total, 0) as quantidade_total
FROM vendas v
LEFT JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN (
    SELECT 
        iv.venda_id,
        COUNT(iv.id) as total_itens,
        SUM(iv.quantidade) as quantidade_total
    FROM itens_venda iv
    GROUP BY iv.venda_id
) agg ON agg.venda_id = v.id
WHERE DATE(v.data) = CURDATE()
ORDER BY v.data DESC;

-- View para produtos mais vendidos hoje
DROP VIEW IF EXISTS vw_produtos_mais_vendidos;
CREATE VIEW vw_produtos_mais_vendidos AS
SELECT 
    p.id,
    p.nome,
    p.categoria,
    p.preco,
    SUM(iv.quantidade) as total_vendido,
    SUM(iv.subtotal) as receita_total,
    COUNT(DISTINCT iv.venda_id) as vendas_distintas,
    AVG(iv.preco_unitario) as preco_medio_venda
FROM produtos p
JOIN itens_venda iv ON p.id = iv.produto_id
JOIN vendas v ON iv.venda_id = v.id
WHERE DATE(v.data) = CURDATE() AND v.status = 'concluida'
GROUP BY p.id, p.nome, p.categoria, p.preco
ORDER BY total_vendido DESC;

-- View para relatório de vendas por período
DROP VIEW IF EXISTS vw_relatorio_vendas;
CREATE VIEW vw_relatorio_vendas AS
SELECT 
    DATE(v.data) as data_venda,
    COUNT(v.id) as total_vendas,
    SUM(v.total) as receita_total,
    SUM(v.desconto) as desconto_total,
    AVG(v.total) as ticket_medio,
    SUM(CASE WHEN v.forma_pagamento = 'dinheiro' THEN v.total ELSE 0 END) as dinheiro,
    SUM(CASE WHEN v.forma_pagamento = 'pix' THEN v.total ELSE 0 END) as pix,
    SUM(CASE WHEN v.forma_pagamento = 'cartao' THEN v.total ELSE 0 END) as cartao,
    SUM(CASE WHEN v.forma_pagamento = 'multiplo' THEN v.total ELSE 0 END) as multiplo
FROM vendas v
WHERE v.status = 'concluida'
GROUP BY DATE(v.data)
ORDER BY data_venda DESC;

-- ===============================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ===============================================

CREATE INDEX idx_vendas_data_total ON vendas(data, total);
CREATE INDEX idx_movimentacoes_data_tipo ON movimentacoes(data, tipo);
CREATE INDEX idx_produtos_nome_categoria ON produtos(nome, categoria);
CREATE INDEX idx_itens_venda_produto_data ON itens_venda(produto_id, venda_id);

-- ===============================================
-- TRIGGERS PARA AUTOMAÇÃO
-- ===============================================

DELIMITER //

-- Trigger: Registrar movimentação ao inserir produto
DROP TRIGGER IF EXISTS tr_produto_inserted//
CREATE TRIGGER tr_produto_inserted 
AFTER INSERT ON produtos
FOR EACH ROW
BEGIN
    IF NEW.quantidade > 0 THEN
        INSERT INTO movimentacoes (
            produto_id, tipo, quantidade, quantidade_anterior, 
            quantidade_atual, descricao
        ) VALUES (
            NEW.id, 'entrada', NEW.quantidade, 0, 
            NEW.quantidade, 'Estoque inicial do produto'
        );
    END IF;
END//

-- Trigger: Registrar movimentação ao atualizar estoque
DROP TRIGGER IF EXISTS tr_produto_estoque_updated//
CREATE TRIGGER tr_produto_estoque_updated 
AFTER UPDATE ON produtos
FOR EACH ROW
BEGIN
    IF OLD.quantidade != NEW.quantidade THEN
        IF NEW.quantidade > OLD.quantidade THEN
            INSERT INTO movimentacoes (
                produto_id, tipo, quantidade, quantidade_anterior,
                quantidade_atual, descricao
            ) VALUES (
                NEW.id, 'entrada', NEW.quantidade - OLD.quantidade, 
                OLD.quantidade, NEW.quantidade, 'Ajuste de estoque - entrada'
            );
        ELSE
            INSERT INTO movimentacoes (
                produto_id, tipo, quantidade, quantidade_anterior,
                quantidade_atual, descricao
            ) VALUES (
                NEW.id, 'saida', OLD.quantidade - NEW.quantidade, 
                OLD.quantidade, NEW.quantidade, 'Ajuste de estoque - saída'
            );
        END IF;
    END IF;
END//

-- Trigger: Atualizar estoque após venda
DROP TRIGGER IF EXISTS tr_item_venda_inserted//
CREATE TRIGGER tr_item_venda_inserted
AFTER INSERT ON itens_venda
FOR EACH ROW
BEGIN
    DECLARE produto_nome VARCHAR(150);
    DECLARE estoque_anterior INT;
    
    -- Buscar dados do produto
    SELECT nome, quantidade INTO produto_nome, estoque_anterior 
    FROM produtos WHERE id = NEW.produto_id;
    
    -- Atualizar estoque
    UPDATE produtos 
    SET quantidade = quantidade - NEW.quantidade 
    WHERE id = NEW.produto_id;
    
    -- Registrar movimentação
    INSERT INTO movimentacoes (
        produto_id, tipo, quantidade, quantidade_anterior,
        quantidade_atual, descricao, venda_id
    ) VALUES (
        NEW.produto_id, 'saida', NEW.quantidade, estoque_anterior,
        estoque_anterior - NEW.quantidade, 
        CONCAT('Venda - ', produto_nome), NEW.venda_id
    );
END//

-- Trigger: Atualizar último login do usuário
DROP TRIGGER IF EXISTS tr_usuario_login//
CREATE TRIGGER tr_usuario_login
BEFORE UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.ultimo_login != OLD.ultimo_login OR OLD.ultimo_login IS NULL THEN
        SET NEW.ultimo_login = CURRENT_TIMESTAMP;
    END IF;
END//

DELIMITER ;

-- ===============================================
-- DADOS DE EXEMPLO (DESCOMENTE PARA USAR)
-- ===============================================

/*
-- Usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES
('Administrador', 'admin@carrinhopraia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Produtos de exemplo
INSERT INTO produtos (nome, categoria, preco, quantidade, limite_minimo, observacoes) VALUES
('Água Mineral 500ml', 'bebida', 2.50, 100, 20, 'Água gelada sempre disponível'),
('Refrigerante Lata', 'bebida', 4.00, 50, 10, 'Coca-Cola, Pepsi, Guaraná'),
('Cerveja Lata', 'bebida', 5.50, 40, 8, 'Somente para maiores de 18 anos'),
('Sanduíche Natural', 'comida', 8.00, 25, 5, 'Presunto e queijo, fresco'),
('Pipoca Doce', 'comida', 3.50, 30, 8, 'Pacote pequeno, feita na hora'),
('Protetor Solar FPS 30', 'acessorio', 15.00, 15, 3, 'À prova d\'água, 120ml'),
('Chapéu de Praia', 'acessorio', 12.00, 12, 3, 'Várias cores disponíveis'),
('Boia Infantil', 'acessorio', 25.00, 8, 2, 'Para crianças de 3 a 8 anos'),
('Picolé', 'comida', 4.50, 60, 15, 'Diversos sabores'),
('Óculos de Sol', 'acessorio', 18.00, 10, 2, 'Proteção UV400');
*/

-- ===============================================
-- FINALIZAÇÃO
-- ===============================================

-- Verificar se todas as tabelas foram criadas
SELECT 
    TABLE_NAME as 'Tabela Criada',
    TABLE_ROWS as 'Registros',
    CREATE_TIME as 'Criada em'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'sistema_carrinho' 
  AND TABLE_TYPE = 'BASE TABLE'
ORDER BY TABLE_NAME;

SELECT 
    'Database "sistema_carrinho" inicializado com sucesso!' as 'Status',
    '🎉 Pronto para usar com WAMP!' as 'Mensagem',
    NOW() as 'Data/Hora';
