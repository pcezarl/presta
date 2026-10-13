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
	} else if ( $_SESSION['banco'] == 'ASA' ) {
		$b = new remessa_ASA();
	} else {
		// $b= new boleto_HSBC();
		die('Erro na hora do processamento');
	}

	$db   = new db(); // link principal
	$pr   = new db(); // link das prestacoes
	$cb   = new db(); // link de checagem da remessa
	$ub   = new db(); // query de update da remessa
	// $mail = new envia_boleto();

	if ( empty($_POST["parcela"]) ) {
		erro("Selecione ao menos uma parcela para gerar a remessa.");
	}

	// NSA — numero sequencial do arquivo, que o banco exige crescente por conta.
	//
	// Antes era MAX(boletos.remessa_id) + 1. Como o numero so existia enquanto houvesse
	// boleto marcado com ele, zerar o remessa_id (ex.: ao regerar o nosso numero) fazia
	// o NSA voltar para tras e o banco recusava o arquivo como repetido.
	//
	// Agora o contador fica em contas.ultimo_nsa e e incrementado de forma atomica. O
	// LAST_INSERT_ID(expr) devolve o valor novo na mesma conexao, entao nao pode haver
	// reset() do $db entre o UPDATE e o SELECT. Cada ambiente tem o seu contador; so o
	// de producao corresponde ao que o banco ja recebeu.
	$conta_id = (int) $_SESSION['conta_id'];
	$db->query("UPDATE contas SET ultimo_nsa = LAST_INSERT_ID(ultimo_nsa + 1) WHERE id = ".$conta_id);
	if ( $db->affect != 1 ) {
		erro("Conta de cobranca nao encontrada. Selecione a conta novamente antes de gerar a remessa.");
	}
	$db->query("SELECT LAST_INSERT_ID() AS nsa");
	$remessa_id = (int) $db->get_val("nsa");
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
	$data['agencia_dv'] = $row['agencia_dv'];
	$data['conta_dv']   = $row['conta_dv'];
	$data['operacao']   = $row['operacao'];
	$data['carteira']   = $row['carteira'];

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
			$data['parcela'] = '1';
		} else if ( $_SESSION['banco'] == 'ASA' ) {
			$data['data_vencimento_titulo'] = $d->bo_data_vence;
			$data['endereco_pagador'] = $d->cli_rua.' - '.$d->cli_numero;
			$data['cidade_pagador']   = $d->cli_cidade;
			$data['bairro_pagador']   = $d->cli_bairro;
			$data['estado_pagador']   = $d->cli_estado;
			// bo_nnum guarda o nosso numero SEM o DV; a classe recalcula o digito.
			$data['nosso_numero']     = $d->bo_nnum;
		}
		$data['cep_pagador'] = $d->cli_cep;
		$data['nosso_numero_completo'] = $data['nosso_numero'] = $d->bo_nnum;
		$data['numero_documento'] = $d->bo_ndoc;

		// atualizo o boleto, indicando que ele pertence a essa remessa que esta sendo gerada
		$ub->query("UPDATE boletos SET remessa_id = $remessa_id WHERE id_boleto = $d->id_boleto");

		// classe registro, que vai possuir os detalhes dos boletos
		$b->registro($data);
	}	
	// chamo a classe de trailer, que finaliza o arquivo remessa e gera o txt para download
	$b->trailer($data);
?>