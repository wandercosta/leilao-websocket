-- Estrutura do banco. Executado automaticamente pelo MySQL na primeira subida do container.
-- Valores em centavos para evitar erros de arredondamento.

SET NAMES utf8mb4;

CREATE TABLE usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(80)  NOT NULL,
    email       VARCHAR(160) NOT NULL UNIQUE,
    senha_hash  VARCHAR(255) NOT NULL,
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE leiloes (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo             VARCHAR(120) NOT NULL,
    descricao          TEXT         NOT NULL,
    imagem             VARCHAR(255) NOT NULL,
    lance_inicial      INT UNSIGNED NOT NULL,
    incremento_minimo  INT UNSIGNED NOT NULL,
    encerra_em         DATETIME     NOT NULL,
    criado_em          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_encerra_em (encerra_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE lances (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leilao_id   INT UNSIGNED NOT NULL,
    usuario_id  INT UNSIGNED NOT NULL,
    valor       INT UNSIGNED NOT NULL,
    criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lance_leilao  FOREIGN KEY (leilao_id)  REFERENCES leiloes (id),
    CONSTRAINT fk_lance_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    INDEX idx_leilao_valor (leilao_id, valor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
