<?php

	// detalhes de edificio


	include "lib/var.php";
	include "lib/func.php";

	$p= new tpl("$modelo");
	$pn=new tpl("tpl/tpl_ver_edificio.html");
	$db=new db;


	$i=limpa($_GET["i"]);

	// dados do edificio
	$db->query("select * from  edificios  where id_edificio='$i'");
	if($db->status=="erro")die("erro na sql");
	if($db->rows!=1)error(7);


	$r=$db->result;
	$dados=mysql_fetch_array($r);

	$pn->set("nome edificio",$dados["ed_nome"]);
	$pn->set("end",$dados["ed_end"]);
	$pn->set("info",$dados["ed_info"]);

	// fim edificio

	// comeco lista aptos
	$pn->begin_loop("linha");

	$db->query("select * from aptos where ap_ed='$i' order by ap_num desc");

	if($db->status=="erro")die($db->erro);

	$pn->set("num",$db->rows);

	$r=$db->result;

	while($dados=mysql_fetch_array($r)){
		$c++;

		$d["cor"]=(is_int($c/2))?"corsim": "cornao";
		$d["cc"]=$c;
		$d["valor"]=mil($dados["ap_valor"]);
		$d["id"]=$dados["id_apto"];
		$d["ap"]=$dados["ap_num"];
		$d["vendido"]=($dados["ap_vendido"]=='n')?"disponivel": "vendido";

		if($dados["ap_vendido"]=='s')$pn->esc("vendido$c");
		if($dados["ap_vendido"]=='n')$pn->esc("ver$c");

		$pn->set_loop($d);
	}
	$pn->end_loop("linha");
	// fim lista aptos


	///////////// gera dados financeiros
	$db->reset();




	/// soma total ate a atual data
	$sql="select sum(pr_valor) as  valor from prestacoes
join aptos on pr_apto=id_apto
join edificios on ap_ed=id_edificio
where id_edificio=$i";
	$db->query($sql);


	$valor_total=mil($db->get_val("valor"));

	/// soma total ate a atual data PAGAS
	$sql="select sum(pr_valor) as  valor from prestacoes
join aptos on pr_apto=id_apto
join edificios on ap_ed=id_edificio
where id_edificio=$i and pr_pago='s'";
	$db->query($sql);
	$valor_total_pago=mil($db->get_val("valor"));

	/// soma total ate a atual data DEVIDO
	 $db->reset();
	$sql="select sum(pr_valor) as  valor from prestacoes
join aptos on pr_apto=id_apto
join edificios on ap_ed=id_edificio
where id_edificio=$i and pr_pago='n'";
	$db->query($sql);

	$valor_total_devido=mil($db->get_val("valor"));


	/// pega os aptos vendidos
	$sql="select count(*) as total,ap_vendido as vendido from aptos
	left join edificios on id_edificio=ap_ed
	where id_edificio=$i
	group by ap_vendido";

	$db->query($sql);
	$total_ap_vendido=$db->get_val("total",0);
	$total_ap_disponivel=$db->get_val("total",1);

	$painel=new tpl("tpl/tpl_mini_painel.html");

	$texto="
<b>Informativo deste edificio:</b><hr />
<pre>

<table border='1' width='100%' style=\"border-collapse:collapse; border:1px solid #ACB1D7\">
<tr>
<td>Valor total</td>
<td align='right'>$valor_total</td>
</tr>
<tr>
<td>Valor pago</td>
<td align='right'>$valor_total_pago</td>
</tr>
<tr>
<td>Valor devido</td>
<td align='right'>$valor_total_devido</td>
</tr>


</table>
aptos vendidos:    $total_ap_vendido
aptos disponiveis: $total_ap_disponivel
</pre>
	";

	$painel->set("texto",$texto);


	//////////// fim finac


	$p->set("pagina","Detalhes do edificio");
	$p->set("conteudo",$pn->tvar().$painel->tvar() );
	$p->tprint();


?>
