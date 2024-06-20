<?php

// formulario de cadastro de clientes

include "lib/var.php";

$p=new tpl("$modelo");
$p->set("pagina","Cadastro de Clientes");
$p->arquivo("conteudo","tpl/tpl_form_add_cli.html");
$p->tprint();
?>