<?php
  
// apaga apto e suas prestas

include "lib/var.php";
include "lib/func.php";


$i=limpa($_GET["i"]);
$db = new db;

// verifica se o apto existe
$db->query("select * from aptos where id_apto='$i'");
if($db->rows==0)error(4);

// apaga apto
$db->query("delete from aptos where id_apto='$i'");

// apaga prestacoes
$db->query("delete from prestacoes where pr_apto='$i'");

// apaga venda
$db->query("delete from vendas where venda_apto='$i'");

header("location:index.php");

?>


