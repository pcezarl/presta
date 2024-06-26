<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<meta name="author" content="">
<meta http-equiv="Reply-to" content="@.com">
<meta name="generator" content="PhpED 5.8">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="creation-date" content="01/01/2009">
<meta name="revisit-after" content="15 days">
<title>Instalação</title>

</head>
<body>

<?php
// cria as tabelas no bd

if($_POST["opt"]=="ok"){

include 'lib/var.php';
//error_reporting(E_ALL);
$dd=@mysql_connect($mysql["host"],$mysql["user"],$mysql["pass"]);
mysql_query("create database $mysql[dados]");
@mysql_close($dd);


define("_erro","<span style='color:red;font-weight:bold;size:12pt'>ERRO</span>");  
define("_ok","<span style='color:green;font-weight:bold;size:12pt'>OK</span>");  

$db=new db();

$db->transact("abre");

echo "Criando tabela \"Edificios\"...";

$db->query("DROP TABLE IF EXISTS edificios");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("CREATE  TABLE IF NOT EXISTS `edificios` (
  `id_edificio` INT NOT NULL AUTO_INCREMENT ,
  `ed_nome` VARCHAR(45) NULL ,
  `ed_end` TEXT NULL ,
  `ed_info` TEXT NULL ,
  PRIMARY KEY (`id_edificio`) )
ENGINE = InnoDB;
");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";

//////////////////////////////////////
echo "Criando tabela \"Clientes\"...";

$db->query("DROP TABLE IF EXISTS clientes;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("CREATE  TABLE IF NOT EXISTS `clientes` (
  `id_cliente` INT NOT NULL AUTO_INCREMENT ,
  `cli_nome` VARCHAR(200) NULL ,
  `cli_email` VARCHAR(45) NULL ,
  `cli_tel` VARCHAR(45) NULL ,
  `cli_cpf` VARCHAR(45) NULL ,
  `cli_data_cadastro` DATE NULL ,
  `cli_obs` TEXT NULL ,
  `cli_rua` VARCHAR(200) NULL ,
  `cli_numero` VARCHAR(20) NULL ,
  `cli_bairro` VARCHAR(200) NULL ,
  `cli_cidade` VARCHAR(200) NULL ,
  `cli_cep` VARCHAR(200) NULL ,
  `cli_estado` VARCHAR(200) NULL ,
  PRIMARY KEY (`id_cliente`) )
ENGINE = InnoDB;
");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";

////////////////////////////////////////////////////

echo "Criando tabela \"Vendas\"...";

$db->query("DROP TABLE IF EXISTS vendas;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("CREATE  TABLE IF NOT EXISTS `vendas` (
  `id_venda` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `venda_apto` INT NULL ,
  `venda_prop` INT NULL ,
  `venda_data` DATE NULL ,
  `venda_total` DECIMAL NULL ,
  `venda_pago` DECIMAL NULL ,
  `venda_prestacao` INT NULL ,
  `venda_qt_presta` INT NULL ,
  `venda_trimestral` DECIMAL NULL ,
  `venda_semestral` DECIMAL NULL ,
  `venda_chave` DECIMAL NULL ,
  `venda_entrega_chave` DECIMAL NULL ,
  `venda_data_chave` DATE NULL ,
  PRIMARY KEY (`id_venda`) )
ENGINE = InnoDB;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";

////////////////////////////////////////
echo "Criando tabela \"Prestações\"...";

$db->query("DROP TABLE IF EXISTS prestacoes;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("CREATE  TABLE IF NOT EXISTS `prestacoes` (
  `id_presta` BIGINT NOT NULL AUTO_INCREMENT ,
  `pr_venda` INT NULL ,
  `pr_apto` INT NULL ,
  `pr_prop` INT NULL ,
  `pr_valor` DECIMAL(12,2) NULL ,
  `pr_vencimento` DATE NULL ,
  `pr_pago` ENUM('s', 'n') NOT NULL DEFAULT 'n' ,
  `pr_data_pago` DATE NOT NULL ,
  `pr_tipo` CHAR(1) NOT NULL DEFAULT 'n' ,
  `pr_obs` TEXT NULL ,
pr_num int,
  PRIMARY KEY (`id_presta`),
unique(id_presta)
 )

ENGINE = InnoDB;

");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";



////////////////////////////////////////
echo "Criando tabela \"Apartamentos\"...";

$db->query("DROP TABLE IF EXISTS aptos;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("
CREATE  TABLE IF NOT EXISTS `aptos` (
  `id_apto` INT NOT NULL AUTO_INCREMENT ,
  `ap_num` VARCHAR(10) NULL ,
  `ap_ed` INT NULL ,
  `ap_valor` DECIMAL(16,2) NULL ,
  `ap_prop` INT NULL ,
  `ap_data_compra` DATE NULL ,
  `ap_total_presta` INT NULL ,
  `ap_valor_pago` DECIMAL(16,2) NULL ,
  `ap_entregue` ENUM('s','n') NULL DEFAULT 'n' ,
  `ap_chave` DATE NULL ,
  `ap_obs` TEXT NULL ,
  `ap_vendido` ENUM('s','n') NULL DEFAULT 'n' ,
  PRIMARY KEY (`id_apto`) )
ENGINE = InnoDB;


          ");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";

////////////////////////////////////////////////
echo "Criando tabela \"Boletos\"...";

$db->query("DROP TABLE IF EXISTS boletos;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

$db->query("CREATE  TABLE IF NOT EXISTS `boletos` (
  `id_boleto` INT NOT NULL AUTO_INCREMENT ,
  `bo_apto` INT NULL ,
  `bo_presta` INT NULL ,
  `bo_prop` INT NULL ,
  `bo_valor` DECIMAL(12,2) NULL ,
  `bo_data_emissao` DATE NULL ,
  `bo_data_pagto` DATE NULL ,
  `bo_data_vence` DATE NULL ,
  `bo_num_presta` INT NULL ,
  `bo_ndoc` VARCHAR(200) NULL ,
  `bo_nnum` VARCHAR(200) NULL ,
  `bo_pago` CHAR(1) NULL ,
  PRIMARY KEY (`id_boleto`) )
ENGINE = InnoDB;
");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";

}


?>
<h2>Criação das tabelas do sistema</h2>
<h2>Atenção: todos os dados existentes serão excluidos</h2>
<form action="" method="post">
<input type="checkbox" name="opt" value="ok">Confirmar<br />
<input type="submit" value="Instalar" style="width:100px;height:40px;">
</form>

</body>
</html>
