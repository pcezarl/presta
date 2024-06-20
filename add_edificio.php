<?php

// formulario de cadastro de edificio



include "lib/var.php";

$p=new tpl("$modelo");
$p->set("pagina","Cadastro de Edificio");
$p->arquivo("conteudo","tpl/tpl_form_add_edi.html");
$p->tprint();
?>
