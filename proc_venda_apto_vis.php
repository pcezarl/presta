<?php


include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

session_start();
unset($_SESSION["tri"]);
unset($_SESSION["ent"]);
unset($_SESSION["sem"]);
unset($_SESSION["valor_total"]);
session_destroy();

$db= new db;

$parcelas    = limpa($_POST["prestacao"]);
$chaves      = nformat($_POST["chaves"]);
$dia_chave   = limpa($_POST["dia_chave"]);
$mes_chave   = limpa($_POST["mes_chave"]);
$ano_chave   = limpa($_POST["ano_chave"]);
$vence       = limpa($_POST["vencimento"]);

$valor       = nformat(limpa($_POST["valor"]));

$entrada     = limpa($_POST["entrada"]);
$semestral   = limpa($_POST["semestral"]);
$trimestral  = limpa($_POST["trimestral"]);
$intermediaria  = limpa($_POST["intermediaria"]);


$apto        = limpa($_POST["apto"]);


$saldo_imovel=$valor;

$data_chave="$vence/$mes_chave/$ano_chave";


if($chaves)$saldo_imovel-=$chaves;


// processa entrada
if($entrada){

$en=str_decode($entrada);
$en=array_simply($en);

$t_entrada="<table border='1' cellpadding='1' cellspacing='0' width='100%'>";
foreach($en as $v){
$presta=mil($v["valor"]);
$data_vence=date("d/m/Y",$v["data"]);
$t_entrada.="<tr><td style='border-right:none'>$data_vence</td><td style='text-align:right;border-left:none'>R\$ $presta</td></tr>\n";

$total_entrada+=$v["valor"];
} // fim loop foreach
$tt_ent=mil($total_entrada);
$t_entrada.="<tr style='color:white;background:black'><td style='border-right:none'>Total:</td><td style='text-align:right;border-left:none'>R\$ $tt_ent</td></tr></table>";
// calcula valor do imovel
$saldo_imovel-=$total_entrada;
} // fim entrada

// processa semestrais
if($semestral){
$en=str_decode($semestral);
$en=array_simply($en);

$t_semestral="<table border='1' cellpadding='1' cellspacing='0' width='100%'>";
foreach($en as $v){
$presta=mil($v["valor"]);
$data_vence=date("d/m/Y",$v["data"]);
$t_semestral.="<tr><td style='border-right:none'>$data_vence</td><td style='text-align:right;border-left:none'>R\$ $presta</td></tr>\n";

$total_semestral+=$v["valor"];
} // fim loop foreach
$tt_ent=mil($total_semestral);
$t_semestral.="<tr style='color:white;background:black'><td style='border-right:none'>Total:</td><td style='text-align:right;border-left:none'>R\$ $tt_ent</td></tr></table>";

// calcula valor do imovel
$saldo_imovel-=$total_semestral;


} // fim semestral

// processa tirmestrais
if($trimestral){
$en=str_decode($trimestral);
$en=array_simply($en);

$t_trimestral="<table border='1' cellpadding='1' cellspacing='0' width='100%'>";
foreach($en as $v){
$presta=mil($v["valor"]);
$data_vence=date("d/m/Y",$v["data"]);
$t_trimestral.="<tr><td style='border-right:none'>$data_vence</td><td style='text-align:right;border-left:none'>R\$ $presta</td></tr>\n";

$total_trimestral+=$v["valor"];

} // fim loop foreach
$tt_ent=mil($total_trimestral);
$t_trimestral.="<tr style='color:white;background:black'><td style='border-right:none'>Total:</td><td style='text-align:right;border-left:none'>R\$ $tt_ent</td></tr></table>";

// calcula valor do imovel
$saldo_imovel-=$total_trimestral;

} // fim semestral

// processa intermediarias
if($intermediaria){
$en=str_decode($intermediaria);
$en=array_simply($en);

$t_intermediaria="<table border='1' cellpadding='1' cellspacing='0' width='100%'>";
foreach($en as $v){
$presta=mil($v["valor"]);
$data_vence=date("d/m/Y",$v["data"]);
$t_intermediaria.="<tr><td style='border-right:none'>$data_vence</td><td style='text-align:right;border-left:none'>R\$ $presta</td></tr>\n";

$total_intermediaria+=$v["valor"];

} // fim loop foreach
$tt_ent=mil($total_intermediaria);
$t_intermediaria.="<tr style='color:white;background:black'><td style='border-right:none'>Total:</td><td style='text-align:right;border-left:none'>R\$ $tt_ent</td></tr></table>";

// calcula valor do imovel
$saldo_imovel-=$total_intermediaria;

} // fim intermediaria






// calcula valor da parcela base
$parcela_base=$saldo_imovel/$parcelas;
settype($parcela_base,"float");


// chega de fazer contas
// agora vamos pegar as infos do apto

$sql="select * from aptos join edificios on ap_ed=id_edificio where id_apto=$apto";
$db->query($sql);
$ap_info=$db->data_object;

?>
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="pt-br" />
</head>
<body>
<h1 style='font-familt:verdana;font-size:14pt'>DEMONSTRATIVO DE FINANCIAMENTO</h1>
<span style='font-size:7pt;font-weight:normal'><?php echo date("d/m/Y - H:i") ?>  </span><br /><br />

<div style='font-family:verdana;font-size:12pt;border:1px solid silver;width:80%;display:block;padding:3px'>
Edificio <?php echo $ap_info->ed_nome?>, apartamento <?php echo $ap_info->ap_num?> (R$ <?php echo mil($ap_info->ap_valor) ?> )<br />
</div><br />


<table border='0' style="border:2px solid #3C4A84;font-family:courier;font-size:8pt;color:black" cellspacing="0" cellpadding="3" width="100%">
<tr style="background:#525252;color:white" >
<th>ENTRADA</th>
<th>INTERMEDIARIAS</th>
<th>TRIMESTRAIS</th>
<th>SEMESTRAIS</th>
</tr>

<tr>
<td valign="top" style="border:2px solid #C0C0C0"><?php echo $t_entrada ?>&nbsp;</td>
<td valign="top" style="border:2px solid #C0C0C0"><?php echo $t_intermediaria ?>&nbsp;</td>
<td valign="top" style="border:2px solid #C0C0C0"><?php echo $t_trimestral ?>&nbsp;</td>
<td valign="top" style="border:2px solid #C0C0C0"><?php echo $t_semestral ?>&nbsp;</td>
</tr>
</table>
<br /><br />
<table border="1" cellpadding="1" cellspacing="0">
<tr>
<td>Chaves para <?php echo $data_chave?> no valor de <b>R$ <?php echo mil($chaves)?></b></td>
</tr>
<tr>
<td> Valor base das prestações mensais (<?php echo $parcelas?> parcelas) :<b>R$ <?php echo mil($parcela_base)?></b></td>
</tr>
</table>

</body>
</html>
