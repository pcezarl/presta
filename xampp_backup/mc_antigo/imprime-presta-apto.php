<?php

	// imprime prestacoes do aopto

	include "lib/var.php";
	include "lib/func.php";

	$i=limpa($_GET["i"]);

	$db=new db();

	$sql="
	select * from prestacoes
	left join clientes  on pr_prop=id_cliente
	left join aptos     on pr_apto=id_apto
	left join edificios on ap_ed=id_edificio
	left join boletos   on bo_presta=id_presta
	where id_apto='$i'
	";

	$db->query($sql);


	$info=$db->data_object;
	$db->reset();

	$sql="select max(pr_num) as max, pr_tipo as tipo from prestacoes where pr_apto='$i' group by tipo ";
	$db->query($sql);

	while($d=mysql_fetch_object($db->result)){
		$max[$d->tipo]=$d->max;
	}

?>
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="content-language" content="pt-br" />
		<title>Relatorio de parcelas:: <?php  echo $info->ed_nome ?>::<?php  echo $info->ap_num ?> </title>



		<style type="text/css" media="print,screen">


			.tabela{
				color:black;
				font-size:9pt;
				font-family: monospace;
				border-collapse: collapse;
				border:1px solid #B9B9B9;
				width: 100%;

			}

			.info{
				border:1px solid #969696;
				margin:0 auto;
				display:block;
				width: 60%;
				padding: 5px;

			}

			td{
				padding-right: 5px;
				padding-left : 5px;

			}

			.pendente{
				font-size:9pt;
				text-align:center;
			}

		</style>

	</head>
	<body onload="print();close()">

		<div class="info">
			Edificio: <?php echo $info->ed_nome ?>, apartamento <?php echo $info->ap_num ?><br />
			Proprietario: <?php echo $info->cli_nome ?>
		</div><hr noshade="noshade" /><br />

		<table class="tabela" border="1" align="center" width="80%">

			<tr style="text-align: center;background-color: #585858;color:white">
				<th>Valor</th>
				<th>Vencimento</th>
				<th colspan="2">Tipo</th>
				<th>Pagamento</th>
				<th>OBS</th>

			</tr>

			<?php

				$sql="
				select * from prestacoes
				left join clientes  on pr_prop=id_cliente
				left join aptos     on pr_apto=id_apto
				left join edificios on ap_ed=id_edificio
				left join boletos   on bo_presta=id_presta
				where id_apto='$i' order by pr_vencimento
				";

				$db->query($sql);

				while($d=mysql_fetch_object($db->result)){

					$valor=mil($d->pr_valor);
					$vence=mydata($d->pr_vencimento);
					$tipo=$tipo_parcela[$d->pr_tipo];
					$parc=$d->pr_num ."/". $max[$d->pr_tipo];
					if($d->pr_pago=='s'){
						$dpg=mydata($d->pr_data_pago);
						$pagto="<div class='pendente'>$dpg</div>";
					}else{
						$pagto="<div class='pendente'>pendente</div>";
					}

					echo "
					<tr>
					<td align='right'>$valor  </td>
					<td align='center'>$vence  </td>
					<td style='border-right:0'>$parc  </td>
					<td style='border-left:0'>$tipo  </td>
					<td style='font-size:8pt'>$pagto  </td>
					<td>{$d->pr_obs}</td>
					</tr>

					";
				}
			?>


		</table> <br />
		<div style="width: 50%; display: block;margin:0 auto; color:#808080">------ fim do relatorio ------</div>


	</body>
</html>


