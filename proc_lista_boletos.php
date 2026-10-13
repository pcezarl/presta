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

	} else if ( $dados[0] == 'SICOOB' ) {
		$b = new boleto_SICOOB();
		$b->init();
		if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");
		// loop boletos
		while($d=mysql_fetch_object($db->result)){
			
			$b->val("razao"     	, $d->razao);
			$b->val("cnpj"      	, $d->cnpj);
			$b->val("agencia"   	, $d->agencia);
			$b->val("conta"     	, $d->conta);
			$b->val("codigo_cliente", $d->cod_cliente);
			$conta_sicoob = $d;
			$dados[2] = $d->id;
		}
	} else if ( $dados[0] == 'ASA' ) {
		$b = new boleto_ASA();
		$b->init();
		if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");
		// loop boletos
		while($d=mysql_fetch_object($db->result)){

			$b->val("razao"      , $d->razao);
			$b->val("cnpj"       , $d->cnpj);
			$b->val("agencia"    , $d->agencia);
			$b->val("agencia_dv" , $d->agencia_dv);
			$b->val("conta"      , $d->conta);
			$b->val("conta_dv"   , $d->conta_dv);
			$b->val("operacao"   , $d->operacao);
			$b->val("carteira"   , $d->carteira);
			$conta_asa = $d;
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
        $b->set("parcela", $parc) ;
		
        $b->set("demonstrativo1","Parcela {$d->pr_num} ($parc)");
		$b->set("demonstrativo2","Edificio {$d->ed_nome}  -  apartamento {$d->ap_num}"); 
		$b->set("endereco1", (($d->cli_rua)).','.$d->cli_numero.' - '.$d->cli_bairro);
		$b->set("endereco2", ($d->cli_cidade." - ".$d->cli_estado." - CEP: {$d->cli_cep}"));
		$b->val("valor_boleto", $d->pr_valor);
		$b->val("data_vencimento",$vc);

		if ( $dados[0] == 'SICOOB' ) {
			$ndoc = substr(unmask($d->cli_cpf),0 , 4) . date('m');
		} else {
			$ndoc = substr(unmask($d->cli_cpf),0 , 6) . date('my');
		}
		$bol->query("SELECT * FROM boletos WHERE bo_ndoc LIKE '%".$ndoc."%' ORDER BY bo_ndoc desc");
		$boleto = $bol->get_val("bo_ndoc");
		$nnum = $bol->get_val("bo_nnum");
		$digito = ( $boleto != '' ) ? substr($boleto, -1, 1)+1 : 1;
		$ndoc = $ndoc. $digito;

		$b->val("numero_documento",$ndoc);
		$b->set("data_documento",date("d/m/Y"));

		if ( $dados[0] == 'SICOOB' ) {
			// O numero do documento do SICOOB deixa de ser derivado do CPF.
			//
			// Motivo: a base antiga era substr(cpf,0,4).date('m') — 4 digitos do CPF
			// e o mes, SEM o ano. Como o documento tem 7 caracteres, sobrava 1 para o
			// sequencial: no maximo 9 titulos por balde. E o balde era compartilhado
			// por todos os clientes com os mesmos 4 digitos iniciais de CPF E por todos
			// os anos. Ao estourar, o documento repetia indefinidamente; como no SICOOB
			// o nosso numero E o documento, o nosso numero repetia junto e o campo livre
			// passava de 25 para 26 posicoes, gerando codigo de barras de 45.
			//
			// Agora o numero e um sequencial proprio da conta, de 7 digitos. O ponto de
			// partida vem de contas.faixa_inicio — dado de cadastro, nao constante no
			// codigo — para que cada ambiente tenha a sua numeracao.
			$cb->reset();
			$cb->query("SELECT bo_ndoc FROM boletos WHERE bo_presta = '{$d->id_presta}' AND bo_ndoc IS NOT NULL AND bo_ndoc <> '' LIMIT 1");
			$ndoc_sicoob   = $cb->get_val("bo_ndoc");
			$sicoob_lock   = false;

			if ( $ndoc_sicoob == '' ) {
				$inicio = (int) $conta_sicoob->faixa_inicio;
				$fim    = (int) $conta_sicoob->faixa_fim;
				if ( $inicio < 1 || $fim < $inicio ) {
					erro("A conta SICOOB esta sem a numeracao inicial de nosso numero. Preencha 'Nosso numero de / ate' no cadastro da conta antes de emitir boletos.");
				}

				$cb->reset();
				$cb->query("LOCK TABLES boletos WRITE");
				$sicoob_lock = true;

				// Maior numero ja usado DENTRO da nova numeracao. Os boletos antigos,
				// derivados do CPF, ficam fora da faixa e nao interferem.
				$cb->reset();
				$cb->query("SELECT MAX(CAST(LEFT(bo_nnum,7) AS UNSIGNED)) AS ultimo FROM boletos
					WHERE conta_id = ".$dados[2]."
					AND CAST(LEFT(bo_nnum,7) AS UNSIGNED) BETWEEN ".$inicio." AND ".$fim);
				$ultimo = (int) $cb->get_val("ultimo");
				$prox   = ( $ultimo < $inicio ) ? $inicio : $ultimo + 1;

				// Pula qualquer numero que a numeracao antiga ja tenha ocupado.
				// bo_nnum guarda o nosso numero com o DV, entao comparamos por prefixo.
				$tentativas = 0;
				while ( $prox <= $fim && $tentativas < 1000 ) {
					$cb->reset();
					$cb->query("SELECT COUNT(*) AS qt FROM boletos WHERE conta_id = ".$dados[2]." AND bo_nnum LIKE '".str_pad($prox, 7, '0', STR_PAD_LEFT)."%'");
					if ( (int) $cb->get_val("qt") == 0 ) { break; }
					$prox++;
					$tentativas++;
				}

				if ( $prox > $fim ) {
					$cb->reset();
					$cb->query("UNLOCK TABLES");
					erro("A numeracao de nosso numero da conta SICOOB chegou ao limite ({$fim}). Amplie 'Nosso numero ate' no cadastro da conta.");
				}
				$ndoc_sicoob = str_pad($prox, 7, '0', STR_PAD_LEFT);
			}

			// Vale tambem para a reimpressao: o documento vem do que esta gravado,
			// nunca recalculado. Antes, reimprimir um boleto antigo devolvia outro
			// numero — e, depois da virada, um boleto com codigo de barras invalido.
			$ndoc = $ndoc_sicoob;
			$b->val("numero_documento", $ndoc);
		}

		if ( $dados[0] == 'ASA' ) {
			// Reimpressao nunca consome numero novo: reusa o que ja foi emitido.
			$cb->reset();
			$cb->query("SELECT bo_nnum FROM boletos WHERE bo_presta = '{$d->id_presta}' AND bo_nnum IS NOT NULL AND bo_nnum <> '' LIMIT 1");
			$nnum_asa = $cb->get_val("bo_nnum");
			$asa_lock = false;

			if ( $nnum_asa == '' ) {
				// Alocacao sob lock: numero duplicado faz o ASA recusar a remessa inteira.
				// O lock, o SELECT MAX e o INSERT precisam estar na MESMA conexao ($cb),
				// porque no MySQL o LOCK TABLES vale so para a conexao que o executou.
				$cb->reset();
				$cb->query("LOCK TABLES boletos WRITE");
				$asa_lock = true;

				$cb->reset();
				$cb->query("SELECT MAX(CAST(bo_nnum AS UNSIGNED)) AS ultimo FROM boletos WHERE conta_id = ".$dados[2]);
				$ultimo = (int) $cb->get_val("ultimo");

				$nnum_asa = ( $ultimo < (int) $conta_asa->faixa_inicio )
					? (int) $conta_asa->faixa_inicio
					: $ultimo + 1;

				if ( $nnum_asa > (int) $conta_asa->faixa_fim ) {
					$cb->reset();
					$cb->query("UNLOCK TABLES");
					erro("A faixa de nosso numero da conta ASA acabou (limite: {$conta_asa->faixa_fim}). Solicite uma nova faixa ao banco antes de emitir mais boletos.");
				}
			}
			$b->val("nosso_numero", $nnum_asa);

			// O numero do documento do ASA passa a ser o proprio nosso numero,
			// zero-preenchido em 10 posicoes.
			//
			// Motivo: o ASA le o campo das posicoes 63-77 do segmento P com apenas
			// 10 caracteres. A composicao padrao do sistema — 6 digitos do CPF +
			// MMAA + 1 sequencial, 11 caracteres — tem os 10 primeiros identicos
			// para o mesmo sacado no mesmo mes, entao dois titulos do mesmo pagador
			// chegavam ao banco com o mesmo numero. Recusa "RC02 Documento em
			// duplicidade" na homologacao de 16/09/2026, com a coluna Titulo do
			// relatorio mostrando 0225210926 e 3812580926, de 10 digitos.
			//
			// O nosso numero ja e unico por construcao (faixa atribuida pelo banco),
			// tem comprimento fixo e nao colide entre sacados, meses ou remessas.
			$ndoc = str_pad($nnum_asa, 10, '0', STR_PAD_LEFT);
			$b->val("numero_documento", $ndoc);
		}

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
			if ($dados[0] == 'ASA') {
				// Gravado SEM o DV: a alocacao usa MAX(bo_nnum)+1 e o DV e recalculado.
				$nnum = $nnum_asa;
			} else if ($dados[0] != 'SICOOB') {
				$nnum = substr(str_replace(array('/','-',' '), '', $b->nossonumero), 2);
			} else {
				$nnum = str_replace(array('/','-',' '), '', $b->dadosboleto["nosso_numero_completo"]);
			}

			$sql="insert into boletos (bo_apto,bo_prop,bo_presta,bo_valor,bo_data_emissao,bo_data_vence,bo_num_presta,bo_ndoc,bo_nnum, conta_id)
			values ('{$d->id_apto}','{$d->id_cliente}', '{$d->id_presta}', '{$d->pr_valor}','$de','$vcq','{$d->pr_num}','$ndoc','$nnum', '{$dados[2]}')
			";

			$cb->reset();
			$cb->query($sql);
		} else if ( $dados[0] != 'ASA' && $dados[0] != 'SICOOB' ) {

			$bol->reset();
			$bol->query("SELECT * FROM boletos WHERE bo_ndoc LIKE '%".$ndoc."%' ORDER BY bo_ndoc desc");

			$boleto = $bol->get_val("bo_ndoc");
			$nnum = $bol->get_val("bo_nnum");
			if ( $boleto != '' ) {
				if ($nnum == null ) {
					$nnum = str_replace(array('/','-',' '), '', $b->nossonumero);
					$nnum = substr($nnum, 2);

					$cb->reset();
					$cb->query("UPDATE boletos SET bo_nnum = ".$nnum. ", remessa_id = 0 WHERE bo_ndoc = ". $ndoc); 
				}
			}
		}

		// Libera o lock em qualquer caminho — inclusive quando o boleto ja existia
		// mas estava sem bo_nnum, caso em que o lock foi adquirido e nao houve INSERT.
		if ( $dados[0] == 'ASA' && $asa_lock ) {
			$cb->reset();
			$cb->query("UNLOCK TABLES");
			$asa_lock = false;
		}
		if ( $dados[0] == 'SICOOB' && $sicoob_lock ) {
			$cb->reset();
			$cb->query("UNLOCK TABLES");
			$sicoob_lock = false;
		}
		/////////// fim verifica boleto
		$b->reset();
	} // fim loop boletos
?>