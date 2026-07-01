CREATE DATABASE teste_db;

USE teste_db;

/* 01/07/2026 */

CREATE TABLE cadastro (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nome_completo VARCHAR(80) NOT NULL UNIQUE,  
    data_de_nascimento DATE,
    sexo VARCHAR(10) NOT NULL,
    nome_materno VARCHAR(80) NOT NULL,
    cpf VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(50) NOT NULL UNIQUE,
    telefone_celular VARCHAR(20) NOT NULL,
    telefone_fixo VARCHAR(20) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    logradouro VARCHAR(100) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    complemento VARCHAR(100) NOT NULL,        
    bairro VARCHAR(50) NOT NULL,
    cidade VARCHAR(50) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    login VARCHAR(6) NOT NULL UNIQUE,
    senha VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    acao VARCHAR(100) NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL
);

INSERT INTO cadastro (login, senha, nome_completo, nome_materno, data_de_nascimento, cep)
VALUES (
    'master',
    '$2y$10$G0z3Z5x8JwK7mN2pQrT9sUvW5uLrKpNqRsT7vW5uLrKpNqRsT7vW5u',
    'Administrador Master',
    'MASTER MOTHER',
    '2000-01-01',
    '00000000'
);

ALTER TABLE logs ADD COLUMN tipo_2fa VARCHAR(50) DEFAULT NULL;