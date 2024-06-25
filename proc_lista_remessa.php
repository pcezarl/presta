<?php

	// gera o arquivo de remessa
	set_time_limit(600);

	include "lib/var.php";
	include "lib/func.php";
	include "lib/class_db.php";
	session_start();

	if ( $_SESSION['banco'] == 'CEF' ) {
		// header('location: remessa-caixa.php');
		$b = new remessa_CEF();
	} else if ( $_SESSION['banco'] == 'BRA' ) {
		$b = new remessa_Bradesco();
	} else if ( $_SESSION['banco'] == 'SICOOB' ) {
		$b = new remessa_SICOOB();
	} else {
		// $b= new boleto_HSBC();
		die('Erro na hora do processamento');
	}

	$db   = new db(); // link principal
	$pr   = new db(); // link das prestacoes
	$cb   = new db(); // link de checagem da remessa
	$ub   = new db(); // query de update da remessa
	// $mail = new envia_boleto();
	// Pego o ultimo ID de remessa gerado
	$db->query("SELECT MAX(remessa_id) as max_remessa_id FROM boletos WHERE conta_id =".$_SESSION['conta_id']);
	$result = mysql_fetch_assoc($db->result);
	$max_remessa_id = ( $result['max_remessa_id'] == '' ) ? '0' : $result['max_remessa_id'];
	$remessa_id = (int)$max_remessa_id + 1; 
	// Carrego os detalhes da conta selecionada
	$sql = "SELECT * FROM contas where id = ".$_SESSION['conta_id'];
	$db->query($sql);
	$row = mysql_fetch_assoc($db->result);
	$data['agencia']  		= $row['agencia'];
	$data['documento']		= $row['cnpj'];
	$data['nome_empresa'] 	= $row['razao'];
	$data['conta_corrente'] = $row['conta'];
	$data['acessorio'] 		= $row['acessorio'];
	$data['numero_sequencia_remessa'] = $remessa_id;
	$data['numero_sequencia_registro'] = 1;

	// Classe que monta o cabecalho do arquivo de remessa
	$b->header($data);

	// Crio um select buscando os detalhes das prestações selecionadas
	$lista_presta=implode(",",$_POST["parcela"]);
	$enviar=limpa($_POST["send"]);
	$sql="select * from prestacoes as a
	left join aptos on pr_apto=id_apto
	left join clientes on pr_prop=id_cliente
	left join edificios on ap_ed=id_edificio
	left join boletos b on a.id_presta = b.bo_presta 
	where id_presta in($lista_presta)";

	$db->query($sql);
	$qt=$db->rows;
	$cnt=0;

	while($d=mysql_fetch_object($db->result)) {

		$data['valor_titulo'] = $d->bo_valor;
		$data['data_emissao_titulo'] = $d->bo_data_emissao;
		$data['documento_pagador'] = $d->cli_cpf;
		$data['nome_pagador'] = $d->cli_nome;

		if ( $_SESSION['banco'] == 'BRA' ) {
			$data['data_vencimento_titulo'] = $d->bo_data_vence;
			$data['endereco_pagador'] = $d->cli_rua.' - '.$d->cli_numero.' - '.$d->cli_bairro.' - '.$d->cli_cidade.' - '.$d->cli_estado;
		} else if ( $_SESSION['banco'] == 'CEF' ) {
			$data['data_vencimento_titulo'] = $d->bo_data_vence;
			$data['endereco_pagador'] = $d->cli_rua.' - '.$d->cli_numero;
			$data['cidade_pagador']   = $d->cli_cidade;
			$data['estado_pagador']   = $d->cli_estado;
		} else if ( $_SESSION['banco'] == 'SICOOB' ) {
			$data['data_vencimento_titulo'] = $d->bo_data_vence;
			$data['endereco_pagador'] = $d->cli_rua.' - '.$d->cli_numero;
			$data['cidade_pagador']   = $d->cli_cidade;
			$data['bairro_pagador']   = $d->cli_bairro;
			$data['estado_pagador']   = $d->cli_estado;
			$data['parcela'] = $d->id_presta;
		}
		$data['cep_pagador'] = $d->cli_cep;
		$data['nosso_numero'] = $d->bo_nnum;
		$data['numero_documento'] = $d->bo_ndoc;

		// atualizo o boleto, indicando que ele pertence a essa remessa que esta sendo gerada
		$ub->query("UPDATE boletos SET remessa_id = $remessa_id WHERE id_boleto = $d->id_boleto");

		// classe registro, que vai possuir os detalhes dos boletos
		$b->registro($data);
	}	
	// chamo a classe de trailer, que finaliza o arquivo remessa e gera o txt para download
	$b->trailer($data);
?>