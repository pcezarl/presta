SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `edificios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `edificios` ;

CREATE  TABLE IF NOT EXISTS `edificios` (
  `id_edificio` INT NOT NULL AUTO_INCREMENT ,
  `ed_nome` VARCHAR(45) NULL DEFAULT NULL ,
  `ed_end` TEXT NULL DEFAULT NULL ,
  `ed_info` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id_edificio`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `clientes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `clientes` ;

CREATE  TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT ,
  `cli_nome` VARCHAR(200) NULL DEFAULT NULL ,
  `cli_email` VARCHAR(45) NULL DEFAULT NULL ,
  `cli_tel` VARCHAR(45) NULL DEFAULT NULL ,
  `cli_cpf` VARCHAR(45) NULL DEFAULT NULL ,
  `cli_data_cadastro` DATE NULL DEFAULT NULL ,
  `cli_obs` TEXT NULL DEFAULT NULL ,
  `cli_rua` VARCHAR(200) NULL DEFAULT NULL ,
  `cli_numero` VARCHAR(20) NULL DEFAULT NULL ,
  `cli_bairro` VARCHAR(200) NULL DEFAULT NULL ,
  `cli_cidade` VARCHAR(200) NULL DEFAULT NULL ,
  `cli_cep` VARCHAR(200) NULL DEFAULT NULL ,
  `cli_estado` VARCHAR(200) NULL DEFAULT NULL ,
  PRIMARY KEY (`id_cliente`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `vendas`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vendas` ;

CREATE  TABLE IF NOT EXISTS `vendas` (
  `id_venda` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `venda_apto` INT NULL DEFAULT NULL ,
  `venda_prop` INT NULL DEFAULT NULL ,
  `venda_data` DATE NULL DEFAULT NULL ,
  `venda_total` DECIMAL NULL DEFAULT NULL ,
  `venda_pago` DECIMAL NULL DEFAULT NULL ,
  `venda_prestacao` INT NULL DEFAULT NULL ,
  `venda_qt_presta` INT NULL DEFAULT NULL ,
  `venda_trimestral` DECIMAL NULL DEFAULT NULL ,
  `venda_semestral` DECIMAL NULL DEFAULT NULL ,
  `venda_chave` DECIMAL NULL DEFAULT NULL ,
  `venda_entrega_chave` DECIMAL NULL DEFAULT NULL ,
  `venda_data_chave` DATE NULL DEFAULT NULL ,
  PRIMARY KEY (`id_venda`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `prestacoes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `prestacoes` ;

CREATE  TABLE IF NOT EXISTS `prestacoes` (
  `id_presta` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `pr_venda` INT NULL DEFAULT NULL ,
  `pr_apto` INT NULL DEFAULT NULL ,
  `pr_prop` INT NULL DEFAULT NULL ,
  `pr_valor` DECIMAL(12,2) NULL DEFAULT NULL ,
  `pr_vencimento` DATE NULL DEFAULT NULL ,
  `pr_pago` ENUM('s', 'n') NOT NULL DEFAULT 'n' ,
  `pr_data_pago` DATE NOT NULL ,
  `pr_tipo` CHAR(1) NOT NULL DEFAULT 'n' ,
  `pr_obs` TEXT NULL DEFAULT NULL ,
  `pr_num` INT NULL ,
  PRIMARY KEY (`id_presta`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `aptos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aptos` ;

CREATE  TABLE IF NOT EXISTS `aptos` (
  `id_apto` INT NOT NULL AUTO_INCREMENT ,
  `ap_num` VARCHAR(10) NULL DEFAULT NULL ,
  `ap_ed` INT NULL DEFAULT NULL ,
  `ap_valor` DECIMAL(16,2) NULL DEFAULT NULL ,
  `ap_prop` INT NULL DEFAULT NULL ,
  `ap_data_compra` DATE NULL DEFAULT NULL ,
  `ap_total_presta` INT NULL DEFAULT NULL ,
  `ap_valor_pago` DECIMAL(16,2) NULL DEFAULT NULL ,
  `ap_entregue` ENUM('s','n') NULL DEFAULT 'n' ,
  `ap_chave` DATE NULL DEFAULT NULL ,
  `ap_obs` TEXT NULL DEFAULT NULL ,
  `ap_vendido` ENUM('s','n') NULL DEFAULT 'n' ,
  PRIMARY KEY (`id_apto`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `boletos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `boletos` ;

CREATE  TABLE IF NOT EXISTS `boletos` (
  `id_boleto` INT NOT NULL AUTO_INCREMENT ,
  `bo_apto` INT NULL DEFAULT NULL ,
  `bo_presta` INT NULL DEFAULT NULL ,
  `bo_prop` INT NULL DEFAULT NULL ,
  `bo_valor` DECIMAL(12,2) NULL DEFAULT NULL ,
  `bo_data_emissao` DATE NULL DEFAULT NULL ,
  `bo_data_pagto` DATE NULL DEFAULT NULL ,
  `bo_data_vence` DATE NULL DEFAULT NULL ,
  `bo_num_presta` INT NULL DEFAULT NULL ,
  `bo_ndoc` VARCHAR(200) NULL DEFAULT NULL ,
  `bo_nnum` VARCHAR(200) NULL DEFAULT NULL ,
  `bo_pago` CHAR(1) NOT NULL DEFAULT 'n' ,
  PRIMARY KEY (`id_boleto`) )
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
