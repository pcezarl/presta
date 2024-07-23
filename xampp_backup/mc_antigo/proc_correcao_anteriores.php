<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="content-language" content="pt-br" />
	<title>Cobrança Aires</title>
	<body>
		<?php
			// efetua a correcao nas parcelas


			include "lib/var.php";
			include "lib/func.php";
			include "lib/class_db.php";

			$pc= limpa($_POST["correcao"]); //porcentagem
			$dia=limpa($_POST["dia"]);
			$mes=limpa($_POST["mes"]);
			$ano=limpa($_POST["ano"]);
			$apto=limpa($_POST["apto"]);


			if((!$pc)||(!$dia)){
				die("<h3>SERVER ERROR:</h3><p>Algumas informações estão ausentes.</p>");
			}

			$hj=date("Y-m-d");


			$indice=$pc/100;

			$db=new db;
			$pr=new db;


			$inicio="$ano-$mes-$dia";

			$db->query("select pr_valor,id_presta from prestacoes
			where pr_vencimento>='$inicio' and pr_apto='$apto'
			");

			$qt=$db->rows;


			$pr->transact("abre");
			$pr->lock("prestacoes");

			while($m=mysql_fetch_array($db->result)){

				$cor=$m["pr_valor"]*$indice;
				$c2=$m["pr_valor"]+$cor;
				$acumulado+=$cor;

				$pr->query("update prestacoes set pr_valor='$c2' where id_presta='$m[id_presta]'");
				if($pr->status=="erro")die($pr->erro);

			} // fim loop
			$pr->unlock();
			$pr->transact("salva");

			$ac=mil($acumulado);
			echo "
			<div style='color:#125900;font-family:tahoma;font-size:14pt;background:#E8FF8A;border:1px solid #698400;padding-left:5px;margin-top:2px'><b>OK!</b> as parcelas foram corrigidas com sucesso</div>
			total de parcelas corrigidas: <b>$qt</b><br />
			total acumulado: <b>R\$ $ac</b><br />
			";
		?>

		<script type="text/javascript">
			parent.document.getElementById("carregando").className='off';
			parent.document.getElementById("carregando").style.display='none';
			parent.document.getElementById("proc").style.display='block';
			parent.document.getElementById("ff").reset();

		</script>
	</body>
</html>