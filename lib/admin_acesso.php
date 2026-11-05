<?php

// Acesso restrito das telas de administracao (contas_admin.php, proc_edita_conta.php).
//
// O sistema nao tem login, entao o acesso e por chave: a chave fica em
// lib/chave_admin.php (fora do git, criado a mao em cada ambiente):
//
//     <?php $chave_admin = 'uma-frase-longa-e-dificil';
//
// Abra uma vez contas_admin.php?k=<chave>; o navegador guarda um cookie por 90 dias.
// Sem o arquivo, ou sem chave valida, a tela responde 404 como se nao existisse.

function admin_nega() {
	header("HTTP/1.0 404 Not Found");
	die("<h1>Not Found</h1>");
}

function admin_exige() {
	$arq = dirname(__FILE__).'/chave_admin.php';
	if ( !is_file($arq) ) admin_nega();
	include $arq;
	if ( !isset($chave_admin) || strlen($chave_admin) < 12 ) admin_nega();

	$token = sha1('presta-admin|'.$chave_admin);

	if ( isset($_GET['k']) ) {
		if ( $_GET['k'] !== $chave_admin ) admin_nega();
		setcookie('presta_admin', $token, time() + 90*86400, '/', '', false, true);
		header("Location: ".basename($_SERVER['PHP_SELF']));
		exit;
	}

	if ( !isset($_COOKIE['presta_admin']) || $_COOKIE['presta_admin'] !== $token ) admin_nega();
}
