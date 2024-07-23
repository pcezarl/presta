<?php
	header ("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header ("Cache-Control: no-cache, must-revalidate");
	header ("Pragma: no-cache");


	include "lib/var.php";
	include "lib/func.php";


	$i=limpa($_GET["i"]);

	$db=new db;

	$db->query("select * from prestacoes where id_presta='$i'");
	if($db->status=="erro")die($db->erro);

	if($db->rows!=1)die("
		<h1>Server error</h1>
		O servidor foi abduzido e não conseguiu efetuar a conexão.
		<hr />
		$_SERVER[SERVER_SIGNATURE]
		");

	$dados=$db->data_object;

	$valor=mil($dados->pr_valor);

	$data=mydata($dados->pr_vencimento);

	$data_pg=($dados->pr_data_pago!='0000-00-00')?mydata($dados->pr_data_pago):"";

	$pagos=($dados->pr_pago=='s')?"<input type='radio' name='pago' value='s' checked='checked'>":"<input type='radio' name='pago' value='s'>"  ;
	$pagon=($dados->pr_pago=='n')?"<input type='radio' name='pago' value='n' checked='checked'>":"<input type='radio' name='pago' value='n'>"  ;

	switch($dados->pr_tipo){
		case "n":$tpr[0]="selected='selected'";break; // parcela normal
		case "t":$tpr[1]="selected='selected'";break; // parcela trimestral
		case "s":$tpr[2]="selected='selected'";break; // parcela semestral
		case "c":$tpr[3]="selected='selected'";break; // parcela entrega de chaves
		case "e":$tpr[4]="selected='selected'";break; // parcela entrega de chaves
        case "i":$tpr[5]="selected='selected'";break; // parcela entrega de chaves 

	} // fim case


	$s=basename($_SERVER['HTTP_REFERER']);

?>
<style type="text/css">

	.limpa{
		display:block;
		border: 1px solid #969696;
		background:#D0D0D0;
		height:40px
	}

	.limpa:hover{
		background: #EFEFEF;
	}

	.envia{
		display:block;
		border: 1px solid #969696;
		background:#D0D0D0;
		height:40px
	}

	.envia:hover{
		background: #8001CB;
		color:white;
		border: 1px solid #003D80;
	}


</style>


<div style="background:white;width:400px; height:300px;padding-top:10px;padding-left:7px">
	<form method="post" action="proc_edita_presta.php">
		<input type="hidden" name="id" value="<?php echo $dados->id_presta?>" />
		<input type="hidden" name="ap" value="<?php echo $dados->pr_apto?>" />
		<input type="hidden" name="rd" value="<?php echo $s?>" />


		<table border="0">
			<tr>
				<td>Valor:</td>
				<td><input type="text" name="valor" value="<?php echo $valor?>" /></td>
			</tr>

			<tr>
				<td>Vencimento:</td>
				<td><input type="text" name="vencimento" value="<?php echo $data?>" /></td>
			</tr>

			<tr>
				<td>Data de pagamento:</td>
				<td><input type="text" name="pagto" value="<?php echo $data_pg?>" /></td>
			</tr>


			<tr>
				<td>Parcela paga:</td>
				<td><?php echo $pagos?>SIM  <?php echo $pagon?>NÃO</td>
			</tr>
			<tr>
				<td valign="top">tipo de parcela:</td>
				<td>
					<select name="tipo">
						<option value="n" <?php echo $tpr[0]?>>Mensal</option>
						<option value="t" <?php echo $tpr[1]?>>Trimestral</option>
						<option value="s" <?php echo $tpr[2]?>>Semestral</option>
						<option value="c" <?php echo $tpr[3]?>>Chaves</option>
						<option value="e" <?php echo $tpr[4]?>>Entrada</option>
                                        <option value="i" <?php echo $tpr[5]?>>Intermediaria</option>  

					</select>
				</td>
			</tr>

			<tr>
				<td>Observações:</td>
				<td><input type="text" name="obs" size="30" value="<?php echo $dados->pr_obs?>" /></td>
			</tr>


			<tr>
				<td colspan="2" >&nbsp;</td>
			</tr>


			<tr>
				<td height="80px" align="center"><input type="reset" value="Restaurar" class="limpa" /></td>
				<td height="80px" align="center"><input type="submit" value="Salvar"   class="envia" /></td>
			</tr>


		</table>
	</form>
</div>
