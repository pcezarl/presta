<?php
// Formulario de conta, usado no cadastro (add_conta.php) e na edicao (contas_admin.php).
// Espera: $form_conta (objeto da tabela contas ou null) e $form_acao (url do proc).
// Ordem de preenchimento = ordem no HTML; nenhum tabindex manual.

$c = $form_conta;
$editando = $c !== null;
function fc_v($c, $campo, $padrao = '') {
	if ( $c === null || $c->$campo === null ) return $padrao;
	return htmlspecialchars($c->$campo, ENT_QUOTES, 'ISO-8859-1');
}
$banco_atual = $editando ? $c->banco : '';
?>
<style type="text/css">
	#myform { max-width: 820px; margin: 6px 0 0 20px; color: #1B2543; }
	#myform fieldset { border: 0; border-top: 1px solid #8a91a8; margin: 22px 0 0; padding: 16px 0 0; }
	#myform fieldset:first-child { margin-top: 8px; }
	#myform legend { color: #fff; background: #263C6C url("images/fundo-titulo.gif") repeat-x; padding: 3px 14px; font-size: 10pt; letter-spacing: .5px; }
	#myform legend b { display: inline-block; width: 16px; margin-right: 6px; text-align: center; color: #1B2543; background: #E8D9A0; border-radius: 8px; font-size: 8pt; line-height: 16px; }
	#myform .linha { display: table; width: 100%; margin-bottom: 10px; }
	#myform .linha > label { display: table-cell; width: 170px; padding: 5px 14px 0 0; text-align: right; vertical-align: top; }
	#myform .linha > .campo { display: table-cell; vertical-align: top; }
	#myform input[type=text], #myform select { height: 28px; padding: 3px 7px; font: 10pt tahoma; color: #1B2543; background: #fff none; border: 1px solid #9097ad; border-radius: 3px; box-sizing: border-box; vertical-align: middle; }
	#myform input[type=text]:focus, #myform select:focus { border-color: #B5A266; background: #FFFDE8; box-shadow: 0 0 0 2px rgba(181,162,102,.35); outline: none; }
	#myform input::-webkit-input-placeholder { color: #a3a8b8; }
	#myform .largo { width: 100%; }
	#myform .medio { width: 190px; }
	#myform .dv { width: 44px; text-align: center; }
	#myform .sep { color: #5a6178; padding: 0 4px; }
	#myform .dica { display: block; font-size: 8pt; color: #4d5470; margin-top: 3px; }
	#myform .obr { color: #A21A24; font-size: 11pt; padding-right: 2px; }
	#myform .so-banco { display: none; }
	#myform .acoes { margin-top: 22px; padding-top: 14px; border-top: 1px solid #8a91a8; text-align: right; }
	#myform .acoes button { height: 30px; padding: 0 18px; font: 10pt tahoma; border-radius: 3px; cursor: pointer; }
	#myform #limpa { background: transparent; color: #1B2543; border: 1px solid #8a91a8; margin-right: 6px; }
	#myform #limpa:hover { background: rgba(255,255,255,.4); }
	#myform #ok { color: #fff; font-weight: bold; background: #263C6C url("images/fundo-titulo.gif") repeat-x; border: 1px solid #1B2543; }
	#myform #ok:hover, #myform #ok:focus { border-color: #B5A266; box-shadow: 0 0 0 2px rgba(181,162,102,.35); outline: none; }
	#c_proc { margin-top: 18px; }
	#c_proc iframe { width: 100%; display: block; }
</style>

<form action="<?php echo $form_acao ?>" method="post" target="proc" name="myform" id="myform" autocomplete="off">
<?php if ( $editando ) { ?>
	<input type="hidden" name="id" value="<?php echo (int) $c->id ?>" />
<?php } ?>

	<fieldset>
		<legend><b>1</b>Banco</legend>
		<div class="linha">
			<label for="banco"><span class="obr">*</span> Banco</label>
			<div class="campo">
<?php if ( $editando ) { // trocar o banco de uma conta com boletos emitidos quebraria a numeracao ?>
				<input type="hidden" name="banco" id="banco" value="<?php echo fc_v($c, 'banco') ?>" />
				<b><?php $b = conta_bancos(); echo isset($b[$c->banco]) ? $b[$c->banco] : fc_v($c, 'banco') ?></b>
				<span class="dica">O banco n&atilde;o pode ser alterado depois do cadastro.</span>
<?php } else { ?>
				<select name="banco" id="banco" class="medio" style="width:260px">
					<option value="">Selecione...</option>
<?php foreach ( conta_bancos() as $sigla => $nome ) { ?>
					<option value="<?php echo $sigla ?>"><?php echo $nome ?></option>
<?php } ?>
				</select>
<?php } ?>
			</div>
		</div>
	</fieldset>

	<div id="resto-form" class="so-banco">
	<fieldset>
		<legend><b>2</b>Empresa</legend>
		<div class="linha">
			<label for="razao"><span class="obr">*</span> Raz&atilde;o social</label>
			<div class="campo"><input type="text" name="razao" id="razao" class="largo" maxlength="200" value="<?php echo fc_v($c, 'razao') ?>" /></div>
		</div>
		<div class="linha">
			<label for="cnpj"><span class="obr">*</span> CNPJ</label>
			<div class="campo"><input type="text" name="cnpj" id="cnpj" class="medio" maxlength="18" placeholder="00.000.000/0000-00" value="<?php echo fc_v($c, 'cnpj') ?>" /></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><b>3</b>Dados banc&aacute;rios</legend>
		<div class="linha">
			<label for="agencia"><span class="obr">*</span> <span class="rotulo-agencia">Ag&ecirc;ncia</span></label>
			<div class="campo">
				<input type="text" name="agencia" id="agencia" class="medio" maxlength="20" value="<?php echo fc_v($c, 'agencia') ?>" />
				<span class="so-banco banco-ASA"><span class="sep">&ndash;</span><input type="text" name="agencia_dv" id="agencia_dv" class="dv" maxlength="1" title="D&iacute;gito da ag&ecirc;ncia" value="<?php echo fc_v($c, 'agencia_dv') ?>" /></span>
				<span class="dica">Sem o d&iacute;gito<span class="so-banco banco-ASA"> &mdash; informe o d&iacute;gito no campo ao lado</span>.</span>
			</div>
		</div>
		<div class="linha">
			<label for="conta"><span class="obr">*</span> <span class="rotulo-conta">Conta</span></label>
			<div class="campo">
				<input type="text" name="conta" id="conta" class="medio" maxlength="20" value="<?php echo fc_v($c, 'conta') ?>" />
				<span class="so-banco banco-ASA"><span class="sep">&ndash;</span><input type="text" name="conta_dv" id="conta_dv" class="dv" maxlength="1" title="D&iacute;gito da conta" value="<?php echo fc_v($c, 'conta_dv') ?>" /></span>
				<span class="dica">Sem o d&iacute;gito<span class="so-banco banco-ASA"> &mdash; informe o d&iacute;gito no campo ao lado</span>.</span>
			</div>
		</div>
	</fieldset>

	<fieldset class="so-banco banco-BRA banco-SICOOB banco-ASA">
		<legend><b>4</b>Configura&ccedil;&atilde;o do banco</legend>

		<div class="linha so-banco banco-BRA">
			<label for="acessorio"><span class="obr">*</span> C&oacute;digo da empresa</label>
			<div class="campo">
				<input type="text" name="acessorio" id="acessorio" class="medio" maxlength="20" value="<?php echo fc_v($c, 'acessorio') ?>" />
				<span class="dica">C&oacute;digo fornecido pelo Bradesco para o arquivo de remessa.</span>
			</div>
		</div>

		<div class="linha so-banco banco-SICOOB">
			<label for="cod_cliente"><span class="obr">*</span> C&oacute;digo do cliente</label>
			<div class="campo">
				<input type="text" name="cod_cliente" id="cod_cliente" class="medio" maxlength="7" value="<?php echo fc_v($c, 'cod_cliente') ?>" />
				<span class="dica">C&oacute;digo do benefici&aacute;rio no SICOOB (at&eacute; 7 d&iacute;gitos). Vai no c&oacute;digo de barras do boleto.</span>
			</div>
		</div>

		<div class="linha so-banco banco-ASA">
			<label for="operacao"><span class="obr">*</span> Opera&ccedil;&atilde;o</label>
			<div class="campo"><input type="text" name="operacao" id="operacao" class="medio" maxlength="7" value="<?php echo fc_v($c, 'operacao') ?>" /></div>
		</div>
		<div class="linha so-banco banco-ASA">
			<label for="carteira"><span class="obr">*</span> Carteira</label>
			<div class="campo"><input type="text" name="carteira" id="carteira" class="dv" style="width:60px" maxlength="3" value="<?php echo fc_v($c, 'carteira', '121') ?>" /></div>
		</div>

		<div class="linha so-banco banco-SICOOB banco-ASA">
			<label for="faixa_inicio"><span class="obr so-banco banco-ASA">*</span> Nosso n&uacute;mero</label>
			<div class="campo">
				de <input type="text" name="faixa_inicio" id="faixa_inicio" class="medio" style="width:120px" maxlength="10" value="<?php echo fc_v($c, 'faixa_inicio') ?>" />
				at&eacute; <input type="text" name="faixa_fim" id="faixa_fim" class="medio" style="width:120px" maxlength="10" value="<?php echo fc_v($c, 'faixa_fim') ?>" />
				<span class="dica so-banco banco-ASA">Faixa fornecida pelo ASA para este ambiente.</span>
				<span class="dica so-banco banco-SICOOB">Opcional. Em branco, usa de <?php echo SICOOB_FAIXA_MIN ?> at&eacute; <?php echo SICOOB_FAIXA_MAX ?> (limite do banco).</span>
			</div>
		</div>
	</fieldset>

	<div id="c_proc" style="display:none;">
		<iframe name="proc" id="proc" height="70" frameborder="0" scrolling="no"></iframe>
	</div>

	<div class="acoes">
<?php if ( !$editando ) { ?>
		<button type="reset" id="limpa">Limpar</button>
<?php } ?>
		<button type="submit" id="ok"><?php echo $editando ? 'Salvar altera&ccedil;&otilde;es' : 'Cadastrar conta' ?></button>
	</div>
	</div>
</form>

<script type="text/javascript">
(function($) {
	var rotulos = {
		'':       ['Ag&ecirc;ncia', 'Conta'],
		'BRA':    ['Ag&ecirc;ncia', 'Conta'],
		'CEF':    ['Ag&ecirc;ncia', 'C&oacute;d. cedente'],
		'SICOOB': ['Cooperativa', 'Conta corrente'],
		'ASA':    ['Ag&ecirc;ncia', 'Conta vinculada']
	};

	// Exposta para o iframe de resposta chamar apos limpar o formulario.
	window.contaBancoMudou = function() {
		var banco = $('#banco').val();
		var form  = $('#myform');
		form.find('.so-banco').hide();
		if ( banco === '' ) return;
		$('#resto-form').show();
		form.find('.banco-' + banco).show();
		var r = rotulos[banco] || rotulos[''];
		form.find('.rotulo-agencia').html(r[0]);
		form.find('.rotulo-conta').html(r[1]);
	};

	// Enter avanca para o proximo campo visivel (ordem do HTML), como nos outros
	// formularios do sistema. No ultimo campo, envia.
	function proximoCampo(atual) {
		var campos = $('#myform').find('input[type=text], select, button[type=submit]').filter(':visible');
		var i = campos.index(atual);
		if ( i > -1 && i < campos.length - 1 ) campos.eq(i + 1).focus();
	}

	function mascaraCnpj(v) {
		v = v.replace(/[^0-9]/g, '').substring(0, 14);
		var p = [v.substring(0,2), v.substring(2,5), v.substring(5,8), v.substring(8,12), v.substring(12,14)];
		var s = p[0];
		if ( p[1] ) s += '.' + p[1];
		if ( p[2] ) s += '.' + p[2];
		if ( p[3] ) s += '/' + p[3];
		if ( p[4] ) s += '-' + p[4];
		return s;
	}

	$(function() {
		$('#banco').change(function() {
			contaBancoMudou();
			if ( this.value !== '' ) $('#razao').focus();
		});
		$('#myform').bind('reset', function() { setTimeout(contaBancoMudou, 0); });

		$('#myform input[type=text], #myform select').keypress(function(e) {
			if ( e.which == 13 ) { proximoCampo(this); return false; }
		});

		$('#cnpj').keyup(function() { this.value = mascaraCnpj(this.value); });

		$('#myform').submit(function() { $('#c_proc').show(); });

		contaBancoMudou();
		if ( $('#banco').is('select') ) $('#banco').focus(); else $('#razao').focus();
	});
})(jQuery);
</script>
