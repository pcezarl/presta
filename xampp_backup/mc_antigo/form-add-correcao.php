<?php

// adiciona indice de correcao nas prestacoes

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";

$p= new tpl($modelo);
$pn=new tpl("tpl/tpl_form_correcao.html");
$db=new db;

$hj=date("Y-m-d");
$db->query("select distinct(day(pr_vencimento)) as dia from prestacoes where pr_vencimento>='$hj'  order by dia");

$pn->begin_loop("linha");
while($m=mysql_fetch_object($db->result))
{
$d["dia"]=$m->dia;
$pn->set_loop($d);
}
$pn->end_loop("linha");

$p->set("pagina","Taxa de correзгo");
$p->set("conteudo",$pn->tvar());
$p->tprint();
?>