<?php

// salva edicao de conta — acesso restrito, regras em lib/func_conta.php

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";
include "lib/func_conta.php";
include "lib/admin_acesso.php";

admin_exige();

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$db = new db();
$db->query("SELECT banco FROM contas WHERE id = ".$id);
if ( $id < 1 || $db->rows != 1 ) {
	conta_resposta(false, 'Conta n&atilde;o encontrada.', false);
	exit;
}

// O banco nao muda na edicao, independente do que vier no POST.
$post = $_POST;
$post['banco'] = $db->get_val('banco');

$r = conta_valida($post);
if ( $r['erros'] ) {
	conta_resposta(false, '<b>Corrija antes de salvar:</b><br />'.implode('<br />', $r['erros']), false);
	exit;
}

$db->reset();
$db->query("UPDATE contas SET ".conta_sql_set($r['dados'])." WHERE id = ".$id);

if ( $db->status == "ok" ) {
	conta_resposta(true, 'Altera&ccedil;&otilde;es salvas.', false);
} else {
	conta_resposta(false, 'Ocorreu um erro, nada foi alterado.', false);
}
