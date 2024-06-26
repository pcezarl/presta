<?php

	// gera os boletos para impressao


	include "lib/var.php";
	include "lib/func.php";



	$b= new boleto_HSBC();
	$db=new db(); // link principal
	$pr=new db(); // link das prestacoes
	$cb=new db(); // link de checagem do boleto
	//error_reporting(E_ALL);

	$cnt=0;

	$i=limpa($_GET["i"]);

	$sql="select * from prestacoes
	left join aptos on pr_apto=id_apto
	left join clientes on pr_prop=id_cliente
	left join edificios on ap_ed=id_edificio
	where id_presta='$i'";

	$db->query($sql);
	$qt=$db->rows;
	if($db->rows<1)erro("O link está corrompido, sua solicitação não pôde ser atendida");

	$b->init();

	// calcula o vencimento
	$vc=strtotime( date("Y-m-d"));
	$vc=strtotime("+{$b->mvence} day",$vc); //  dias a partir da data atual
	$vc=date("d/m/Y",$vc);

	//die($vc);


	// loop boletos
	while($d=mysql_fetch_object($db->result)){
		$cnt++;

		$vc=strtotime($d->pr_vencimento);
		$vc=strtotime("+{$b->mvence} day",$vc); //  dias a partir da data atual
		$vc=date("d/m/Y",$vc);


		$sql="
		select max(pr_num) as tt
		from prestacoes
		where pr_apto='{$d->id_apto}' and pr_tipo='{$d->pr_tipo}'";
		$pr->query($sql);
		$tt=$pr->get_val("tt");
		$pr->reset();

		$parc=$tipo_parcela[$d->pr_tipo];
		$parc=strtoupper($parc);

		$b->set("demonstrativo1","parcela {$d->pr_num} de $tt ($parc) ");
		$b->set("demonstrativo2","Edificio {$d->ed_nome} apto: {$d->ap_num}");
		$b->set("demonstrativo3","Taxa do boleto: R\$ ".mil($b->taxa_boleto));
		$b->set("demonstrativo4","Construtura Aires");

		$b->set("endereco1","{$d->cli_rua},{$d->cli_numero} - {$d->cli_bairro}");
		$b->set("endereco2","{$d->cli_cidade} - {$d->cli_estado} -CEP:{$d->cli_cep}");
		$b->val("valor_boleto",$d->pr_valor);
		$b->val("data_vencimento",$vc);

		$ndoc=sprintf("%04d%04d%04d",$d->id_presta,$d->id_cliente,$d->id_apto);
		$b->val("numero_documento",$ndoc);

		$b->set("data_documento",date("d/m/Y"));


		$b->set("sacado",$d->cli_nome);
		$b->draw();

		if($cnt<$qt){
			$b->pagina();
		} else{
			$b->fim();
		}

		// data emissao
		$de=date("Y-m-d");

		// data vencimento
		$vcq=data2mysql($vc);

		echo $b->layout;


		/////// verifica de o boleto existe, caso contrario insere no banco de dados


		$cb->query("select count(*) as qt from boletos where bo_presta='{$d->id_presta}'");
		if($cb->status=="erro")die($cb->erro);

		if($cb->get_val("qt")==0){

			$sql="insert into boletos
			(bo_apto,bo_prop,bo_presta,bo_valor,bo_data_emissao,bo_data_vence,bo_num_presta,bo_ndoc,bo_nnum)
			values
			('{$d->id_apto}','{$d->id_cliente}', '{$d->id_presta}', '{$d->pr_valor}','$de','$vcq','{$d->pr_num}','$ndoc','{$b->nnum}')
			";



			$cb->reset();
			$cb->query($sql);



		}
		/////////// fim verifica boleto


		$b->reset();
	} // fim loop boletos



?>
