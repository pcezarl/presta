<?php

	// gera os boletos para impressao
	set_time_limit(600);


	include "lib/var.php";
	include "lib/func.php";
	include "lib/class_db.php";
	//include "lib/class_HSBC.php";

	//error_reporting(E_ALL);

	$b= new boleto_HSBC();
	$db=new db(); // link principal
	$pr=new db(); // link das prestacoes
	$cb=new db(); // link de checagem do boleto
	$mail=new envia_boleto();


	$cnt=0;

	$lista_presta=implode(",",$_POST["parcela"]);
	$enviar=limpa($_POST["send"]);

	$sql="select * from prestacoes
	left join aptos on pr_apto=id_apto
	left join clientes on pr_prop=id_cliente
	left join edificios on ap_ed=id_edificio
	where id_presta in($lista_presta)";

	$db->query($sql);
	$qt=$db->rows;
	if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");

	$b->init();



	// loop boletos
	while($d=mysql_fetch_object($db->result)){
		$cnt++;

		// calcula o vencimento
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


        

   //     $b->set("demonstrativo1"," ");    
  	$b->set("demonstrativo1","parcela {$d->pr_num} de $tt ($parc) ");
		$b->set("demonstrativo2","Edificio {$d->ed_nome}  -  apartamento {$d->ap_num}");
//		$b->set("demonstrativo3","R$ 1.214,43 referente aluguel do mes agosto/2014");


//   $b->set("demonstrativo4","taxa do boleto: R\$ ".mil($b->taxa_boleto));         
  


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


		/////////// envia boleto por email

		if($enviar=="s"){

			$boleto=str_replace("imagens/","",$b->layout);
			$valor=mil($d->pr_valor);
			$msg="
			Segue em anexo<br />
			boleto referente ao pagamento de parcela do apartamento {$d->ap_num} do edificio {$d->ed_nome}<br>
			Valor da prestação: R\${$valor}<br />
			Numero do documento: {$ndoc}<br /><br /><br />
			caso tenha problemas na visualização do boleto, utilize a linha digitavel:<br /><br />
			<span style='padding:3px;background:#C0C0C0;border:1px solid;#585858;font-family:courier;font-size:11pt;color:black;font-weight:bold'>{$b->dadosboleto[linha_digitavel]} </span>
			";

			$mail->set_dados($boleto);
			$mail->enviar($d->cli_email,$msg);
			$mail->reset();
		}

		///////////// fim  envia boleto por email



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
