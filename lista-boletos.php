<?php
// gera lista de boletos  

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";


$p =new tpl("$modelo");
$pn=new tpl("tpl/tpl_form_lista_boletos.html");
$db=new db();

// Periodo escolhido na tela (mes/ano e, opcionalmente, o dia). Antes a listagem
// ficava presa ao mes corrente: no fim do mes nao havia como ver ou emitir os
// boletos que vencem no mes seguinte.
$per=periodo_vencimento($_GET["mes"],$_GET["ano"],$_GET["i"]);
$periodo=$mes_extenso[$per["mes"]]."/".$per["ano"];
$titulo=($per["dia"])?$per["dia"]."/".$periodo:$periodo;
foreach(array("mes","ano","mes_ant","ano_ant","mes_prox","ano_prox") as $k){
    $pn->set($k,$per[$k]);
}
$pn->set("periodo",$periodo);
$pn->set("titulo",$titulo);

$sql="
select * from prestacoes
left join aptos on pr_apto=id_apto
left join clientes on pr_prop=id_cliente
left join edificios on ap_ed=id_edificio
left join boletos on bo_presta=id_presta
where pr_vencimento>='{$per["inicio"]}' and pr_vencimento<'{$per["fim"]}'
and pr_pago='n'
order by pr_vencimento, cli_nome
";

$db->query($sql);
// Periodo vazio fica na propria tela: o error() encerrava a pagina e levava
// junto a navegacao entre os meses.
if($db->rows<1){
    $pn->set("sem_registros", "Nenhuma presta&ccedil;&atilde;o em aberto em $titulo");
} else {
    $pn->set("sem_registros", "");
}

$pn->set("qt",$db->rows);

$pn->begin_loop("linha");

while($d=mysql_fetch_object($db->result)){
    $c++;

    $dia=strtotime($d->pr_vencimento);
    $dia=date("d",$dia);
    $aa["indicador"]=($d->bo_ndoc)?"ind-verm.gif":"ind-verde.gif";
    $aa["cor"]=($c%2)?"corsim":"cornao";
    $aa["id"]=$d->id_presta;
    $aa["valor"]=mil($d->pr_valor);
    $aa["cliente"]=$d->cli_nome;
    $aa["edificio"]=$d->ed_nome;
    $aa["apto"]=$d->ap_num;
    $aa["dia"]=$dia;
    $pn->set_loop($aa);
}


$sql = "select * from contas";

$res = $db->query($sql);
if($db->rows<1){
	error(6);
} else {
	while($d=mysql_fetch_object($db->result)){
		$lista_contas .= "<option value='$d->banco - $d->conta'>$d->banco - Conta: $d->conta</option>";
	}
}
$pn->set('lista_contas', $lista_contas);

$pn->end_loop("linha");




$p->set("pagina","Listagem de boletos");
$p->set("conteudo",$pn->tvar());
$p->tprint();

?>