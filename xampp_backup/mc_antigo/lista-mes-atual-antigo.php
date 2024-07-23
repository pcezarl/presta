<?php
  
// lista-mes-atual.php
// exibe as prestacoes do mes atual

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";


$p= new tpl("$modelo");
$db=new db;
$ap=new db;


// pega dados do edificio
$ap->query("select * from edificios join aptos on ap_ed=id_edificio where id_apto='$i'");
if($ap->status=="erro")die($ap->erro);

$ed_nome=$ap->get_val("ed_nome");
$ap_num= $ap->get_val("ap_num");

$ap->destroy();

// fim dados ed

$mes=date("m");
$ano=date("Y");


$sql="
select * from prestacoes
join aptos on pr_apto=id_apto
join edificios on ap_ed=id_edificio
where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano' 
";

$db->query($sql);
if($db->status=="erro")die($db->erro);


$cont="
<h1 class='titulo'>Lista de prestações: {$mes_extenso[date("m")]} </h1>
<script type='text/javascript' src='js/lib_opt.js'></script>
<iframe frameborder='0' width='1' height='2' id='opts' name='opts'></iframe>
<!-- listagem dos dados -->
<table border='0' width='100%' align='center' style='font-family:courier;font-size:10pt;border:2px solid #234770' cellspacing='0'>
<tr style='background:black;color:white'>
<th width='5%'>DIA</th>
<th width='25%'>EDIFICIO/APTO</th>

<th width='10%'>VALOR</th>
<th width='10%'>PAGO</th>
<th width='10%'>TIPO</th>
<th>OBS</th>

<th width='120px'>OPÇÕES</th>

</tr>
";

while($dados=mysql_fetch_object($db->result)){
$c++;
$cor=(is_int($c/2))?"corsim":"cornao";

$id=$dados->id_presta;
$ap=$dados->pr_apto;


$pago=($dados->pr_pago=='s')?"":"NÃO PAGA";

$bt_pagar=($dados->pr_pago=='s')?"<img src='images/bt_trans.gif' alt='' title=''  />":"<img src='images/vender.gif' border='0' alt='pagar' title='Marcar como pago' onclick=\"opt('p','$id','$ap')\" style='cursor:pointer; cursor:hand;' />";
$bt_editar="<a href='edita_prestacao.php?i=$id' rel='facebox'><img src='images/bt_editar.gif' alt='editar' title='editar prestação' border='0' /></a>";


switch($dados->pr_tipo){

case "n":
$tipo="Mensalidade";
break;

case "t":
$tipo="Trimestral";
break;

case "s":
$tipo="Semestral";
break;

case "c":
$tipo="Entrega de chaves";
break;


} // fim switch tipo


$valor=mil($dados->pr_valor);

$stamp=strtotime($dados->pr_vencimento);
$data=date("d",$stamp);

$data_pg =(($dados->pr_data_pago=="0000-00-00") || ($dados->pr_data_pago=="") )?"":mydata($dados->pr_data_pago);


$cont.="
<tr  class='$cor'>
<td>$data</td>
<td style='border-left:1px solid black;padding-left:5px'>{$dados->ed_nome} - <b>{$dados->ap_num}</b>  </td>
<td style='border-left:1px solid black;text-align:right'>$valor</td>
<td style='border-left:1px solid black;text-align:center'>$pago $data_pg</td>
<td style='border-left:1px solid black;padding-left:5px;padding-right:3px'>$tipo</td>
<td style='border-left:1px solid black;padding-left:5px'>{$dados->pr_obs}</td>
<td style='border-left:1px solid black;padding-left:5px;text-align:right'>$bt_pagar $bt_editar</td>
</tr>
";

}

// calcula valores pagos e devidos
$db->destroy();
$db=new db;

// total
$db->query("select sum(pr_valor) as valor from prestacoes where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_total=mil($db->get_val("valor"));

// pago
$db->query("select sum(pr_valor) as valor from prestacoes where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano' and pr_pago='s'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_pago=mil($db->get_val("valor"));

// devido
$db->query("select sum(pr_valor) as valor from prestacoes where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano' and pr_pago='n'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_devido=mil($db->get_val("valor"));



$cont.="
</table><br />
<!-- dados informativos financeiros -->
<div style=\"background:#2B3457;color:white;font-family:courier;font-size:10pt;border:2px solid #121625;display:block;width:280px;margin-left:34%;padding:5px \">
<table border='0' width='100%' cellspacing='0'>

<tr>
<td  style='border-bottom:1px solid #788BBE'>Valor total</td>
<td  style='border-bottom:1px solid #788BBE' align='right'>$v_total</td>
</tr>

<tr>
<td  style='border-bottom:1px solid #788BBE'>Total pago</td>
<td  style='border-bottom:1px solid #788BBE' align='right'>$v_pago</td>
</tr>

<tr>
<td>Total devido</td>
<td align='right'>$v_devido</td>
</tr>

</table>
</div>
<div style='text-align:center'><img src='images/fim-tabela.gif' alt=''/></div>
<!-- FIM DA TABELA DAS PRESTAÇÕES -->
";

$p->set("pagina","Detalhes das prestações");
$p->set("conteudo",$cont);
$p->tprint();

?>
