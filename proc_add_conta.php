<?php

// cadastra conta — regras em lib/func_conta.php

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";
include "lib/func_conta.php";

$r = conta_valida($_POST);
if ( $r['erros'] ) {
	conta_resposta(false, '<b>Corrija antes de cadastrar:</b><br />'.implode('<br />', $r['erros']), false);
	exit;
}

$db = new db();
$set = conta_sql_set($r['dados']).", data = '".date("Y-m-d")."'";
$db->query("INSERT INTO contas SET ".$set);

if ( $db->status == "ok" ) {
	conta_resposta(true, 'Conta cadastrada com sucesso (id '.$db->lastid.').', true);
} else {
	conta_resposta(false, 'Ocorreu um erro, a conta n&atilde;o foi cadastrada.', false);
}
