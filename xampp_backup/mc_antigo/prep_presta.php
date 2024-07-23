<?php
// prep_presta.php
// prepara as parcelas de entrada, trimestrais e semestrais

include "lib/var.php";
include "lib/func.php";


$pr= new parcela();

session_start();

$tipos["ent"]="entrada";
$tipos["sem"]="semestral";
$tipos["tri"]="trimestral";
$tipos["int"]="intermediaria";


$div["ent"]="l_ent";
$div["sem"]="l_sem";
$div["tri"]="l_tri";
$div["int"]="l_int";


$tipo=limpa($_GET["i"]);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo strtoupper($tipos[$tipo]) ?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript">
var sair = true;
window.onbeforeunload = function() {
if (sair) {
return "Para sair, por favor clique no botão FINALIZAR!"
}
}
function naosair() {
sair=false;
}

function validar(){
valor=document.getElementById("valor").value;
dia=document.getElementById("dia").value;
ano=document.getElementById("ano").value;

naosair();

if((valor=='') || (dia=='') || (ano=="") || (parseInt(dia)>31) || (parseInt(ano)<2000)  ){
alert("ERRO: preenchimento incorreto");

}else{
document.getElementById('ff').submit();
}
} // fim validar()

function verif(){

vv=window.opener.document.getElementById("dia_inicial");
document.getElementById("dia").value=vv.value;
}


</script>
<style>
input,select{
height: 18px;
}
</style>

</head>
<body style="background-color:#FDF093  ;background-image:url('images/fn_am.jpg')" onload="verif()">
<div style="color:white;background:#35301E;padding-left:20px;font-weight:bold;font-family:verdana;">tipo de parcela: <?php echo strtoupper($tipos[$tipo]) ?></div>
<table border="1" cellpadding="2" cellspacing="0" width="100%">
<tr><td colspan="2">
<form method="post" action="" name="ff" id="ff">
Valor:<input type="text" name="valor" id="valor" /><br />
Data:<input type="text" size="3" maxlength="3" name="dia" id="dia" />/
<select name="mes" id="mes">
		  <option value="01">Janeiro</option>
		  <option value="02">Fevereiro</option>
		  <option value="03">Março</option>
		  <option value="04">Abril</option>
		  <option value="05">Maio</option>
		  <option value="06">Junho</option>
		  <option value="07">Julho</option>
		  <option value="08">Agosto</option>
		  <option value="09">Setembro</option>
		  <option value="10">Outubro</option>
		  <option value="11">Novembro</option>
		  <option value="12">Dezembro</option>
		</select>/
<input type="text" name="ano" id="ano" size="4" maxlength="4" /><br />
<input type="hidden" name="parc" id="parc" value="<?php echo $parcelas?>" />
<input type="hidden" name="ctl" id="ctl" value="<?php echo mktime()?>" />
</form>



</td></tr>
<tr>
<td align="center">
<a href="javascript:void(0)" onclick="validar()" class="aqua2">Adicionar</a>
</td>
<td align="center">
<a href="destroy_session.php" class="aqua2" onclick="naosair()">Finalizar</a>
</td>
</tr>
</table>  <br />

<div style="display:block;overflow:auto;height:120px;border:1px solid #B05C00;background:#F9EE64">
<pre>
<?php
// processa dados
if($_POST["ctl"]){


$data=strtotime("$_POST[ano]-$_POST[mes]-$_POST[dia]") ;
$val=mil($_POST["valor"]);
$data_h="$_POST[dia]/$_POST[mes]/$_POST[ano] - R\$$val<br />";

$pr->add(nformat($_POST["valor"]),$data);

$_SESSION["valor_total"]+=nformat($_POST["valor"]);

$_SESSION[$tipo][]=$pr->get();

$tt=count($_SESSION[$tipo]);

$code= str_encode($_SESSION[$tipo]);

$vtt=mil($_SESSION["valor_total"]);

echo <<<OOO
total de parcelas: $tt
valor total: R\$ $vtt
<script>
opener.document.getElementById("$div[$tipo]").innerHTML+="$data_h";
opener.document.getElementById("$tipos[$tipo]").value="$code";
</script>
OOO;

} // fim proc
else{
$_SESSION["valor_total"]=0;
}

?>
</pre>
</div>
</body>
</html>
