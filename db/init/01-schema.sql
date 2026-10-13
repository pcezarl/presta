-- Schema atual do sistema, reconstruido a partir do codigo em 2026-07-31.
--
-- O tabelas.sql da raiz esta desatualizado: nao tem a tabela `contas` nem as
-- colunas `boletos.conta_id` e `boletos.remessa_id`, todas em uso no codigo.
-- Este arquivo reflete o banco como ele realmente e hoje, ANTES da migracao do
-- ASA (que roda em seguida, a partir de db/migrations/).

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- edificios
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `edificios` (
  `id_edificio` INT NOT NULL AUTO_INCREMENT,
  `ed_nome` VARCHAR(45) NULL DEFAULT NULL,
  `ed_end` TEXT NULL DEFAULT NULL,
  `ed_info` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_edificio`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- clientes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT,
  `cli_nome` VARCHAR(200) NULL DEFAULT NULL,
  `cli_email` VARCHAR(45) NULL DEFAULT NULL,
  `cli_tel` VARCHAR(45) NULL DEFAULT NULL,
  `cli_cpf` VARCHAR(45) NULL DEFAULT NULL,
  `cli_data_cadastro` DATE NULL DEFAULT NULL,
  `cli_obs` TEXT NULL DEFAULT NULL,
  `cli_rua` VARCHAR(200) NULL DEFAULT NULL,
  `cli_numero` VARCHAR(20) NULL DEFAULT NULL,
  `cli_bairro` VARCHAR(200) NULL DEFAULT NULL,
  `cli_cidade` VARCHAR(200) NULL DEFAULT NULL,
  `cli_cep` VARCHAR(200) NULL DEFAULT NULL,
  `cli_estado` VARCHAR(200) NULL DEFAULT NULL,
  PRIMARY KEY (`id_cliente`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- vendas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendas` (
  `id_venda` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venda_apto` INT NULL DEFAULT NULL,
  `venda_prop` INT NULL DEFAULT NULL,
  `venda_data` DATE NULL DEFAULT NULL,
  `venda_total` DECIMAL NULL DEFAULT NULL,
  `venda_pago` DECIMAL NULL DEFAULT NULL,
  `venda_prestacao` INT NULL DEFAULT NULL,
  `venda_qt_presta` INT NULL DEFAULT NULL,
  `venda_trimestral` DECIMAL NULL DEFAULT NULL,
  `venda_semestral` DECIMAL NULL DEFAULT NULL,
  `venda_chave` DECIMAL NULL DEFAULT NULL,
  `venda_entrega_chave` DECIMAL NULL DEFAULT NULL,
  `venda_data_chave` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id_venda`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- prestacoes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `prestacoes` (
  `id_presta` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pr_venda` INT NULL DEFAULT NULL,
  `pr_apto` INT NULL DEFAULT NULL,
  `pr_prop` INT NULL DEFAULT NULL,
  `pr_valor` DECIMAL(12,2) NULL DEFAULT NULL,
  `pr_vencimento` DATE NULL DEFAULT NULL,
  `pr_pago` ENUM('s','n') NOT NULL DEFAULT 'n',
  `pr_data_pago` DATE NOT NULL,
  `pr_tipo` CHAR(1) NOT NULL DEFAULT 'n',
  `pr_obs` TEXT NULL DEFAULT NULL,
  `pr_num` INT NULL,
  PRIMARY KEY (`id_presta`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- aptos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `aptos` (
  `id_apto` INT NOT NULL AUTO_INCREMENT,
  `ap_num` VARCHAR(10) NULL DEFAULT NULL,
  `ap_ed` INT NULL DEFAULT NULL,
  `ap_valor` DECIMAL(16,2) NULL DEFAULT NULL,
  `ap_prop` INT NULL DEFAULT NULL,
  `ap_data_compra` DATE NULL DEFAULT NULL,
  `ap_total_presta` INT NULL DEFAULT NULL,
  `ap_valor_pago` DECIMAL(16,2) NULL DEFAULT NULL,
  `ap_entregue` ENUM('s','n') NULL DEFAULT 'n',
  `ap_chave` DATE NULL DEFAULT NULL,
  `ap_obs` TEXT NULL DEFAULT NULL,
  `ap_vendido` ENUM('s','n') NULL DEFAULT 'n',
  PRIMARY KEY (`id_apto`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- contas — ausente do tabelas.sql, reconstruida a partir do uso no codigo
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `contas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `razao` VARCHAR(200) NULL DEFAULT NULL,
  `cnpj` VARCHAR(45) NULL DEFAULT NULL,
  `agencia` VARCHAR(20) NULL DEFAULT NULL,
  `conta` VARCHAR(20) NULL DEFAULT NULL,
  `data` DATE NULL DEFAULT NULL,
  `info` TEXT NULL DEFAULT NULL,
  `banco` VARCHAR(10) NULL DEFAULT NULL,
  `acessorio` VARCHAR(45) NULL DEFAULT NULL,
  `cod_cliente` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- boletos — conta_id e remessa_id faltam no tabelas.sql
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `boletos` (
  `id_boleto` INT NOT NULL AUTO_INCREMENT,
  `bo_apto` INT NULL DEFAULT NULL,
  `bo_presta` INT NULL DEFAULT NULL,
  `bo_prop` INT NULL DEFAULT NULL,
  `bo_valor` DECIMAL(12,2) NULL DEFAULT NULL,
  `bo_data_emissao` DATE NULL DEFAULT NULL,
  `bo_data_pagto` DATE NULL DEFAULT NULL,
  `bo_data_vence` DATE NULL DEFAULT NULL,
  `bo_num_presta` INT NULL DEFAULT NULL,
  `bo_ndoc` VARCHAR(200) NULL DEFAULT NULL,
  `bo_nnum` VARCHAR(200) NULL DEFAULT NULL,
  `bo_pago` CHAR(1) NOT NULL DEFAULT 'n',
  `conta_id` INT NULL DEFAULT NULL,
  `remessa_id` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_boleto`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- Indices do item 5 do light refactoring — baratos e uteis desde ja.
CREATE INDEX `ix_prestacoes_vencimento` ON `prestacoes` (`pr_vencimento`);
CREATE INDEX `ix_prestacoes_apto`       ON `prestacoes` (`pr_apto`);
CREATE INDEX `ix_boletos_presta`        ON `boletos` (`bo_presta`);
CREATE INDEX `ix_boletos_conta`         ON `boletos` (`conta_id`);
CREATE INDEX `ix_aptos_ed`              ON `aptos` (`ap_ed`);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
