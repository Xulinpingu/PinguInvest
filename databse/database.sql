CREATE DATABASE IF NOT EXISTS PinguInvest_DB;
USE PinguInvest_DB;

-- =========================
-- USUÁRIOS
-- =========================

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,

    assinante BOOLEAN NOT NULL DEFAULT FALSE,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CARTEIRAS
-- =========================

CREATE TABLE carteiras (
    id_carteira INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT NOT NULL,

    nome VARCHAR(100) NOT NULL DEFAULT 'Carteira Principal',

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- =========================
-- ATIVOS DA CARTEIRA
-- =========================

CREATE TABLE ativos (
    id_ativo INT AUTO_INCREMENT PRIMARY KEY,

    id_carteira INT NOT NULL,

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

    quantidade DECIMAL(12,2) NOT NULL,

    preco_medio DECIMAL(12,2) NOT NULL,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_carteira)
        REFERENCES carteiras(id_carteira)
        ON DELETE CASCADE
);

-- =========================
-- HISTÓRICO DA CARTEIRA
-- =========================

CREATE TABLE historico_carteira (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,

    id_carteira INT NOT NULL,

    valor_total DECIMAL(14,2) NOT NULL,

    data_registro DATE NOT NULL,

    FOREIGN KEY (id_carteira)
        REFERENCES carteiras(id_carteira)
        ON DELETE CASCADE
);

-- =========================
-- MOVIMENTAÇÕES (FUTURO)
-- =========================

CREATE TABLE movimentacoes (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,

    id_ativo INT NOT NULL,

    tipo ENUM(
        'COMPRA',
        'VENDA'
    ) NOT NULL,

    quantidade DECIMAL(12,2) NOT NULL,

    preco_unitario DECIMAL(12,2) NOT NULL,

    data_operacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_ativo)
        REFERENCES ativos(id_ativo)
        ON DELETE CASCADE
);