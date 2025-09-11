-- Carrinho de Praia Database Initialization Script
-- Run this script to set up the database schema

CREATE DATABASE IF NOT EXISTS sistema_carrinho CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_carrinho;

-- Users table with Google OAuth support
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255), -- Will store hashed passwords
    google_id VARCHAR(100),
    imagem_url VARCHAR(500),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
);

-- Products table with enhanced fields
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria ENUM('bebida', 'comida', 'outros') DEFAULT 'outros',
    preco DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    limite_minimo INT NOT NULL DEFAULT 5,
    validade DATE NULL,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_categoria (categoria),
    INDEX idx_estoque_baixo (quantidade, limite_minimo)
);

-- Sales table
CREATE TABLE IF NOT EXISTS vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    valor_pago DECIMAL(10,2) DEFAULT NULL,
    troco DECIMAL(10,2) DEFAULT NULL,
    usuario_id INT,
    observacoes TEXT,
    status ENUM('concluida', 'cancelada') DEFAULT 'concluida',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_data (data),
    INDEX idx_forma_pagamento (forma_pagamento)
);

-- Sale items table
CREATE TABLE IF NOT EXISTS itens_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venda_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * preco_unitario) STORED,
    FOREIGN KEY (venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    INDEX idx_venda (venda_id),
    INDEX idx_produto (produto_id)
);

-- Inventory movements table
CREATE TABLE IF NOT EXISTS movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    produto_id INT NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    descricao VARCHAR(255),
    usuario_id INT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_data (data),
    INDEX idx_produto (produto_id),
    INDEX idx_tipo (tipo)
);

-- System settings table for configuration
CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT,
    descricao VARCHAR(255),
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default configuration values
INSERT IGNORE INTO configuracoes (chave, valor, descricao, tipo) VALUES
('sistema_nome', 'Carrinho de Praia', 'Nome do sistema', 'string'),
('moeda_simbolo', 'R$', 'Símbolo da moeda', 'string'),
('alerta_estoque_dias', '7', 'Dias antes do vencimento para alertar', 'number'),
('backup_automatico', 'false', 'Realizar backup automático', 'boolean'),
('tema_cor', '#0066cc', 'Cor principal do tema', 'string');

-- Create views for commonly used queries
CREATE OR REPLACE VIEW vw_produtos_estoque_baixo AS
SELECT 
    p.*,
    DATEDIFF(p.validade, CURDATE()) as dias_para_vencer
FROM produtos p 
WHERE p.quantidade <= p.limite_minimo 
   OR (p.validade IS NOT NULL AND DATEDIFF(p.validade, CURDATE()) <= 7)
ORDER BY p.quantidade ASC, dias_para_vencer ASC;

CREATE OR REPLACE VIEW vw_vendas_hoje AS
SELECT 
    v.*,
    u.nome as vendedor_nome,
    COUNT(iv.id) as total_itens,
    SUM(iv.quantidade) as quantidade_total
FROM vendas v
LEFT JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN itens_venda iv ON v.id = iv.venda_id
WHERE DATE(v.data) = CURDATE()
GROUP BY v.id
ORDER BY v.data DESC;

CREATE OR REPLACE VIEW vw_produtos_mais_vendidos AS
SELECT 
    p.id,
    p.nome,
    p.categoria,
    SUM(iv.quantidade) as total_vendido,
    SUM(iv.subtotal) as receita_total,
    COUNT(DISTINCT iv.venda_id) as vendas_distintas
FROM produtos p
JOIN itens_venda iv ON p.id = iv.produto_id
JOIN vendas v ON iv.venda_id = v.id
WHERE DATE(v.data) = CURDATE()
GROUP BY p.id, p.nome, p.categoria
ORDER BY total_vendido DESC;

-- Sample data for testing (optional)
-- Uncomment if you want sample data for development

/*
INSERT IGNORE INTO produtos (nome, categoria, preco, quantidade, limite_minimo, observacoes) VALUES
('Água Mineral 500ml', 'bebida', 2.50, 50, 10, 'Água gelada'),
('Refrigerante Lata', 'bebida', 4.00, 30, 8, 'Coca-Cola, Pepsi, Guaraná'),
('Cerveja Lata', 'bebida', 5.50, 25, 5, 'Somente para maiores de 18 anos'),
('Sanduíche Natural', 'comida', 8.00, 15, 3, 'Presunto e queijo'),
('Pipoca Doce', 'comida', 3.50, 20, 5, 'Pacote pequeno'),
('Protetor Solar FPS 30', 'outros', 15.00, 10, 2, 'À prova d\'água'),
('Chapéu de Praia', 'outros', 12.00, 8, 2, 'Várias cores'),
('Boia Infantil', 'outros', 25.00, 5, 1, 'Para crianças até 8 anos');
*/

-- Create indexes for better performance
CREATE INDEX idx_vendas_data_total ON vendas(data, total);
CREATE INDEX idx_movimentacoes_data_tipo ON movimentacoes(data, tipo);
CREATE INDEX idx_produtos_nome ON produtos(nome);

DELIMITER //

-- Trigger to automatically create inventory movement on product creation
CREATE TRIGGER tr_produto_inserted AFTER INSERT ON produtos
FOR EACH ROW
BEGIN
    IF NEW.quantidade > 0 THEN
        INSERT INTO movimentacoes (produto_id, tipo, quantidade, descricao)
        VALUES (NEW.id, 'entrada', NEW.quantidade, 'Estoque inicial');
    END IF;
END//

-- Trigger to update inventory movements on stock changes
CREATE TRIGGER tr_produto_estoque_updated AFTER UPDATE ON produtos
FOR EACH ROW
BEGIN
    IF OLD.quantidade != NEW.quantidade THEN
        IF NEW.quantidade > OLD.quantidade THEN
            INSERT INTO movimentacoes (produto_id, tipo, quantidade, descricao)
            VALUES (NEW.id, 'entrada', NEW.quantidade - OLD.quantidade, 'Ajuste de estoque');
        ELSE
            INSERT INTO movimentacoes (produto_id, tipo, quantidade, descricao)
            VALUES (NEW.id, 'saida', OLD.quantidade - NEW.quantidade, 'Ajuste de estoque');
        END IF;
    END IF;
END//

DELIMITER ;

-- Grant appropriate permissions (adjust as needed)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON sistema_carrinho.* TO 'app_user'@'localhost';

SELECT 'Database initialization completed successfully!' as message;
