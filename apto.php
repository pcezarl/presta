<?php

	////////////
	// informacoes do apartamento
	///////////

	include "lib/var.php";
	include "lib/func.php";

	$i=limpa($_GET["i"]);

	$pg = new tpl("$modelo");
	$pn = new tpl("tpl/tpl_apto.html");
	$db = new db;
	$tm = new db;



	$sql="select * from aptos
	left join edificios on id_edificio=ap_ed
	left join clientes on id_cliente=ap_prop
	left join vendas on venda_apto=id_apto
	where id_apto='$i'";

	$db->query($sql);
	if($db->status=="erro")die($db->erro);
	if($db->rows==0)die("<h1>SERVER ERROR!!</h1>Não foi possível criar a Implementação da Interface de Classes.<br />
		<i>Isso <u>pode</u> ter ocorrido por causa de acesso indevido.<br /><br /><hr />
		$_SERVER[SERVER_SIGNATURE]
		");

	$dados=mysql_fetch_object($db->result);

	$cont="<h2 class='titulo'>Detalhes de apartamento: {$dados->ed_nome}, {$dados->ap_num}</h2>";

	// dados gerais
	$pn->set("edificio",   $dados->ed_nome);
	$pn->set("prop_nome",  $dados->cli_nome);
	$pn->set("valor",      mil($dados->ap_valor));
	$pn->set("id_prop",    $dados->id_cliente);
	$pn->set("id",$dados->id_apto);


	$vnd=($dados->ap_vendido=='s')?"<a href=\"javascript:void(0)\" class=\"aqua4\" onclick=\"dispo('{$dados->id_apto}')\">remover venda</a>":
	"<a href=\"vender.php?i={$dados->id_apto}\" class=\"aqua2\" >Vender</a>";
	$pn->set("bt venda",$vnd);

	$entregue=($dados->ap_entregue=='s')?"SIM":"NÃO <div style='display:inline;float:right;clear:both'><a href='javascript:void(dispo(\"$i\"))'   class='aqua4'>entregar</a></div>";
	$pn->set("entregue",$entregue);

	//  informativo

	// conta parcelas
	$tm->query("select count(*) as qt from prestacoes where pr_tipo='n' and pr_apto='$i'");
	$pn->set("total_presta",$tm->get_val("qt"));


	// conta entradas
	$tm->query("select count(*) as qt from prestacoes where pr_tipo='e' and pr_apto='$i'");
	$pn->set("total_entrada",$tm->get_val("qt"));



	// conta trimestrais
	$tm->query("select count(*) as qt from prestacoes where pr_tipo='t' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"qt");
	$pn->set("total_trimestral",$qt);

	// conta semestrais
	$tm->query("select count(*) as qt from prestacoes where pr_tipo='s' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"qt");
	$pn->set("total_semestral",$qt);


	//pega valor prestacao normal
	$tm->query("select pr_valor as valor from prestacoes where pr_tipo='n' and pr_apto='$i' limit 1");
	$r=$tm->result;
	$qt=mysql_result($r,0,"valor");
	$pn->set("valor_presta",  mil($qt));

	//pega valor prestacao tri
	$tm->query("select sum(pr_valor) as valor from prestacoes where pr_tipo='t' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"valor");
	$pn->set("valor_trimestral",  mil($qt));

	//pega valor prestacao sem
	$tm->query("select sum(pr_valor) as valor from prestacoes where pr_tipo='s' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"valor");
	$pn->set("valor_semestral",  mil($qt));

	//pega valor prestacao chave
	$tm->query("select sum(pr_valor) as valor from prestacoes where pr_tipo='c' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"valor");
	$pn->set("valor_chaves",  mil($qt));


	// pega valor pago
	$tm->query("select sum(pr_valor) as valor from prestacoes where pr_pago='s' and pr_apto='$i'");
	$r=$tm->result;
	$qt=mysql_result($r,0,"valor");
	$pn->set("pago",  mil($qt));

	$saldo=$dados->ap_valor-$qt;
	$pn->set("saldo",  mil($saldo));


	// monta pagina
	$cont.=$pn->tvar();
	$pg->set("conteudo",$cont);
	$pg->set("pagina","Detalhe de Apartamento");
	$pg->tprint();
?>