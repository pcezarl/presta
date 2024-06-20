<?php

	// lista-mes-atual.php
	// exibe as prestacoes do mes atual

	include "lib/var.php";
	include "lib/func.php";



	$p= new tpl("$modelo");
	$t= new tpl("tpl/tpl_tabela_prestacoes2.html");

	// painel com estatisticas
	$stat=new tpl("tpl/tpl_mini_painel.html");

	$db= new db();
	$ap= new db();
	$dbt=new db();


	// pega dados do edificio
	$ap->query("select * from edificios join aptos on ap_ed=id_edificio where id_apto='$i'");
	if($ap->status=="erro")die($ap->erro);

	$ed_nome=$ap->get_val("ed_nome");
	$ap_num= $ap->get_val("ap_num");

	$ap->reset();

	// fim dados ed

	$mes=(integer)date("m");
	$ano=date("Y");

	// separa por dias de venc
	$sql="
	select distinct(day(pr_vencimento)) as diav from prestacoes
	where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
	order by day(pr_vencimento)
	";

	$cont="<h1 class='titulo'>Prestações: $mes_extenso[$mes] de $ano</h1>";

	$db->query($sql);
	if($db->status=="erro")die($db->erro);


	while($ldia=mysql_fetch_object($db->result)){

		$ldias.="<a href='lista-mes-atual-dia.php?i={$ldia->diav}' style='display:inline;padding-left:15px;padding-right:15px;margin:3px;padding-top:4px' class='cinza'>{$ldia->diav}</a>";

	}

	$cont.="dias de vencimento neste mes:$ldias";

	$db->reset();
	// fim dias venc

	$sql="
	select * from prestacoes
	join aptos on pr_apto=id_apto
	join edificios on ap_ed=id_edificio
	left join boletos on bo_presta=id_presta
	where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
	order by day(pr_vencimento)
	";

	$db->query($sql);
	if($db->status=="erro")die($db->erro);

	$t->set("mes atual",$mes_extenso[(integer)date("m")]);
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
		$max_presta=$dbt->get_val("idx") ;

		$mini_tipo= substr($tipo_parcela[$dados->pr_tipo],0,4);
		$tipo="<b><span style='font-size:10pt'>{$dados->ed_nome} - {$dados->ap_num}</span></b> <br /><span style='font-size:7pt;font-family:verdana'>{$dados->pr_num}/$max_presta</span> $mini_tipo";

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

	$cont.=$t->tvar();
	$p->set("pagina","Detalhes das prestações");
	$p->set("conteudo",$cont);
	$p->tprint();

?>
