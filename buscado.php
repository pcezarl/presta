<?php
	// resultado da busca  por codigo

	include "lib/var.php";
	include "lib/func.php";

	$i=limpa($_GET["i"]); # ID
	$t=limpa($_GET["t"]); # tipo

	$sql_tipo["a"]="select * from aptos where id_apto='$i'";
	$sql_tipo["c"]="select * from clientes where id_cliente='$i'";
	$sql_tipo["b"]="select * from boletos where id_boleto='$i'";
	$sql_tipo["d"]="select * from boletos where bo_ndoc='$i'";
	$sql_tipo["p"]="select * from prestacoes where id_presta='$i'";

	if(!array_key_exists($t,$sql_tipo) )error(2);

	$db=new db();
	$db->query($sql_tipo[$t]);


	function getPresta($i)
	{
		global $db;
		$db->reset();
		$sql="select bo_presta from boletos where bo_ndoc='$i'";
		$db->query($sql);
		return $db->get_val("bo_presta");

	}


	if($db->status=="erro")die($db->erro);

	$p=new tpl($modelo);
	$p->set("pagina","Resultado da busca");

	$pn=new tpl("tpl/tpl_mini_painel.html");

	if($db->rows==0)
	{
		$p->set("conteudo","<h3 style='color:#800000'>Nenhum registro encontrado</h3><a href='index.php' class='aqua4'>Continuar</a>");
		$p->tdie();
	}

	switch ($t)
	{
		case "a":header("location:apto.php?i=$i");break;
		case "c":header("location:cliente.php?i=$i");break;
		case "b":header("location:ver-boleto-unico.php?i=$i");break;
		case "d":$i = getPresta($i); break;


	}

	normal:

	$db->reset();
	$sql="
	SELECT * FROM prestacoes
	left join aptos on id_apto=pr_apto
	left join clientes on pr_prop=id_cliente
	left join edificios on id_edificio=ap_ed
	left join boletos on bo_presta=id_presta
	where id_presta='$i'
	";


	$db->query($sql);
	if($db->status=="erro")die($db->erro);
	$data=$db->data_object;

	$valor=mil($data->pr_valor);
	$vence=mydata($data->pr_vencimento);
	$d_pago=($data->pr_data_pago!='0000-00-00')?"<div style='background-color:#51A3FD;'>".mydata($data->pr_data_pago)."</div>":"<div style='color:white;text-align:center;background-color:#BB0000;font-weight:bold;font-family:verdana'>PENDENTE</div>";


	if($data->bo_ndoc==null)
	{
		$bt_boleto="<a href='gera-boleto-unico.php?i=$i' target='_blank'><img src='images/bt_boleto.gif' alt='boleto' title='gerar boleto' border='0' /></a>";
	}else{
		$bt_boleto="
		<a href='ver-boleto-unico.php?i=$i' target='_blank'><img src='images/bt_ver_boleto.gif' alt='boleto' title='ver boleto' border='0' /></a>
		<a href='proc_apaga_boleto.php?i=$i' ><img src='images/bt_apaga.png' alt='boleto' title='excluir boleto' border='0' /></a>
		";
	}

	if($data->pr_pago=='n')
	{
		$bt_pagar="<a href='proc_pagar_presta.php?i=$i&amp;a=direct' target='proc'><img src='images/vender.gif' alt='pagar' title='efetuar pagamento' border='0' /></a>";
	}else{
		$bt_pagar="";
	}


	$cont="
	<iframe id='proc' name='proc' style='display:none'></iframe>
	<table border='1' width='100%' cellspacing='0' align='center' style='border-collapse: collapse;border:1px solid #63541F'>
	<tr><td>Valor</td><td>$valor</td></tr>
	<tr><td>Vencimento</td><td>$vence</td></tr>
	<tr><td>Pagamento</td><td>$d_pago</td></tr>
	<tr><td>Edificio</td><td>{$data->ed_nome}</td></tr>
	<tr><td>Apartamento</td><td>{$data->ap_num}</td></tr>
	<tr><td>Proprietario</td><td>{$data->cli_nome}</td></tr>
	<tr><td colspan='2' align='center'> $bt_pagar $bt_boleto </td></tr>
	</table>
	";

	$pn->set("texto",$cont);
	$cont="<h1 class='titulo'>Detalhe de Prestação</h1><div style='margin-left:30%;display:block'>{$pn->tvar()}</div> ";


	$p->set("conteudo",$cont);
	$p->tprint();
	exit();


	///////////////////////////////



?>