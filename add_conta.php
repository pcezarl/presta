<?php

// formulario de cadastro de contas

include "lib/var.php";
include "lib/func_conta.php";

$form_conta = null;
$form_acao  = "proc_add_conta.php";

ob_start();
echo '<h3 class="titulo">Cadastro de conta ::</h3>';
include "inc_form_conta.php";
$conteudo = ob_get_clean();

$p=new tpl("$modelo");
$p->set("pagina","Cadastro de Contas");
$p->set("conteudo",$conteudo);
$p->tprint();
