<?php

// formulario de cadastro de contas

include "lib/var.php";

$p=new tpl("$modelo");
$p->set("pagina","Cadastro de Contas");
$p->arquivo("conteudo","tpl/tpl_form_add_conta.html");
$p->tprint();
?>