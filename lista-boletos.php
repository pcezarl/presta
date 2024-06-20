<?php
// gera lista de boletos  

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";


$i=limpa($_GET["i"]);

$p =new tpl("$modelo");
$pn=new tpl("tpl/tpl_form_lista_boletos.html");
$db=new db();

$data=date("d/m/Y");
$mes=date("m");
$mes_atual=$mes_extenso[(integer)date("m")];
$ano=date("Y");

// dia separado
if ( $i ) {
    $sql="
    select * from prestacoes
    left join aptos on pr_apto=id_apto
    left join clientes on pr_prop=id_cliente
    left join edificios on ap_ed=id_edificio
    left join boletos on bo_presta=id_presta
    where day(pr_vencimento)='$i' and month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
    and pr_pago='n'";

    $pn->set("titulo","$i/$mes_atual/$ano");
} else { // todos
    $sql="
    select * from prestacoes
    left join aptos on pr_apto=id_apto
    left join clientes on pr_prop=id_cliente
    left join edificios on ap_ed=id_edificio
    left join boletos on bo_presta=id_presta
    where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
    and pr_pago='n' 
    ";
    $pn->set("titulo","$mes_atual/$ano");
} // fim todos

$db->query($sql);
if($db->rows<1){
    error(6);
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