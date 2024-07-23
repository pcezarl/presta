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
$dd=@mysql_connect($mysql["host"],$mysql["user"],$mysql["pass"]);
mysql_query("create database $mysql[dados]");
@mysql_close($dd);


define("_erro","<span style='color:red;font-weight:bold;size:12pt'>ERRO</span>");  
define("_ok","<span style='color:green;font-weight:bold;size:12pt'>OK</span>");  

$db=new db();

$db->transact("abre");

echo "Limpando tabela \"Edificios\"...";

$db->query("delete from edificios");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);


echo _ok."<hr />";

//////////////////////////////////////
echo "Limpando tabela \"Clientes\"...";

$db->query("delete from clientes;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);
echo _ok."<hr />";

////////////////////////////////////////////////////

echo "Limpando tabela \"Vendas\"...";

$db->query("delete from vendas;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);
echo _ok."<hr />";

////////////////////////////////////////
echo "Limpando tabela \"Prestações\"...";

$db->query("delete from prestacoes;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);

echo _ok."<hr />";



////////////////////////////////////////
echo "Limpando tabela \"Apartamentos\"...";

$db->query("delete from aptos;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);
echo _ok."<hr />";

////////////////////////////////////////////////
echo "Limpando tabela \"Boletos\"...";

$db->query("delete from boletos;");
if($db->status=="erro")die($db->erro."<hr>".$db->sql);
echo _ok."<hr />";

}


?>
<h2>Limpeza das tabelas do sistema</h2>
<h2>Atenção: todos os dados existentes serão excluidos</h2>
<form action="" method="post">
<input type="checkbox" name="opt" value="ok">Confirmar<br />
<input type="submit" value="Instalar" style="width:100px;height:40px;">
</form>

</body>
</html>
