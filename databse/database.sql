CREATE DATABASE PinguInvest_DB IF NOT EXISTS

USE PinguInvest_DB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    assinante BIT NOT NULL
);

CREATE TABLE carteira (
    id_carteira INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);


CREATE TABLE ativos (
    id_ativo INT AUTO_INCREMENT PRIMARY KEY,

    id_carteira INT NOT NULL,

    codigo_ativo VARCHAR(10) NOT NULL,
    nome_ativo VARCHAR(100),

    quantidade DECIMAL(10,2) NOT NULL,
    preco_medio DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_carteira)
        REFERENCES carteira(id_carteira)
        ON DELETE CASCADE
);