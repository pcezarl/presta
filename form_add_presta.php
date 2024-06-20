<html>
	<head>
		<title>Acresçentamentio de prestassão</title>
		<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link rel="stylesheet" type="text/css" href="style.css" />

		<script type="text/javascript">
			function valida1(){
				/// essa vai ser um saco pra fazer.....

				var dia=parseInt( document.getElementById("dia").value );
				var mes=parseInt( document.getElementById("mes").value );
				var ano=parseInt( document.getElementById("ano").value );
				var valor=parseInt( document.getElementById("valor").value );

				// agora vai
				if(
				(dia>31) || ( isNaN(dia) ) ||
				(mes>12) || ( isNaN(mes) ) ||
				(ano<2000) || ( isNaN(ano) ) ||
				(valor<=0) || ( isNaN(ano) )

				){
					// erro
					alert("ERRO: Não existem dados suficientes para gerar prestação");
				}
				else
					{ /// OK

					try{
						document.getElementById("ff").submit();

					}catch(e){
						alert("erro no sistema de validação do formulario");
					}


				}

			} // fim valida


			function valida(){

				if(confirm("Confirma a inclusão da prestação??")){
					valida1();
				}else{
					self.close();
				}

			}



		</script>


	</head>

	<body style="background:#508EDC">
		<div style="background:white;padding:5px;margin-left:1px;margin-right:5px;margin-top:15px">
			<form name="ff" id='ff' method="post" action="proc_add_presta.php">
				<table width="95%" border="1" cellspacing="0" align="center">
					<tr>
						<td width="10%">Valor</td>
						<td width="54%"><input name="valor" type="text" id="valor"></td>
						<td width="7%">tipo</td>
						<td width="29%"><select name="tipo" id="tipo">
								<option value="a">Avulsa</option>
								<option value="i">Intermediaria</option>
								<option value="e">Entrada</option>
								<option value="n">Mensal</option>
								<option value="t">Trimestral</option>
								<option value="s">Semestral</option>
							</select></td>
					</tr>
					<tr>
						<td height="28">Vencimento</td>
						<td width="40%"><input name="dia" type="text" id="dia" size="3" maxlength="2">
							/ <select name="mes" id="mes">
								<option value="01">Janeiro</option>
								<option value="02">Fevereiro</option>
								<option value="03">Mar&ccedil;o</option>
								<option value="04">Abril</option>
								<option value="05">Maio</option>
								<option value="06">Junho</option>
								<option value="07">Julho</option>
								<option value="08">Agosto</option>
								<option value="09">Setembro</option>
								<option value="10">Outubro</option>
								<option value="11">Novembro</option>
								<option value="12">Dezembro</option>
							</select>
							/ <input name="ano" type="text" id="ano" size="6" maxlength="4"></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><input name="apto" type="hidden" id="apto" value="<?php echo $_GET["i"]?>">&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><input type="button" value="Cancelar" onclick="self.close()"></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><input type="button" value="Adicionar" onclick="valida()"></td>
					</tr>
				</table>
			</form>
		</div>
	</body>
</html>
