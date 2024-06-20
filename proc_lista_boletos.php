<?php

	// gera os boletos para impressao
	set_time_limit(600);


	include "lib/var.php";
	include "lib/func.php";
	include "lib/class_db.php";

	$db   = new db(); // link principal
	$pr   = new db(); // link das prestacoes
	$bol  = new db(); // load de boletos
	$cb   = new db(); // link de checagem do boleto
	$mail = new envia_boleto();


	$dados = explode(' - ', $_POST['banco']);
	$sql="select * from contas where conta = $dados[1]";
	$db->query($sql);

	if ( $dados[0] == 'CEF' ) {
		$b = new boleto_CEF();
		$b->init();

		if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");
		// loop boletos
		while($d=mysql_fetch_object($db->result)){
			$b->val("razao"   , $d->razao);
			$b->val("cnpj"    , $d->cnpj);
			$b->val("agencia" , $d->agencia);
			$b->val("conta"   , $d->conta);
			$dados[2] = $d->id;
		}

	} else if ( $dados[0] == 'BRA' ) {
		$b = new boleto_Bradesco();
		$b->init();

		if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");
		// loop boletos
		while($d=mysql_fetch_object($db->result)){
			$b->val("razao"     , $d->razao);
			$b->val("cnpj"      , $d->cnpj);
			$b->val("agencia"   , $d->agencia);
			$b->val("conta"     , $d->conta);
			// $b->val("acessorio" , $d->acessorio);
			$dados[2] = $d->id;

		}

	} else {
		// $b= new boleto_HSBC();
		die('Erro na hora do processamento');
	}


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


        

   

        $b->set("demonstrativo1","parcela {$d->pr_num} de $tt ($parc)") ;  
		$b->set("demonstrativo2","Edificio {$d->ed_nome}  -  apartamento {$d->ap_num}"); 
		//$b->set("demonstrativo3","parcela 01/02 Convencao Condominial: R\$ ".mil($b->taxa_boleto));    
		//$b->set("demonstrativo4","taxa do boleto: R\$ ".mil($b->taxa_boleto));  
		//$b->set("demonstrativo3",".");  
		//$b->set("demonstrativo4",".");



//("demonstrativo4","taxa do boleto: R\$ ".mil($b->taxa_boleto)); 

		//- atualizada com juros e multa + honorarios advocaticios

		// 1) parcela {$d->pr_num} de $tt ($parc)

		// 2) Edificio {$d->ed_nome}  -  apartamento {$d->ap_num}

		//  referente aluguel do mes agosto/2018

		//  referente aluguel do periodo de 06 de março/2015 a 05 de abril/2015

		//  sala comercial sito a Av.Pres. Castelo Branco, 4.718 loja 04
		//  aluguel ref.: 01/03/2019 a 31/03/2019 R$ 3.297,33 e IPTU março/2019 R$ 137,16

//  Terreno Tupi - Lote 26 Quadra K
//  aluguel ref.: março/2019 

		$b->set("endereco1", htmlspecialchars(($d->cli_rua)).','.$d->cli_numero.' - '.utf8_encode($d->cli_bairro));
		$b->set("endereco2", htmlspecialchars($d->cli_cidade)." - ".htmlspecialchars(($d->cli_estado))." - CEP: {$d->cli_cep}");
		$b->val("valor_boleto", $d->pr_valor);
		$b->val("data_vencimento",$vc);

		$bol->query("SELECT * FROM boletos WHERE bo_ndoc LIKE '%".substr(unmask($d->cli_cpf),0 , 6).date('my')."%' ORDER BY bo_ndoc desc");
		$boleto = $bol->get_val("bo_ndoc");
		if ( $boleto != '' ) {
			$digito = substr($boleto, -1, 1)+1;
		} else {
			$digito = 1;
		}
		$ndoc = substr(unmask($d->cli_cpf),0 , 6) . date('my') . $digito;

		$b->val("numero_documento",$ndoc);
		$b->set("data_documento",date("d/m/Y"));

		$b->set("sacado", $d->cli_nome . ' - CPF/CNPJ: ' . $d->cli_cpf);
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
		$enviar = 'n';
		if($enviar=="s"){

			$boleto=str_replace("imagens/","",$b->layout);
			$valor=mil($d->pr_valor);
			$msg="
			Segue em anexo<br />
			boleto referente ao pagamento de parcela do apartamento {$d->ap_num} do edificio {$d->ed_nome}<br>
			Valor da prestação: R\${$valor}<br />
			Numero do documento: {$ndoc}<br /><br /><br />
			caso tenha problemas na visualização do boleto, utilize a linha digitavel:<br /><br />
			<span style='padding:3px;background:#C0C0C0;border:1px solid;#585858;font-family:courier;font-size:11pt;color:black;font-weight:bold'>{$b->dadosboleto[linha_digitavel]} </span>";

			$mail->set_dados($boleto);
			$mail->enviar($d->cli_email,$msg);
			$mail->reset();
		}

		///////////// fim  envia boleto por email

		/////// verifica de o boleto existe, caso contrario insere no banco de dados

		$cb->query("select count(*) as qt from boletos where bo_presta='{$d->id_presta}'");
		if($cb->status=="erro")die($cb->erro);

		if($cb->get_val("qt")==0){
			$nnum = str_replace(array('/','-',' '), '', $b->nossonumero);
			$nnum = substr($nnum, 2);
			$sql="insert into boletos (bo_apto,bo_prop,bo_presta,bo_valor,bo_data_emissao,bo_data_vence,bo_num_presta,bo_ndoc,bo_nnum, conta_id)
			values ('{$d->id_apto}','{$d->id_cliente}', '{$d->id_presta}', '{$d->pr_valor}','$de','$vcq','{$d->pr_num}','$ndoc','$nnum', '{$dados[2]}')
			";

			$cb->reset();
			$cb->query($sql);

		}
		/////////// fim verifica boleto

		$b->reset();
	} // fim loop boletos
?>