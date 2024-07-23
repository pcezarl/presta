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

	$id_apto     = limpa($_POST["apto"]);
	$id_prop     = limpa($_POST["cliente"]);

	$parcelas    = limpa($_POST["prestacao"]);
	$chaves      = nformat($_POST["chaves"]);
	$dia_chave   = limpa($_POST["dia_chave"]);
	$mes_chave   = limpa($_POST["mes_chave"]);
	$ano_chave   = limpa($_POST["ano_chave"]);
	$vence       = limpa($_POST["dia_inicial"]);

	$valor       = nformat(limpa($_POST["valor"]));

	$entrada     = limpa($_POST["entrada"]);
	$semestral   = limpa($_POST["semestral"]);
	$trimestral  = limpa($_POST["trimestral"]);
	$intermediaria  = limpa($_POST["intermediaria"]);

	$dia_inicial  = limpa($_POST["dia_inicial"]);
	$mes_inicial  = limpa($_POST["mes_inicial"]);
	$ano_inicial  = limpa($_POST["ano_inicial"]);



	$saldo_imovel=$valor;

	$db->query("select ap_vendido from aptos where id_apto='$id_apto'");
	$status=mysql_fetch_object($db->result);

	if($status->ap_vendido=="s")die ("ERRO: ESSE APARTAMENTO JÁ FOI VENDIDO");



	$data_venda=date("m/d/Y");

	$db->query("lock table vendas write");
	$sql="insert into vendas
	(venda_apto,venda_prop,venda_data,venda_data_chave) values
	('$id_apto','$id_prop','$data_venda','$data_chave');
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



	// processa chaves
	if($chaves){
		$data_chave="$ano_chave-$mes_chave-$vence";
		if($chaves){
			$saldo_imovel-=$chaves;
			$sql=sprintf($sqlbase,"$chaves","$data_chave","c");
			$db->query($sql);
		}

	}// fim chaves


	// processa entrada
	if($entrada){

		$db->transact("abre");
		$en=str_decode($entrada);
		$en=array_simply($en);

		foreach($en as $v){

			$presta=$v["valor"];

			$data_vence=date("Y-m-d",$v["data"]);
			$sql=sprintf($sqlbase,"$presta","$data_vence","e");
			$db->query($sql);

			$total_entrada+=$presta;
		} // fim loop foreach

		// calcula valor do imovel
		$saldo_imovel-=$total_entrada;
		$db->transact("salva");
	} // fim entrada

	// processa semestrais
	if($semestral){

		$db->transact("abre");
		$en=str_decode($semestral);
		$en=array_simply($en);

		foreach($en as $v){
			$presta= $v["valor"];

			$data_vence=date("Y-m-d",$v["data"]);
			$sql=sprintf($sqlbase,"$presta","$data_vence","s");
			$db->query($sql);

			$total_semestral+=$presta;

		} // fim loop foreach

		// calcula valor do imovel
		$saldo_imovel-=$total_semestral;

		$db->transact("salva");
	} // fim semestral



	// processa tirmestrais
	if($trimestral){

		$db->transact("abre");
		$en=str_decode($trimestral);
		$en=array_simply($en);

		foreach($en as $v){
			$presta= $v["valor"];

			$data_vence=date("Y-m-d",$v["data"]);
			$sql=sprintf($sqlbase,"$presta","$data_vence","t");
			$db->query($sql);

			$total_trimestral+=$presta;

		} // fim loop foreach

		// calcula valor do imovel
		$saldo_imovel-=$total_trimestral;

		$db->transact("salva");
	} // fim semestral



	// processa intermediaria
	if($intermediaria){

		$db->transact("abre");
		$en=str_decode($intermediaria);
		$en=array_simply($en);

		foreach($en as $v){
			$presta= $v["valor"];

			$data_vence=date("Y-m-d",$v["data"]);
			$sql=sprintf($sqlbase,"$presta","$data_vence","i");
			$db->query($sql);

			$total_trimestral+=$presta;

		} // fim loop foreach

		// calcula valor do imovel
		$saldo_imovel-=$total_trimestral;

		$db->transact("salva");
	} // fim intermediaria




	// calcula valor da parcela base
	$parcela_base=$saldo_imovel/$parcelas;
	settype($parcela_base,"float");


	// gera lista
	// o prazo começa com 0
	$_prazo = 0;

	// pegamos o dia atual
	$_dia   = $dia_inicial;
	$_mes   = $mes_inicial;
	$_ano   = $ano_inicial;

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


		$_ts = mktime(0,0,0,$_mes,$_dia + $_prazo,$_ano);
		$_data = date('d/m/Y',$_ts);
		$data_vence=date("Y-m-$vence",$_ts);


		$_prazo += 30;

		$sql=sprintf($sqlbase,"$parcela_base","$data_vence","n");
		$db->query("$sql");


	} // fim loop


	// fim gera lista


	///////// cria indices de parcela

	$sql="select
	distinct(pr_tipo) as tipo,
	count(id_presta) as  total
	from prestacoes where pr_apto='$id_apto'
	group by tipo
	";

	$db->query($sql);

	$dbi=new db();
	$db_temp=new db();


	while($d=mysql_fetch_object($db->result)){
		$idx=0;

		$db_temp->query("select id_presta as id from prestacoes where pr_apto='$id_apto' and pr_tipo='{$d->tipo}' ");
		$total=$db_temp->rows;

		// loop do indice
		while($lista_pr=mysql_fetch_object($db_temp->result)){
			$idx++;
			$sql="update prestacoes set pr_num='$idx' where id_presta='{$lista_pr->id}'";
			$dbi->query($sql);

		}

		// fim loop indice




	} ///// fim loop while




	///////////// fim indice


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


			function pof(){
				parent.document.getElementById("myform").reset();
				parent.document.getElementById("l_ent").innerHTML='';
				parent.document.getElementById("l_sem").innerHTML='';
				parent.document.getElementById("l_tri").innerHTML='';
				parent.$("#c_proc").fadeOut(1000);

			}

			setTimeout("pof()",5000);




			-->
		</script>

	</head>
	<body>
		<div style="display:block;width:100%;font-size:14pt  ;background:<?php echo $cor?>" id="okk">
			<img src="images/alerta.gif" alt="" /><?php echo $msg ?>
		</div>
	</body>
</html>
