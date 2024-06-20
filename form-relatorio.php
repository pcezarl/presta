<?php
//////////////// formulario dos relatorios
// nem vai dar trabalho


include "lib/var.php";
include "lib/func.php";


$p =new tpl("$modelo");
$pn=new tpl("tpl/tpl_relatorio_data.html");


$p->set("pagina","Relatorios");
$p->set("conteudo",$pn->tvar());
$p->tprint();
?>
