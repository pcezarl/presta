<?php
	////  processa a apagadura de um boleto

	include("lib/var.php");
	include("lib/func.php");

	$i=(integer)limpa($_GET["i"]);

	$db=new db();

	$db->query("select * from boletos where bo_presta='$i'");
	if($db->rows!=1)
	{
		erro("LINK CORROMPIDO.<br />O processo foi interrompido");

	}

	$db->query("delete from boletos where bo_presta='$i'");

	if($db->affect!=1)
	{
		erro("Itens indisponivel");
	}


	if($db->status=="erro")
	{
		error(2);

	}


	header("location:index.php");




?>
