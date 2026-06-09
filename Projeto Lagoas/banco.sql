-- ============================================================
--  PokéCRUD — Script de criação do banco de dados
--  Execute este arquivo no phpMyAdmin ou via terminal MySQL
-- ============================================================


CREATE DATABASE IF NOT EXISTS crud_pokemon
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE crud_pokemon;

-- ------------------------------------------------------------
--  Tabela de usuários
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id        INT          NOT NULL AUTO_INCREMENT,
    nome      VARCHAR(100) NOT NULL,
    email     VARCHAR(150) NOT NULL,
    senha     CHAR(64)     NOT NULL COMMENT 'Hash SHA256 da senha',
    criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Tabela de pokémons
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pokemon (
    id         INT          NOT NULL AUTO_INCREMENT,
    nome       VARCHAR(100) NOT NULL,
    tipo       VARCHAR(50)  NOT NULL,
    nivel DECIMAL(3,1) NOT NULL,
    usuario_id INT          NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_pokemon_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuario(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Usuário de teste
--  Email: admin@email.com
--  Senha: 123456  (SHA256 = 8d969eef6ecad3c29a3a629280e686cf...)
-- ------------------------------------------------------------
INSERT INTO usuario (nome, email, senha) VALUES
(
    'Ash Ketchum',
    'admin@email.com',
    '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'
);

-- ------------------------------------------------------------
--  Pokémons de exemplo vinculados ao usuário acima (id=1)
-- ------------------------------------------------------------
INSERT INTO pokemon (nome, tipo, nivel, usuario_id) VALUES
    ('Interestelar',            'Ficção Científica', 9.5, 1),
    ('Breaking Bad',            'Drama',             10.0, 1),
    ('The Dark Knight',         'Ação',              9.8, 1),
    ('Stranger Things',         'Suspense',          8.9, 1),
    ('O Poderoso Chefão',       'Crime',             10.0, 1);