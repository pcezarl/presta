<?php

// Lista e edicao de contas — acesso restrito (lib/admin_acesso.php), fora do menu.

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";
include "lib/func_conta.php";
include "lib/admin_acesso.php";

admin_exige();

$db = new db();
$bancos = conta_bancos();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

ob_start();

if ( $id > 0 ) {
	$db->query("SELECT * FROM contas WHERE id = ".$id);
	if ( $db->rows != 1 ) {
		echo '<p>Conta n&atilde;o encontrada. <a href="contas_admin.php">Voltar</a></p>';
	} else {
		$form_conta = $db->data_object;
		$form_acao  = "proc_edita_conta.php";
		echo '<h3 class="titulo">Editar conta #'.$id.' ::</h3>';
		echo '<p><a href="contas_admin.php">&laquo; Voltar para a lista</a>'
		   . ' &nbsp;|&nbsp; &Uacute;ltimo NSA enviado: <b>'.(int) $form_conta->ultimo_nsa.'</b>'
		   . ' <span style="font-size:8pt;color:#666">(somente leitura &mdash; ajuste via SQL, ver db/migrations/2026-09-28-contas-ultimo-nsa.sql)</span></p>';
		include "inc_form_conta.php";
	}
} else {
	$db->query("SELECT c.*, (SELECT COUNT(*) FROM boletos b WHERE b.conta_id = c.id) AS qt_boletos FROM contas c ORDER BY c.banco, c.id");
	echo '<h3 class="titulo">Contas cadastradas ::</h3>';
	echo '<p><a href="add_conta.php">+ Cadastrar nova conta</a></p>';
	echo '<table border="0" cellpadding="5" cellspacing="0" width="100%" style="background:#fff">';
	echo '<tr style="background:#263C6C;color:#fff;text-align:left"><th>#</th><th>Banco</th><th>Raz&atilde;o social</th><th>Ag&ecirc;ncia / Conta</th><th>Nosso n&uacute;mero</th><th>NSA</th><th>Boletos</th><th>Cadastro</th><th></th></tr>';
	$n = 0;
	while ( $c = mysql_fetch_object($db->result) ) {
		$n++;
		$cor   = ($n % 2) ? 'corsim' : 'cornao';
		$ag    = htmlspecialchars($c->agencia).($c->agencia_dv !== null && $c->agencia_dv !== '' ? '-'.htmlspecialchars($c->agencia_dv) : '');
		$ct    = htmlspecialchars($c->conta).($c->conta_dv !== null && $c->conta_dv !== '' ? '-'.htmlspecialchars($c->conta_dv) : '');
		$faixa = $c->faixa_inicio !== null ? $c->faixa_inicio.' &ndash; '.$c->faixa_fim : '&mdash;';
		$data  = $c->data ? date('d/m/Y', strtotime($c->data)) : '';
		echo "<tr class='$cor'><td>{$c->id}</td><td>".(isset($bancos[$c->banco]) ? $bancos[$c->banco] : htmlspecialchars($c->banco))."</td>"
		   . "<td>".htmlspecialchars($c->razao)."</td><td>$ag / $ct</td><td>$faixa</td><td>".(int) $c->ultimo_nsa."</td>"
		   . "<td>".(int) $c->qt_boletos."</td><td>$data</td><td><a href='contas_admin.php?id={$c->id}'>editar</a></td></tr>";
	}
	if ( $n == 0 ) echo '<tr><td colspan="9">Nenhuma conta cadastrada.</td></tr>';
	echo '</table>';
}

$conteudo = ob_get_clean();

$p = new tpl("$modelo");
$p->set("pagina", "Contas");
$p->set("conteudo", $conteudo);
$p->tprint();
