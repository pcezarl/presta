<?php

	// index
	//

	include "lib/var.php";
	include "lib/func.php";
	include "lib/class_tpl.php";
	include "lib/class_db.php";


	$p =new tpl("$modelo");
	$pn=new tpl("tpl/tpl_painel.html");
	$db=new db;

	$data=date("d/m/Y");
	$mes=date("m");
	$mes_atual=$mes_extenso[(integer)date("m")]  ;
	$ano=date("Y");

	$p->set("pagina","Página inicial - $data");

	////////// lista edificio

	$db->query("select * from edificios order by ed_nome");
	$r=$db->result;

	$led="<table border='0' width='100%'>";
	while ($dados=mysql_fetch_array($r)){
		$c++;
		$cor=(is_int($c/2))?"corsim":"cornao";
		$led.="<tr class='$cor'><td><a href='edificio.php?i=$dados[id_edificio]' class='link-lista'>$dados[ed_nome]</a></td></tr>\n";
	}
	$led.="</table>";

	$pn->set("lista ed",$led);
	////////// fim lista edificio

	$db->query("select count(*) as qt from clientes");
	$qt=mysql_result($db->result,0,0);
	$pn->set("total cli",$qt);

	$db->query("select count(*) as qt from edificios");
	$qt=mysql_result($db->result,0,0);
	$pn->set("total ed",$qt);

	$db->query("select count(*) as qt from aptos");
	$qt=mysql_result($db->result,0,0);
	$pn->set("total apto",$qt);

	// monta painel com os dados do mes atual
	$pn->set("data atual","$mes_atual/$ano");

	$sql="
	select sum(pr_valor) as valor, day(pr_vencimento) as dia,count(*)as total from prestacoes where day(pr_vencimento)
	in(
	select distinct(day(pr_vencimento) ) from prestacoes
	)
	and
	month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano' group by pr_vencimento
	";
	$db->reset();
	$db->query($sql);
	if($db->status=="erro")die($db->erro ."<hr>".$db->sql );

	$r=$db->result;

	$led="<table border='0' width='100%' cellspacing='0'>
	<tr class='titulo-total' style='text-align:center;font-size:9pt'>
	<td>DIA</td><td>VALOR TOTAL</td><td>PRESTAÇÕES</td>
	</tr>
	";
	while ($dados=mysql_fetch_array($r)){
		$c++;
		$cor=(is_int($c/2))?"corsim":"cornao";
		$valor=mil($dados["valor"]);

		$led.="<tr class='$cor'  style='font-family:courier'>
		<td style='border-right:1px solid #444444'>
		<a href='lista-mes-atual-dia.php?i=$dados[dia]' class='link-lista'>$dados[dia]</a>
		</td>
		<td style='text-align:right;border-right:1px solid #444444'>
		R\$ $valor
		</td>
		<td style='text-align:right;'>
		$dados[total]
		</td>
		</tr>\n";

	}
	$led.="</table>";

	$pn->set("lista vencimentos",$led);


	// fim dados mes atual


	$p->set("conteudo",$pn->tvar());
	$p->tprint();

?>