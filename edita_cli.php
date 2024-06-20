<?php
	// edicao de cliente

	header ("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header ("Cache-Control: no-cache, must-revalidate");
	header ("Pragma: no-cache");


	include "lib/var.php";
	include "lib/func.php";


	$d=new db;
	$i=limpa($_GET["i"]);

	$d->query("select * from clientes where id_cliente='$i'" );
	if($d->rows!=1)die("<h1>Server error:</h1>não foi possivel compor os bytes de alinhamento.");

	$dados=mysql_fetch_object($d->result);


?>
<div style="background:white;padding:10px">

	<form action="proc_edita_cli.php" method="post" name="myform" id="myform" >
		<table width="95%" border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td width="10%">nome:</td>
				<td width="38%"><input name="nome" type="text" value="<?php echo $dados->cli_nome ?>"  id="nome" size="40" tabindex="1" ></td>
				<td width="11%">e-mail</td>
				<td width="41%"><input name="email" value="<?php echo $dados->cli_email ?>" type="text" id="email" size="40" tabindex="2"></td>
			</tr>
			<tr>
				<td>telefone:</td>
				<td><input name="telefone" value="<?php echo $dados->cli_tel ?>" type="text" id="telefone" size="40" tabindex="3"></td>
				<td>CPF/CNPJ:</td>
				<td><input name="cpf" value="<?php echo $dados->cli_cpf ?>" type="text" id="cpf" size="40" tabindex="4"></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp; </td>
				<td>&nbsp; </td>
				<td>&nbsp; </td>
			</tr>
			<tr>
				<td>rua:</td>
				<td><input name="rua" value="<?php echo $dados->cli_rua ?>" type="text" id="rua" size="40" tabindex="5"></td>
				<td>n&uacute;mero:</td>
				<td><input name="numero" value="<?php echo $dados->cli_numero ?>" type="text" id="numero" size="8" tabindex="6"></td>
			</tr>
			<tr>
				<td>bairro:</td>
				<td><input name="bairro" value="<?php echo $dados->cli_bairro ?>" type="text" id="bairro" size="40" tabindex="7"></td>
				<td>cidade:</td>
				<td><input name="cidade" value="<?php echo $dados->cli_cidade ?>" type="text" id="cidade" size="40" tabindex="8"></td>
			</tr>
			<tr>
				<td>CEP:</td>
				<td><input name="cep" value="<?php echo $dados->cli_cep ?>" type="text" id="cep" size="10" tabindex="9"></td>
				<td>estado</td>
				<td><input name="estado" type="text" id="estado" value="<?php echo $dados->cli_estado ?>" size="4" maxlength="2" tabindex="10"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>informações adicionais:<br>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><textarea name="info" cols="40" rows="4" wrap="PHYSICAL" id="info" ><?php echo $dados->cli_obs ?></textarea></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align="center"> <input name="limpa" type="reset" id="limpa" value="Limpar">
				</td>
				<td>&nbsp;</td>
				<td align="center"> <input name="ok" type="submit" id="ok" value=":: Salvar ::"  tabindex="11">
				</td>
			</tr>
		</table>
		<input type="hidden" name="id" value="<?php echo $dados->id_cliente ?>" />
	</form>
</div>
