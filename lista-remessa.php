<?php
// gera lista de boletos  

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";
session_start();

$i=limpa($_GET["i"]);

$p =new tpl("$modelo");
$pn=new tpl("tpl/tpl_form_lista_remessa.html");
$db=new db();

$data=date("d/m/Y");
$mes=date("m");
$mes_atual=$mes_extenso[(integer)date("m")];
$ano=date("Y");

// dia separado
if($i){
$sql="
select * from prestacoes
left join aptos on pr_apto=id_apto
left join clientes on pr_prop=id_cliente
left join edificios on ap_ed=id_edificio
left join boletos on bo_presta=id_presta
where day(pr_vencimento)='$i' and month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
AND conta_id = ".$_SESSION['conta_id']." 
AND remessa_id = 0
and pr_pago='n'";

$pn->set("titulo","$i/$mes_atual/$ano");
}// fim dia separado
else { // todos
$sql="
select * from prestacoes
left join aptos on pr_apto=id_apto
left join clientes on pr_prop=id_cliente
left join edificios on ap_ed=id_edificio
left join boletos on bo_presta=id_presta

where month(pr_vencimento)='$mes' and year(pr_vencimento)='$ano'
AND conta_id = ".$_SESSION['conta_id']." 
AND remessa_id = 0
and pr_pago='n'
";
$pn->set("titulo","$mes_atual/$ano");
} // fim todos
$db->query($sql);
if($db->rows<1){
    $pn->set("sem_registros", "Essa conta n&atilde;o possui nenhum boleto gerado no momento");
// error(6);
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
    $i = 0;
    if ( $_SESSION['conta'] != '' ) {
        while($d=mysql_fetch_object($db->result)){
            if( $_SESSION['conta'] == $d->conta ) {
                $selected = 'selected';
                $pn->set("conta_id", $d->id);
            } else {
                $selected = '';
            }
            $lista_contas .= "<option $selected value='$d->banco - $d->conta'>Ag: $d->banco - CB: $d->conta</option>";
        }
    } else {

        while($d=mysql_fetch_object($db->result)){
            if( $i == 0 ) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $lista_contas .= "<option $selected value='$d->banco - $d->conta'>Ag: $d->banco - CB: $d->conta</option>";
            $_SESSION['conta'] = $d->conta;
            $_SESSION['conta_id'] = $d->id;
            $_SESSION['banco'] = $d->banco;
            $pn->set("conta_id", $d->id);

            $i++;
        }
    }
}
$pn->set('lista_contas', $lista_contas);

$pn->end_loop("linha");
$p->set("pagina","Listagem de boletos - Geração de Remessa");
$p->set("conteudo",$pn->tvar());
$p->tprint();

?>