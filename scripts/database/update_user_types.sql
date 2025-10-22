-- ===============================================
-- ATUALIZAÇÃO DA TABELA USUÁRIOS
-- Sistema de tipos de usuário e códigos únicos
-- ===============================================

USE sistema_carrinho;

-- Adicionar novos campos à tabela usuários se não existirem
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS tipo_usuario ENUM('administrador', 'funcionario') DEFAULT 'administrador',
ADD COLUMN IF NOT EXISTS funcao_funcionario ENUM('anotar_pedido', 'fazer_pedido', 'ambos') NULL,
ADD COLUMN IF NOT EXISTS codigo_admin VARCHAR(10) NULL,
ADD COLUMN IF NOT EXISTS codigo_unico VARCHAR(10) NOT NULL DEFAULT '' AFTER codigo_admin;

-- Criar índices para otimização
CREATE INDEX IF NOT EXISTS idx_tipo_usuario ON usuarios(tipo_usuario);
CREATE INDEX IF NOT EXISTS idx_codigo_admin ON usuarios(codigo_admin);
CREATE INDEX IF NOT EXISTS idx_codigo_unico ON usuarios(codigo_unico);

-- Atualizar usuários existentes para serem administradores por padrão
UPDATE usuarios SET tipo_usuario = 'administrador' WHERE tipo_usuario IS NULL;

-- Gerar códigos únicos para administradores existentes (6 dígitos aleatórios)
UPDATE usuarios 
SET codigo_unico = LPAD(FLOOR(RAND() * 999999), 6, '0') 
WHERE tipo_usuario = 'administrador' AND (codigo_unico = '' OR codigo_unico IS NULL);

-- ===============================================
-- TABELA PARA CONTROLE DE CÓDIGOS ÚNICOS
-- ===============================================

-- Tabela para rastrear códigos únicos gerados pelos administradores
CREATE TABLE IF NOT EXISTS codigos_funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    admin_id INT NOT NULL,
    funcao ENUM('anotar_pedido', 'fazer_pedido', 'ambos') NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    usado_por_usuario INT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_uso TIMESTAMP NULL,
    ativo TINYINT(1) DEFAULT 1,
    
    INDEX idx_codigo (codigo),
    INDEX idx_admin_id (admin_id),
    INDEX idx_usado (usado),
    INDEX idx_ativo (ativo),
    
    CONSTRAINT fk_codigo_admin FOREIGN KEY (admin_id) 
        REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_codigo_usado_por FOREIGN KEY (usado_por_usuario) 
        REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- FUNÇÕES E PROCEDURES ÚTEIS
-- ===============================================

DELIMITER //

-- Procedure para gerar novo código único para funcionário
DROP PROCEDURE IF EXISTS GerarCodigoFuncionario//
CREATE PROCEDURE GerarCodigoFuncionario(
    IN p_admin_id INT,
    IN p_funcao ENUM('anotar_pedido', 'fazer_pedido', 'ambos'),
    OUT p_codigo VARCHAR(10)
)
BEGIN
    DECLARE v_codigo VARCHAR(10);
    DECLARE v_exists INT DEFAULT 1;
    DECLARE v_tentativas INT DEFAULT 0;
    
    -- Tentar gerar um código único até 100 tentativas
    WHILE v_exists = 1 AND v_tentativas < 100 DO
        -- Gerar código de 6 dígitos aleatório
        SET v_codigo = LPAD(FLOOR(RAND() * 999999), 6, '0');
        
        -- Verificar se já existe
        SELECT COUNT(*) INTO v_exists
        FROM codigos_funcionarios 
        WHERE codigo = v_codigo;
        
        SET v_tentativas = v_tentativas + 1;
    END WHILE;
    
    -- Se encontrou um código único, inserir na tabela
    IF v_exists = 0 THEN
        INSERT INTO codigos_funcionarios (codigo, admin_id, funcao, ativo)
        VALUES (v_codigo, p_admin_id, p_funcao, 1);
        
        SET p_codigo = v_codigo;
    ELSE
        SET p_codigo = NULL;
    END IF;
END//

-- Procedure para validar e usar código de funcionário
DROP PROCEDURE IF EXISTS ValidarCodigoFuncionario//
CREATE PROCEDURE ValidarCodigoFuncionario(
    IN p_codigo VARCHAR(10),
    IN p_usuario_id INT,
    OUT p_valido TINYINT(1),
    OUT p_admin_id INT,
    OUT p_funcao ENUM('anotar_pedido', 'fazer_pedido', 'ambos')
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    -- Verificar se o código existe e está disponível
    SELECT COUNT(*), admin_id, funcao
    INTO v_count, p_admin_id, p_funcao
    FROM codigos_funcionarios 
    WHERE codigo = p_codigo 
      AND usado = 0 
      AND ativo = 1
    LIMIT 1;
    
    IF v_count > 0 THEN
        -- Marcar código como usado
        UPDATE codigos_funcionarios 
        SET usado = 1, 
            usado_por_usuario = p_usuario_id, 
            data_uso = NOW()
        WHERE codigo = p_codigo;
        
        SET p_valido = 1;
    ELSE
        SET p_valido = 0;
        SET p_admin_id = NULL;
        SET p_funcao = NULL;
    END IF;
END//

DELIMITER ;

-- ===============================================
-- DADOS DE TESTE (OPCIONAL)
-- ===============================================

-- Gerar alguns códigos de teste para o primeiro administrador
-- (Descomente para usar)
/*
SET @admin_id = (SELECT id FROM usuarios WHERE tipo_usuario = 'administrador' LIMIT 1);

CALL GerarCodigoFuncionario(@admin_id, 'anotar_pedido', @codigo1);
CALL GerarCodigoFuncionario(@admin_id, 'fazer_pedido', @codigo2);
CALL GerarCodigoFuncionario(@admin_id, 'ambos', @codigo3);

SELECT CONCAT('Códigos gerados: ', @codigo1, ', ', @codigo2, ', ', @codigo3) AS resultado;
*/

-- ===============================================
-- VERIFICAÇÃO FINAL
-- ===============================================

-- Mostrar estrutura atualizada
DESCRIBE usuarios;

-- Mostrar nova tabela
DESCRIBE codigos_funcionarios;

SELECT 'Atualização concluída com sucesso!' AS status;