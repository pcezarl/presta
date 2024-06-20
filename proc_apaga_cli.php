<?php
  
////// apaga cliente

include "lib/var.php";
include "lib/func.php";

$i=limpa($_GET["i"]);


$db=new db();
$db->query("set autocommit=0");
$db->transact("abre");

// apaga cliente
$db->query("delete from clientes where id_cliente='$i'");
if($db->status=="erro"){
$erro=$db->erro;
$db->transact("cancela");
die("erro ao excluir prestacoes<hr>$erro");
}


// apaga prestacoes
$db->query("delete from prestacoes where pr_prop='$i'");
if($db->status=="erro"){
$erro=$db->erro;
$db->transact("cancela");
die("erro ao excluir prestacoes<hr>$erro");
}

// apaga boletos
$db->query("delete from boletos where bo_prop='$i'");
if($db->status=="erro"){
$erro=$db->erro;
$db->transact("cancela");
die("erro ao excluir boletos<hr>$erro");
}


// apaga vendas
$db->query("delete from vendas where venda_prop='$i'");
if($db->status=="erro"){
$erro=$db->erro;
$db->transact("cancela");
die("erro ao excluir vendas<hr>$erro");
}

// atualiza aptos
$db->query("update aptos set ap_vendido='n' where ap_prop='$i'");
if($db->status=="erro"){
$erro=$db->erro;
$db->transact("cancela");
die("erro ao reconfigurar aptos<hr>$erro");
}

$db->transact("salva");


header("location:lista-clientes.php");

?>
