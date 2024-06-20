<?php

	/// processa a inclusao de prestacao avulsa
	//// isso vai ser um saco.....

	include "lib/var.php";
	include "lib/func.php";
	error_reporting(E_ALL);
	$db=new db();

	foreach($_POST as $n=>$v)
	{
		$$n=limpa($v);
	}

	// pega dados da venda

	$vencimento="$ano-$mes-$dia";
	$valor =nformat($valor);


	$sql="select * from vendas
	left join aptos on venda_apto=id_apto
	left join clientes on venda_prop=id_cliente
	where id_apto='$apto'";


	$db->query($sql);
	$dados=$db->data_object;

	$db->reset();
	$db->query("select max(pr_num) as max from prestacoes where pr_tipo='$tipo' and pr_apto='{$dados->id_apto}' ");
	$idx=$db->get_val("max")+1;


	/// monta sql
	$sqlbase="insert into
	prestacoes (pr_venda,pr_apto,pr_prop,pr_valor,pr_vencimento,pr_tipo,pr_num)
	values     ('{$dados->id_venda}','{$dados->id_apto}','{$dados->id_cliente}','$valor','$vencimento','$tipo','$idx'); ";


	$db->query($sqlbase);
	if($db->status=="erro")
	{
		die("ERRO::N�O FOI POSSIVEL ADICIONAR PRESTACAO<BR>{$db->erro}");
	}

	//die($sqlbase);

	/*
	$sql_indice="update prestacoes set pr_num=pr_num+1
	where pr_apto={$dados->id_apto} and pr_vencimento>'$vencimento' and pr_num is not null order by pr_num";





	////// recria indice
	$db->reset();
	$db->query($sql_indice);
	if($db->status=="erro")
	{
		die("ERRO::N�O FOI POSSIVEL RECRIAR INDICE DE PRESTACOES<BR>{$db->erro}");
	}
	  */


	   //// ate que nao foi tao ruim qto pensei... ainda bem :)

?>
<script>




	alert("OK!! Presta��o adicionada com sucesso.");
	self.close();



</script>

