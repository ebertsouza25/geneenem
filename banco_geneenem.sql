-- Banco de dados do GeneEnem
--
-- O arquivo não cria nem troca de banco: importe no banco que já existe.
-- Pode ser importado quantas vezes for preciso (usa CREATE TABLE IF NOT EXISTS),
-- mas ele NÃO apaga dados.
--
-- XAMPP: phpMyAdmin > banco "geneenem" > Importar este arquivo.
-- Wasmer: app no wasmer.io > Databases > DB Explorer (Adminer) > Import >
--         envie este arquivo. Basta uma vez; o banco persiste.

-- Conta do aluno. O e-mail é o identificador do login e a senha fica
-- guardada como hash, nunca em texto puro.
CREATE TABLE IF NOT EXISTS `alunos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(80) NOT NULL,
    `email` VARCHAR(180) NOT NULL,
    `senha_hash` VARCHAR(255) NOT NULL,
    `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email_unico` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada simulado concluído gera uma linha. O token vem do formulário e impede
-- que o mesmo envio seja gravado duas vezes (clique repetido ou F5).
CREATE TABLE IF NOT EXISTS `atividades` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `aluno_id` INT UNSIGNED NOT NULL,
    `token` CHAR(64) NOT NULL,
    `tipo` VARCHAR(40) NOT NULL DEFAULT 'simulado_enem',
    `total_questoes` SMALLINT UNSIGNED NOT NULL,
    `acertos` SMALLINT UNSIGNED NOT NULL,
    `erros` SMALLINT UNSIGNED NOT NULL,
    `pontuacao` SMALLINT UNSIGNED NOT NULL,
    `percentual` DECIMAL(5,2) NOT NULL,
    `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token_unico` (`token`),
    KEY `atividade_por_aluno` (`aluno_id`, `criado_em`),
    CONSTRAINT `fk_atividade_aluno`
        FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Respostas de cada questão do simulado, ligadas à atividade.
-- Serve para conferir o desempenho por área sem duplicar dados do aluno.
CREATE TABLE IF NOT EXISTS `respostas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `atividade_id` INT UNSIGNED NOT NULL,
    `numero_questao` TINYINT UNSIGNED NOT NULL,
    `area` VARCHAR(60) NOT NULL,
    `resposta` CHAR(1) NOT NULL,
    `gabarito` CHAR(1) NOT NULL,
    `acertou` TINYINT(1) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `respostas_da_atividade` (`atividade_id`),
    CONSTRAINT `fk_resposta_atividade`
        FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
