<?php

// Regras do cadastro de contas, compartilhadas entre o cadastro (proc_add_conta.php)
// e a edicao (proc_edita_conta.php). Funcoes puras: recebem o $_POST e devolvem os
// dados normalizados ou a lista de erros — testadas em tests/teste_conta.php.

// Limite do nosso numero do SICOOB: 7 digitos (ver class_boleto_SICOOB.php).
define('SICOOB_FAIXA_MIN', 1);
define('SICOOB_FAIXA_MAX', 9999999);

function conta_bancos() {
	return array(
		'SICOOB' => 'SICOOB',
		'BRA'    => 'Bradesco',
		'CEF'    => 'Caixa Econ&ocirc;mica Federal',
		'ASA'    => 'Banco ASA SCD',
	);
}

function conta_txt($post, $campo) {
	if ( !isset($post[$campo]) ) return '';
	$v = trim(strip_tags($post[$campo]));
	return preg_replace("/[<>#%\\^'\"\\\\;]/", "", $v);
}

function conta_num($post, $campo) {
	if ( !isset($post[$campo]) ) return '';
	return preg_replace('/[^0-9]/', '', $post[$campo]);
}

// Retorna array('dados' => array campo => valor, 'erros' => array de mensagens).
function conta_valida($post) {
	$erros = array();

	$d = array(
		'banco'        => conta_txt($post, 'banco'),
		'razao'        => conta_txt($post, 'razao'),
		'cnpj'         => conta_txt($post, 'cnpj'),
		'agencia'      => conta_num($post, 'agencia'),
		'conta'        => conta_num($post, 'conta'),
		'acessorio'    => conta_txt($post, 'acessorio'),
		'cod_cliente'  => conta_num($post, 'cod_cliente'),
		'operacao'     => conta_num($post, 'operacao'),
		'carteira'     => conta_num($post, 'carteira'),
		'agencia_dv'   => conta_txt($post, 'agencia_dv'),
		'conta_dv'     => conta_txt($post, 'conta_dv'),
		'faixa_inicio' => conta_num($post, 'faixa_inicio'),
		'faixa_fim'    => conta_num($post, 'faixa_fim'),
	);

	$bancos = conta_bancos();
	if ( !isset($bancos[$d['banco']]) ) {
		$erros[] = 'Selecione o banco.';
		return array('dados' => $d, 'erros' => $erros);
	}

	if ( $d['razao'] === '' )   $erros[] = 'Informe a raz&atilde;o social.';
	if ( strlen(preg_replace('/[^0-9]/', '', $d['cnpj'])) != 14 ) $erros[] = 'CNPJ deve ter 14 d&iacute;gitos.';
	if ( $d['agencia'] === '' ) $erros[] = 'Informe a ag&ecirc;ncia.';
	if ( $d['conta'] === '' )   $erros[] = 'Informe a conta.';

	// Campos que nao pertencem ao banco escolhido sao limpos, para que um valor
	// digitado antes de trocar o banco no formulario nao va parar no cadastro.
	$so = array(
		'acessorio'   => array('BRA'),
		'cod_cliente' => array('SICOOB'),
		'operacao'    => array('ASA'),
		'carteira'    => array('ASA'),
		'agencia_dv'  => array('ASA'),
		'conta_dv'    => array('ASA'),
		'faixa_inicio'=> array('ASA', 'SICOOB'),
		'faixa_fim'   => array('ASA', 'SICOOB'),
	);
	foreach ( $so as $campo => $bs ) {
		if ( !in_array($d['banco'], $bs) ) $d[$campo] = '';
	}

	if ( $d['banco'] == 'BRA' && $d['acessorio'] === '' ) {
		$erros[] = 'Informe o c&oacute;digo da empresa (Bradesco).';
	}

	if ( $d['banco'] == 'SICOOB' ) {
		if ( $d['cod_cliente'] === '' ) $erros[] = 'Informe o c&oacute;digo do cliente (SICOOB).';
		// Sem faixa informada, vale o intervalo inteiro aceito pelo banco.
		if ( $d['faixa_inicio'] === '' ) $d['faixa_inicio'] = (string) SICOOB_FAIXA_MIN;
		if ( $d['faixa_fim'] === '' )    $d['faixa_fim']    = (string) SICOOB_FAIXA_MAX;
		if ( (int) $d['faixa_inicio'] < SICOOB_FAIXA_MIN || (int) $d['faixa_fim'] > SICOOB_FAIXA_MAX ) {
			$erros[] = 'A faixa do SICOOB deve ficar entre '.SICOOB_FAIXA_MIN.' e '.SICOOB_FAIXA_MAX.'.';
		}
	}

	if ( $d['banco'] == 'ASA' ) {
		if ( $d['operacao'] === '' )   $erros[] = 'Informe a opera&ccedil;&atilde;o (ASA).';
		if ( $d['carteira'] === '' )   $erros[] = 'Informe a carteira (ASA).';
		if ( $d['agencia_dv'] === '' || $d['conta_dv'] === '' ) $erros[] = 'Informe os d&iacute;gitos da ag&ecirc;ncia e da conta (ASA).';
		if ( $d['faixa_inicio'] === '' || $d['faixa_fim'] === '' ) $erros[] = 'Informe a faixa de nosso n&uacute;mero fornecida pelo ASA.';
	}

	if ( $d['faixa_inicio'] !== '' && $d['faixa_fim'] !== '' && (int) $d['faixa_fim'] < (int) $d['faixa_inicio'] ) {
		$erros[] = 'O fim da faixa de nosso n&uacute;mero deve ser maior ou igual ao in&iacute;cio.';
	}

	return array('dados' => $d, 'erros' => $erros);
}

// Monta "campo = valor" para INSERT/UPDATE. Campos vazios viram NULL.
function conta_sql_set($d) {
	$partes = array();
	foreach ( $d as $campo => $valor ) {
		if ( $valor === '' ) {
			$partes[] = "$campo = NULL";
		} else {
			$partes[] = "$campo = '".mysql_real_escape_string($valor)."'";
		}
	}
	return implode(', ', $partes);
}

// Pagina de resposta exibida no iframe do formulario.
function conta_resposta($ok, $msg, $limpar_form) {
	$cor  = $ok ? '#CDFF9F' : '#FF9FA2';
	$js   = $limpar_form ? 'parent.document.getElementById("myform").reset(); parent.contaBancoMudou && parent.contaBancoMudou();' : '';
	$tempo = $ok ? 4000 : 9000;
	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />'
	   . '<script type="text/javascript">'
	   . 'setTimeout(function(){ parent.$("#c_proc").fadeOut(1000); }, '.$tempo.');'
	   . ($ok ? $js : '')
	   . '</script></head>'
	   . '<body style="margin:0;font-family:tahoma;font-size:11pt">'
	   . '<div style="padding:8px;background:'.$cor.'">'.$msg.'</div>'
	   . '<script type="text/javascript">parent.document.getElementById("proc").style.height = document.body.scrollHeight + "px";</script>'
	   . '</body></html>';
}
