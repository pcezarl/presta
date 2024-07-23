<?php
// proc_edita_presta.php  
// grava as alteracoes da prestacao 


header ("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$db=new db;

$i=limpa($_POST["id"]);
$ap=limpa($_POST["ap"]);
$valor=nformat(limpa($_POST["valor"]));
$data_pago=(limpa($_POST["pagto"]!=""))?data2mysql(limpa($_POST["pagto"])):"0000-00-00";
$data_vence=data2mysql(limpa($_POST["vencimento"]));
$pago=limpa($_POST["pago"]);
$tipo=limpa($_POST["tipo"]);
$obs=limpa($_POST["obs"]);
$rd=limpa($_POST["rd"]);




$sql="update prestacoes set
pr_valor='$valor',
pr_vencimento='$data_vence',
pr_data_pago='$data_pago',
pr_pago='$pago',
pr_tipo='$tipo',
pr_obs='$obs'
where id_presta='$i'
 ";

$db->query($sql);

if($db->status=="erro")die($db->erro);

header("location:$rd");

?>
