<?php


include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$db= new db;

$id_apto     = limpa($_POST["apto"]);
$id_prop     = limpa($_POST["cliente"]);

$parcelas    = limpa($_POST["prestacao"]);
$chaves      = nformat($_POST["chaves"]);
$dia_chave   = limpa($_POST["dia_chave"]);
$mes_chave   = limpa($_POST["mes_chave"]);
$ano_chave   = limpa($_POST["ano_chave"]);
$vence       = limpa($_POST["vencimento"]);

$valor       = nformat(limpa($_POST["valor"]));
$semestral   = nformat(limpa($_POST["semestre"]));
$trimestral  = nformat(limpa($_POST["trimestre"]));


$db->query("select ap_vendido from aptos where id_apto='$id_apto'");
$status=mysql_fetch_object($db->result);

if($status->ap_vendido=="s")die ("ERRO: ESSE APARTAMENTO JÁ FOI VENDIDO");


$data_chave="$ano_chave-$mes_chave-$vence";

 
$conta_tri=0;
$conta_sem=0;
$conta_presta=0;

 
if($semestral){
$total_sem=floor($parcelas/6);
$valor_total_sem=$total_sem*$semestral;
}

if($trimestral){
$total_tri=floor($parcelas/3);
$valor_total_tri=$total_tri*$trimestral;
}

$valor_total_intermediaria=$valor_total_sem+$valor_total_tri;
$valor_imovel_saldo=$valor-$valor_total_intermediaria;

if($chaves)$valor_imovel_saldo-=$chaves;


$presta=$valor_imovel_saldo/$parcelas;

$data_venda=date("m/d/Y");
 
$db->query("lock table vendas");

$sql="insert into vendas
(venda_apto,venda_prop,venda_data,venda_total,venda_prestacao,venda_qt_presta,venda_trimestral,venda_semestral,venda_chave,venda_data_chave) values
('$id_apto','$id_prop','$data_venda','$valor','$presta','$parcelas','$trimestral','$semestral','$chaves','$data_chave');
" ;


$db->query($sql);
if($db->status=="erro"){
print($db->erro);
print($db->sql);
}
$id_venda=$db->lastid;

$db->query("unlock tables");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
}

$sqlbase="insert into 
prestacoes (pr_venda,pr_apto,pr_prop,pr_valor,pr_vencimento,pr_tipo)
values     ('$id_venda','$id_apto','$id_prop','%s','%s','%s'); ";

// gera lista
// o prazo começa com 0
$_prazo = 0;

// pegamos o dia atual
$_dia   = date('d');
$_mes   = date('m');
$_ano   = date('Y');

// atualiza dados do apto
$db->query("update aptos set ap_prop='$id_prop',ap_vendido='s' where id_apto='$id_apto' ");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
exit();
}


// gera as prestacoes
$db->query("lock table prestacoes write");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
exit();
}

$db->query("start transaction");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
exit();
}


for($i=1;$i <= $parcelas;$i++)
{

$conta_presta++;

// verifica se tem trimestral e conta os meses
if($trimestral)$conta_tri++;

if($semestral)$conta_sem++;

$_ts = mktime(0,0,0,$_mes,$_dia + $_prazo,$_ano);
$_data = date('d/m/Y',$_ts);
$data_vence=date("Y-m-$vence",$_ts);
$_prazo += 30;


$sql=sprintf($sqlbase,"$presta","$data_vence","n");
$db->query("$sql");


if($chaves){
if($data_chave==$data_vence){
$conta_presta++;
$sql=sprintf($sqlbase,"$chaves","$data_vence","c");
$db->query("$sql");
}
}


if($trimestral){
if($conta_tri==3){ 
$conta_presta++;

$sql=sprintf($sqlbase,"$trimestral","$data_vence","t");
$db->query("$sql");
$conta_tri=0;
}
}


if($semestral){
if($conta_sem==6){ 
$conta_presta++;
$sql=sprintf($sqlbase,"$semestral","$data_vence","s");
$db->query("$sql");
$conta_sem=0;
}
}



} // fim loop


$db->query("commit");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
exit();
}

$db->query("unlock tables");
if($db->status=="erro"){
print($db->erro);
print($db->sql);
exit();
}

// fim gera lista

$msg="OK!! apartamento vendido com sucesso";
$cor="#CDFF9F";



?>
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="pt-br" />

<script type="text/javascript" src="js/jquery.js" ></script>
<script type="text/javascript" src="js/jquery.delay.js"></script>

<script type="text/javascript"> 
<!--


$(document).ready(function(){

$("#okk").delay(2000).fadeOut(300);
parent.document.getElementById("myform").reset();

}); //fim on ready

-->
</script>

</head>
<body>
<div style="display:block;width:100%;font-size:14pt  ;background:<?php echo $cor?>" id="okk">
<img src="images/alerta.gif" alt="" /><?php echo $msg ?>
</div>
</body>
</html>
