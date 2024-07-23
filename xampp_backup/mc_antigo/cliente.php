<?php

	////////////
	// informacoes do cliente
	///////////

	include "lib/var.php";
	include "lib/func.php";


	$i=limpa($_GET["i"]);

	$pg = new tpl($modelo);
	$pn = new tpl("tpl/tpl_cliente.html");
	$db = new db;
	$ap = new db;


	$sql="select * from clientes where id_cliente='$i'";
	$db->query($sql);
	if($db->status=="erro")die($db->erro);
	if($db->rows==0) error(3) ;

	$dados=mysql_fetch_object($db->result);
	$data=strtotime($dados->cli_data_cadastro);
	$data=date("d/m/Y",$data);
	$cont="<h2 class='titulo'>Detalhes do cliente: {$dados->cli_nome} - cadastrado em: $data</h2>";


	$pn->set("nome",    $dados->cli_nome);
	$pn->set("email",   $dados->cli_email);
	$pn->set("telefone",$dados->cli_tel);
	$pn->set("cpf",     $dados->cli_cpf);
	$pn->set("id_cli",  $dados->id_cliente);



	// endereco
	$pn->set("rua",   $dados->cli_rua);
	$pn->set("numero",$dados->cli_numero);
	$pn->set("bairro",$dados->cli_bairro);
	$pn->set("cep",   $dados->cli_cep);
	$pn->set("cidade",$dados->cli_cidade);
	$pn->set("estado",$dados->cli_estado);


	// monta aptos do cli
	$ap->query("select * from aptos join edificios on ap_ed=id_edificio join clientes on ap_prop=id_cliente where id_cliente='{$dados->id_cliente}'");


	if($ap->rows==0){
		$pn->esc("aptos");
	}else{


		$pn->begin_loop("linha");
		// loop linha aptos
		while($dados=mysql_fetch_object($ap->result)){

			$ad["id_edi"]=$dados->id_edificio;
			$ad["nome_edi"]=$dados->ed_nome;
			$ad["apto"]=$dados->ap_num;
			$ad["id_apto"]=$dados->id_apto;
			$ad["id_ed"]=$dados->id_edificio;

			$pn->set_loop($ad);
		}// fim loop linha aptos
		$pn->end_loop("linha");

	}


	$cont.=$pn->tvar();
	$pg->set("conteudo",$cont);
	$pg->set("pagina","Detalhe de Cliente");
	$pg->tprint();
?>