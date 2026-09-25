CREATE DATABASE IF NOT EXISTS PinguInvest_DB;
USE PinguInvest_DB;

-- =========================
-- USUÁRIOS
-- =========================

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    google_id VARCHAR(255) UNIQUE NULL,

    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,

    foto VARCHAR(100),

    assinante BOOLEAN NOT NULL DEFAULT FALSE,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ATIVOS
-- =========================

CREATE TABLE ativos (
    id_ativo INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,

    codigo VARCHAR(15) NOT NULL,
    nome VARCHAR(100) NOT NULL,

    tipo ENUM(
        'ACAO',
        'FII',
        'ETF',
        'CRIPTO',
        'RENDA_FIXA',
        'OUTROS'
    ) NOT NULL,

    quantidade DECIMAL(15,4) NOT NULL,
    preco_medio DECIMAL(15,2) NOT NULL,
    valor_atual DECIMAL(15,2) NOT NULL,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    UNIQUE(id_usuario, codigo)
);

-- =========================
-- MOVIMENTAÇÕES
-- =========================

CREATE TABLE movimentacoes (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,
    id_ativo INT NOT NULL,

    tipo ENUM('COMPRA', 'VENDA') NOT NULL,

    quantidade DECIMAL(15,4) NOT NULL,
    preco_unitario DECIMAL(15,2) NOT NULL,

    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_ativo)
        REFERENCES ativos(id_ativo)
        ON DELETE CASCADE
);

-- =========================
-- HISTÓRICO DA CARTEIRA
-- =========================

CREATE TABLE historico_carteira (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,

    valor_total DECIMAL(15,2) NOT NULL,

    registrado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- =========================
-- VLORIZAÇÃO DE ATIVOS
-- ========================= 
CREATE TABLE valorizacao_ativos (
    id_valorizacao INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,
    id_ativo INT NOT NULL,

    valorizacao_diaria DECIMAL(15,2) NOT NULL,

    data_val TIMESTAMP DEFAULT CURRENT_DATE,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_ativo)
        REFERENCES ativos(id_ativo)
        ON DELETE CASCADE
);