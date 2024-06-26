<?php

// lista-mes-atual.php
// exibe as prestacoes do mes atual

include "lib/var.php";
include "lib/func.php";

$i=limpa($_GET["i"]);

$p= new tpl("$modelo");
$t= new tpl("tpl/tpl_tabela_prestacoes2.html");
$dads=new tpl("tpl/tpl_mini_painel.html");

$db=new db();
$dbt=new db();





$mes=(integer)date("m");
$ano=date("Y");

$cont="<h1 class='titulo'>Prestações: $i de $mes_extenso[$mes] de $ano</h1>";

$dv="$ano-$mes-$i";

$sql="
select * from prestacoes
join aptos on pr_apto=id_apto
join edificios on ap_ed=id_edificio
left join boletos on bo_presta=id_presta
where pr_vencimento='$dv'
";

//die("<pre>".$sql);

$db->query($sql);
if($db->status=="erro")die($db->erro."<hr>".$db->sql);
if($db->rows==0)error(2);


$t->set("mes atual",$mes_extenso[date("m")]);
$t->set("qt presta",$db->rows);

$t->begin_loop("linha");
while($dados=mysql_fetch_object($db->result)){
$c++;
$cor=($c%2)?"corsim":"cornao";

$id=$dados->id_presta;
$ap=$dados->pr_apto;

$pago=($dados->pr_pago=='s')?"":"<span style='color:red'><b>NÃO PAGA</b></span>";

$bt_pagar=($dados->pr_pago=='s')?"<img src='images/bt_trans.gif' alt='' title=''  />":"<img src='images/vender.gif' border='0' alt='pagar' title='Marcar como pago' onclick=\"opt('p','$id','$ap')\" style='cursor:pointer; cursor:hand;' />";
$bt_editar="<a href='edita_prestacao.php?i=$id' rel='facebox'><img src='images/bt_editar.gif' alt='editar' title='editar prestação' border='0' /></a>";


if($dados->bo_ndoc==null){
$bt_boleto="<a href='gera-boleto-unico.php?i=$id' target='_blank'><img src='images/bt_boleto.gif' alt='boleto' title='gerar boleto' border='0' /></a>";
}else{
$bt_boleto="<a href='ver-boleto-unico.php?i=$id' target='_blank'><img src='images/bt_ver_boleto.gif' alt='boleto' title='ver boleto' border='0' /></a>";
}

$dbt->query("select max(pr_num) as idx from prestacoes where pr_tipo='{$dados->pr_tipo}' and pr_apto='{$dados->pr_apto}' ");
//die($dbt->sql);
$max_presta=$dbt->get_val("idx") ;

$mini_tipo= substr($tipo_parcela[$dados->pr_tipo],0,4);
		$tipo="<b><span style='font-size:10pt'>{$dados->ed_nome} - {$dados->ap_num}</span></b> <br /><span style='font-size:7pt;font-family:verdana'>{$dados->pr_num}/$max_presta</span> $mini_tipo";





//$tipo="<span style='font-size:7pt;font-family:verdana'>".$dados->pr_num."/".$max_presta. "</span> " . $tipo_parcela[$dados->pr_tipo];



$valor=mil($dados->pr_valor);

$stamp=strtotime($dados->pr_vencimento);
$data=date("d",$stamp);

$data_pg =(($dados->pr_data_pago=="0000-00-00") || ($dados->pr_data_pago=="") )?"":mydata($dados->pr_data_pago);

$dpr["valor"]         =$valor;
$dpr["data"]          =$data;
$dpr["cor"]           =$cor;
$dpr["nome edificio"] =$dados->ed_nome;
$dpr["num apto"]      =$dados->ap_num;
$dpr["pago"]          =$pago;
$dpr["data_pg"]       =$data_pg;
$dpr["tipo"]          =$tipo;
$dpr["obs"]           =$dados->pr_obs;
$dpr["bt pagar"]      =$bt_pagar;
$dpr["bt editar"]     =$bt_editar;
$dpr["bt boleto"]     =$bt_boleto;

if($dados->bo_ndoc==null){
			$dpr["doc"]='';
		}else{
			$dpr["doc"]="{$dados->bo_ndoc}";
		}


$t->set_loop($dpr) ;

} // fim loop dados
$t->end_loop("linha");

// calcula valores pagos e devidos
$db->reset();

// total
$db->query("select sum(pr_valor) as valor from prestacoes where pr_vencimento='$dv'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_total=mil($db->get_val("valor"));

// pago
$db->query("select sum(pr_valor) as valor from prestacoes where pr_vencimento='$dv' and pr_pago='s'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_pago=mil($db->get_val("valor"));

// devido
$db->query("select sum(pr_valor) as valor from prestacoes where pr_vencimento='$dv' and pr_pago='n'");
if($db->status=="erro")die($db->erro."[ {$db->sql}  ] ");
$v_devido=mil($db->get_val("valor"));

$cont.=$t->tvar();


$dads->set("texto","
<div style=\"color:black;font-family:courier;font-size:10pt;display:block; \">
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
");


$cont.="
<br />
<!-- dados informativos financeiros -->
<table border=0><tr>
<td><img src='charts/chart-mes-atual-dia.php?i=$i' /></td>
<td>
{$dads->tvar()}
</td>
</tr></table>
<div style='text-align:center'><img src='images/fim-tabela.png' alt=''/></div>
<!-- FIM DA TABELA DAS PRESTAÇÕES -->
";

$p->set("pagina","Detalhes das prestações");
$p->set("conteudo",$cont);
$p->tprint();

?>
